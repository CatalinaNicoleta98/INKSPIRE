<?php

class PostModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDb() {
        return $this->conn;
    }

    // Create a new post
    public function createPost($title, $description, $imagePath, $userId, $tags = null) {
        try {
            $query = "INSERT INTO Post (title, description, image_url, user_id, tags)
                      VALUES (:title, :description, :image_url, :user_id, :tags)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':image_url' => $imagePath,
                ':user_id' => $userId,
                ':tags' => $tags
            ]);
            return true;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Get all posts (Explore, generic)
    public function getAllPosts() {
        $query = "SELECT p.*, u.username, pr.profile_picture,
                         (SELECT COUNT(*) FROM `Like` l WHERE l.post_id = p.post_id) AS likes,
                         (SELECT COUNT(*) FROM `Comment` c 
                          WHERE c.post_id = p.post_id 
                          OR c.parent_id IN (SELECT comment_id FROM Comment WHERE post_id = p.post_id)) AS comments
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  LEFT JOIN Profile pr ON u.user_id = pr.user_id
                  WHERE u.is_active = 1
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one post by ID
    public function getPostById($postId) {
        $query = "SELECT p.*, u.username, pr.profile_picture
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  LEFT JOIN Profile pr ON pr.user_id = u.user_id
                  WHERE p.post_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get posts for multiple users (Home)
    public function getPostsForUsers($userIds) {
        if (empty($userIds)) return [];

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT p.*, u.username, pr.profile_picture,
                       (SELECT COUNT(*) FROM `Like` l WHERE l.post_id = p.post_id) AS likes,
                       (SELECT COUNT(*) FROM `Comment` c 
                        WHERE c.post_id = p.post_id 
                        OR c.parent_id IN (SELECT comment_id FROM Comment WHERE post_id = p.post_id)) AS comments
                FROM Post p
                JOIN User u ON p.user_id = u.user_id
                LEFT JOIN Profile pr ON u.user_id = pr.user_id
                WHERE u.is_active = 1
                AND p.user_id IN ($placeholders)
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($userIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get posts by a specific user (Profile)
    public function getPostsByUser($userId) {
        $query = "SELECT p.*, u.username, pr.profile_picture,
                         (SELECT COUNT(*) FROM `Like` l JOIN User uu ON l.user_id = uu.user_id WHERE l.post_id = p.post_id AND uu.is_active = 1) AS likes,
                         (SELECT COUNT(*) FROM `Comment` c 
                          JOIN User uu2 ON c.user_id = uu2.user_id
                          WHERE (c.post_id = p.post_id 
                          OR c.parent_id IN (SELECT comment_id FROM Comment WHERE post_id = p.post_id))
                          AND uu2.is_active = 1) AS comments
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  LEFT JOIN Profile pr ON u.user_id = pr.user_id
                  WHERE p.user_id = :user_id
                  ORDER BY p.is_sticky DESC, p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update post title/description
    public function updatePost($postId, $userId, $title, $description) {
        try {
            $query = "UPDATE Post 
                      SET title = :title, description = :description, updated_at = NOW()
                      WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':title' => htmlspecialchars(trim($title)),
                ':description' => htmlspecialchars(trim($description)),
                ':post_id' => $postId,
                ':user_id' => $userId
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Delete a post
    public function deletePost($postId, $userId = null) {
        try {
            if ($userId === null) {
                // Admin delete: ignore ownership
                $query = "DELETE FROM Post WHERE post_id = :post_id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([':post_id' => $postId]);
            } else {
                // Normal user: must own the post
                $query = "DELETE FROM Post WHERE post_id = :post_id AND user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':post_id' => $postId,
                    ':user_id' => $userId
                ]);
            }
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Toggle post privacy
    public function togglePrivacy($postId, $userId, $isPublic) {
        try {
            $query = "UPDATE Post SET is_public = :is_public WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':is_public' => $isPublic,
                ':post_id' => $postId,
                ':user_id' => $userId
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // ✅ Unified: Works for both DB types (Follow table uses either user_id or profile_id)
    public function getPostsFromFollowedUsers($userId) {
        // Attempt profile-based relationship, fallback to user_id
        $query = "SELECT p.*, u.username, pr.profile_picture,
                         (SELECT COUNT(*) FROM `Like` l WHERE l.post_id = p.post_id) AS likes,
                         (SELECT COUNT(*) FROM `Comment` c 
                          WHERE c.post_id = p.post_id 
                          OR c.parent_id IN (SELECT comment_id FROM Comment WHERE post_id = p.post_id)) AS comments
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  LEFT JOIN Profile pr ON u.user_id = pr.user_id
                  WHERE u.is_active = 1
                  AND p.user_id IN (
                      SELECT CASE 
                          WHEN EXISTS (SELECT 1 FROM Profile WHERE Profile.profile_id = f.following_id)
                          THEN (SELECT user_id FROM Profile WHERE profile_id = f.following_id)
                          ELSE f.following_id
                      END
                      FROM Follow f
                      WHERE f.follower_id = :user_id
                  )
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Unified: Get all public posts (Explore feed)
    public function getAllPublicPosts($excludeUserId = null, $blockedIds = []) {
        $blockedCondition = '';
        if (!empty($blockedIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedIds), '?'));
            $blockedCondition = "AND p.user_id NOT IN ($placeholders)";
        }

        $params = [];
        $where = "p.is_public = 1 AND u.is_active = 1";
        if ($excludeUserId) {
            $where .= " AND p.user_id != ?";
            $params[] = $excludeUserId;
        }

        $params = array_merge($params, $blockedIds);

        $sql = "SELECT p.*, u.username, pr.profile_picture,
                       (SELECT COUNT(*) FROM `Like` l JOIN User uu ON l.user_id = uu.user_id WHERE l.post_id = p.post_id AND uu.is_active = 1) AS likes,
                       (SELECT COUNT(*) FROM `Comment` c 
                        JOIN User uu2 ON c.user_id = uu2.user_id
                        WHERE (c.post_id = p.post_id 
                        OR c.parent_id IN (SELECT comment_id FROM Comment WHERE post_id = p.post_id))
                        AND uu2.is_active = 1) AS comments
                FROM Post p
                JOIN User u ON p.user_id = u.user_id
                LEFT JOIN Profile pr ON u.user_id = pr.user_id
                WHERE $where $blockedCondition
                ORDER BY p.created_at DESC";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update full post (title, description, image, tags)
    public function updatePostFull($postId, $userId, $title, $description, $imagePath, $tags) {
        try {
            $query = "UPDATE Post 
                      SET title = :title,
                          description = :description,
                          image_url = :image_url,
                          tags = :tags,
                          updated_at = NOW()
                      WHERE post_id = :post_id AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':title' => htmlspecialchars(trim($title)),
                ':description' => htmlspecialchars(trim($description)),
                ':image_url' => $imagePath,
                ':tags' => $tags,
                ':post_id' => $postId,
                ':user_id' => $userId
            ]);
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }
    // Toggle sticky state for a post (pin/unpin)
    public function setSticky($postId, $value) {
        try {
            $query = "UPDATE Post SET is_sticky = :sticky WHERE post_id = :post_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':sticky' => $value,
                ':post_id' => $postId
            ]);
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Count how many sticky posts a user currently has (optional limit)
    public function countStickyPosts($userId) {
        try {
            $query = "SELECT COUNT(*) FROM Post WHERE user_id = :user_id AND is_sticky = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return 0;
        }
    }
}
?>