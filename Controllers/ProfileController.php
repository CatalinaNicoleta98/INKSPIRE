<?php

class ProfileController {
    private $profileModel;
    private $postModel;
    private $followModel;
    private $blockModel;
    private $likeModel;
    private $commentModel;

    public function __construct($db) {
        $this->profileModel = new ProfileModel($db);
        $this->postModel = new PostModel($db);
        $this->followModel = new FollowModel($db);
        $this->blockModel = new BlockModel($db);
        $this->likeModel = new LikeModel($db);
        $this->commentModel = new CommentModel($db);
    }

    public function view($userId = null) {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $currentUser = Session::get('user');
        $viewerId = $currentUser['user_id'];

        // Ensure flags always exist
        $isBlocked = false;
        $isAdminBlocked = false;
        $isProfileAdminBlocked = false;

        // Determine which profile to show
        if ($userId === null) {
            $userId = $viewerId;
        }

        // If logged-in user is admin-blocked and viewing their own profile
        if (isset($currentUser['is_active']) && (int)$currentUser['is_active'] === 0 && $userId == $viewerId) {
            $profile = $this->profileModel->getUserProfile($userId);
            $followersList = [];
            $followingList = [];
            $isAdminBlocked = true;
            // Do NOT block posts here — blocked users may see their own posts
            $posts = $this->postModel->getPostsByUser($userId);
            /* Sidebar variables */
            $db = $this->profileModel->getDb();
            $sidebar = SidebarController::data($db);
            extract($sidebar);

            include __DIR__ . '/../Views/Profile.php';
            return;
        }

        // Check if either user has blocked the other
        $isEitherBlocked = $this->blockModel->isEitherBlocked($viewerId, $userId);
        if ($isEitherBlocked && $userId != $viewerId) {
            // ✅ Fixed: render full HTML document with Tailwind CDN
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Profile Unavailable | Inkspire</title>
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center">
                <div class="text-center bg-white p-8 rounded-2xl shadow-lg max-w-md mx-auto">
                    <h1 class="text-3xl font-semibold text-gray-800 mb-3">🚫 Profile Unavailable</h1>
                    <p class="text-gray-600 mb-6">You cannot view this profile.</p>
                    <a href="index.php?action=feed" class="inline-block bg-indigo-500 text-white px-5 py-2 rounded-md hover:bg-indigo-600 transition">
                        Go back
                    </a>
                </div>
            </body>
            </html>';
            exit();
        }

        // Get profile info
        $profile = $this->profileModel->getUserProfile($userId);
        if (!$profile) {
            include __DIR__ . '/../Views/404.php';
            exit();
        }

        // If profile owner is admin-blocked and viewer is not the owner
        if (isset($profile['is_active']) && (int)$profile['is_active'] === 0 && $userId != $viewerId) {
            $posts = [];
            $followersList = [];
            $followingList = [];
            $isProfileAdminBlocked = true;
            /* Sidebar variables */
            $db = $this->profileModel->getDb();
            $sidebar = SidebarController::data($db);
            extract($sidebar);

            include __DIR__ . '/../Views/Profile.php';
            return;
        }

        // Add follower and following counts
        $profile['followers_count'] = $this->followModel->countFollowers($userId);
        $profile['following_count'] = $this->followModel->countFollowing($userId);

        // Fetch detailed follower/following lists for hover dropdowns
        $followersList = $this->followModel->getFollowersList($userId);
        $followingList = $this->followModel->getFollowingList($userId);

        // Check follow and block status
        $isFollowing = $this->followModel->isFollowing($viewerId, $userId);
        $isBlocked = $this->blockModel->isBlocked($userId, $viewerId); // viewer blocked by profile owner

        // Handle privacy
        $canViewAllPosts = false;
        $canViewPublicPosts = false;

        if ($userId == $viewerId) {
            $canViewAllPosts = true;
        } elseif ($isFollowing && !$isBlocked) {
            $canViewAllPosts = true;
        } elseif ($profile['is_private'] == 0 && !$isBlocked) {
            // Public profile but non-follower
            $canViewPublicPosts = true;
        }

        // Load posts conditionally:
        if ($canViewAllPosts) {
            $posts = $this->postModel->getPostsByUser($userId);
        } elseif ($canViewPublicPosts) {
            if (method_exists($this->postModel, 'getPublicPostsByUser')) {
                $posts = $this->postModel->getPublicPostsByUser($userId);
            } else {
                $allPosts = $this->postModel->getPostsByUser($userId);
                $posts = array_values(array_filter($allPosts, function($p) {
                    return isset($p['is_public']) ? (int)$p['is_public'] === 1 : false;
                }));
            }
        } else {
            // Private profile, non-follower → see nothing
            $posts = [];
        }
        if (!is_array($posts)) { $posts = []; }

        // Attach like information for each post on profile (viewer-based like state)
        $likeModel = new LikeModel($this->postModel->getDb());
        $likedPosts = $likeModel->getUserLikes($viewerId);
        $likedSet = array_flip($likedPosts);

        foreach ($posts as &$post) {
            $post['likes'] = $likeModel->countLikes($post['post_id']);
            $post['liked'] = isset($likedSet[$post['post_id']]);
        }
        unset($post);
        
        // Flags for the view
        // On private profiles, only followers (or the owner) should see followers/following lists.
        $canSeeSocialLists = ($profile['is_private'] == 0) || ($userId == $viewerId) || ($isFollowing && !$isBlocked);
        $showPrivateNotice = ($profile['is_private'] == 1) && !$isFollowing && ($userId != $viewerId);

        /* Sidebar variables */
        $db = $this->profileModel->getDb();
        $sidebar = SidebarController::data($db);
        extract($sidebar);

        include __DIR__ . '/../Views/Profile.php';
    }

