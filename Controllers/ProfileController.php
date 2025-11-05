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

        // Determine which profile to show
        if ($userId === null) {
            $userId = $viewerId;
        }

        // Check if either user has blocked the other
        $isEitherBlocked = $this->blockModel->isEitherBlocked($viewerId, $userId);
        if ($isEitherBlocked && $userId != $viewerId) {
            // Render simple unavailable message
            include __DIR__ . '/../Views/layout/Header.php';
            echo '<div class="flex items-center justify-center min-h-screen bg-gray-100">
                    <div class="text-center">
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">This account is unavailable</h2>
                        <p class="text-gray-500">You cannot view this profile.</p>
                    </div>
                  </div>';
            include __DIR__ . '/../Views/layout/Sidebar.php';
            include __DIR__ . '/../Views/layout/Rightbar.php';
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

        // Load posts conditionally
        $posts = $canViewPosts ? $this->postModel->getPostsByUser($userId) : [];

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