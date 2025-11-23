<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/../Models/PostModel.php';
require_once __DIR__ . '/../Models/FollowModel.php';
require_once __DIR__ . '/../Models/BlockModel.php';
require_once __DIR__ . '/../Models/LikeModel.php';
require_once __DIR__ . '/../Models/CommentModel.php';

class ProfileController {
    private $profileModel;
    private $postModel;
    private $followModel;
    private $blockModel;
    private $likeModel;
    private $commentModel;

    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->postModel = new PostModel();
        $this->followModel = new FollowModel();
        $this->blockModel = new BlockModel();
        $this->likeModel = new LikeModel();
        $this->commentModel = new CommentModel();
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
            $profile = [
                'username' => 'Unknown User',
                'bio' => '',
                'profile_picture' => 'uploads/default.png',
                'followers' => 0,
                'following' => 0,
                'is_private' => 0
            ];
        }

        // If profile owner is admin-blocked and viewer is not the owner
        if (isset($profile['is_active']) && (int)$profile['is_active'] === 0 && $userId != $viewerId) {
            $posts = [];
            $followersList = [];
            $followingList = [];
            $isProfileAdminBlocked = true;
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
        $canViewPosts = false;
        if ($userId == $viewerId) {
            $canViewPosts = true;
        } elseif ($profile['is_private'] == 0 && !$isBlocked) {
            $canViewPosts = true;
        } elseif ($isFollowing && !$isBlocked) {
            $canViewPosts = true;
        }

        // Load posts conditionally:
        // - If viewer can view posts (owner, public profile, or follower), show all posts.
        // - Otherwise (private profile, non-follower), show only public posts.
        if ($canViewPosts) {
            $posts = $this->postModel->getPostsByUser($userId);
        } else {
            // Fallback to public posts only (so private profiles still show public content)
            if (method_exists($this->postModel, 'getPublicPostsByUser')) {
                $posts = $this->postModel->getPublicPostsByUser($userId);
            } else {
                // If the method does not exist, load all and filter to public on the PHP side.
                $allPosts = $this->postModel->getPostsByUser($userId);
                $posts = array_values(array_filter($allPosts, function($p) {
                    // Support multiple schema variations:
                    // - boolean/int flag: is_public == 1
                    // - string flags: visibility == 'public' or privacy == 'public'
                    if (isset($p['is_public'])) {
                        return (int)$p['is_public'] === 1;
                    }
                    if (isset($p['visibility'])) {
                        return strtolower((string)$p['visibility']) === 'public';
                    }
                    if (isset($p['privacy'])) {
                        return strtolower((string)$p['privacy']) === 'public';
                    }
                    // If no visibility field exists, be conservative and hide it
                    return false;
                }));
            }
        }
        if (!is_array($posts)) { $posts = []; }
        
        // Flags for the view
        // On private profiles, only followers (or the owner) should see followers/following lists.
        $canSeeSocialLists = ($profile['is_private'] == 0) || ($userId == $viewerId) || ($isFollowing && !$isBlocked);
        $showPrivateNotice = ($profile['is_private'] == 1) && !$isFollowing && ($userId != $viewerId);

        include __DIR__ . '/../Views/Profile.php';
    }

    // Follow a user
    public function follow($followedId) {
        Session::start();
        $followerId = Session::get('user')['user_id'];
        if ($followerId == $followedId) return;

        $this->followModel->followUser($followerId, $followedId);
        header("Location: index.php?action=profile&user_id=" . $followedId);
        exit();
    }

    // Unfollow a user
    public function unfollow($followedId) {
        Session::start();
        $followerId = Session::get('user')['user_id'];
        if ($followerId == $followedId) return;

        $this->followModel->unfollowUser($followerId, $followedId);
        header("Location: index.php?action=profile&user_id=" . $followedId);
        exit();
    }

    // Block a user
    public function block($blockedId) {
        Session::start();
        $blockerId = Session::get('user')['user_id'];
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
        $blockerId = Session::get('user')['user_id'];
        if ($blockerId == $blockedId) return;

        $this->blockModel->unblockUser($blockerId, $blockedId);
        header("Location: index.php?action=settings&section=blocked");
        exit();
    }
}
?>