    // Follow a user
    public function follow($followedId) {
        Session::start();
        $currentUser = Session::get('user');
        // Prevent globally blocked users from performing this action
        if (isset($currentUser['is_globally_blocked']) && (int)$currentUser['is_globally_blocked'] === 1) {
            header("Location: index.php?action=profile&user_id=" . $followedId);
            exit();
        }
        $followerId = $currentUser['user_id'];
        if ($followerId == $followedId) return;

        $this->followModel->followUser($followerId, $followedId);
        header("Location: index.php?action=profile&user_id=" . $followedId);
        exit();
    }

    // Unfollow a user
    public function unfollow($followedId) {
        Session::start();
        $currentUser = Session::get('user');
        // Prevent globally blocked users from performing this action
        if (isset($currentUser['is_globally_blocked']) && (int)$currentUser['is_globally_blocked'] === 1) {
            header("Location: index.php?action=profile&user_id=" . $followedId);
            exit();
        }
        $followerId = $currentUser['user_id'];
        if ($followerId == $followedId) return;

        $this->followModel->unfollowUser($followerId, $followedId);
        header("Location: index.php?action=profile&user_id=" . $followedId);
        exit();
    }

    // Block a user
    public function block($blockedId) {
        Session::start();
        $currentUser = Session::get('user');
        // Prevent globally blocked users from performing this action
        if (isset($currentUser['is_globally_blocked']) && (int)$currentUser['is_globally_blocked'] === 1) {
            header("Location: index.php?action=profile&user_id=" . $blockedId);
            exit();
        }
        $blockerId = $currentUser['user_id'];
        if ($blockerId == $blockedId) return;

        $this->blockModel->blockUser($blockerId, $blockedId);
        // Auto-unfollow in both directions when blocking
        $this->followModel->unfollowUser($blockerId, $blockedId);
        $this->followModel->unfollowUser($blockedId, $blockerId);
        // Delete all likes and comments between the two users
        $this->likeModel->deleteInteractionsBetween($blockerId, $blockedId);
        $this->commentModel->deleteInteractionsBetween($blockerId, $blockedId);
        header("Location: index.php?action=profile&user_id=" . $blockedId);
        exit();
    }

    // Unblock a user
    public function unblock($blockedId) {
        Session::start();
        $currentUser = Session::get('user');
        // Prevent globally blocked users from performing this action
        if (isset($currentUser['is_globally_blocked']) && (int)$currentUser['is_globally_blocked'] === 1) {
            header("Location: index.php?action=profile&user_id=" . $blockedId);
            exit();
        }
        $blockerId = $currentUser['user_id'];
        if ($blockerId == $blockedId) return;

        $this->blockModel->unblockUser($blockerId, $blockedId);
        header("Location: index.php?action=settings&section=blocked");
        exit();
    }
}
?>
