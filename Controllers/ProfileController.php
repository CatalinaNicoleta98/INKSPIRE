<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/../Models/PostModel.php';
require_once __DIR__ . '/../Models/FollowModel.php';
require_once __DIR__ . '/../Models/BlockModel.php';

class ProfileController {
    private $profileModel;
    private $postModel;
    private $followModel;
    private $blockModel;

    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->postModel = new PostModel();
        $this->followModel = new FollowModel();
        $this->blockModel = new BlockModel();
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
        header("Location: index.php?action=profile&user_id=" . $blockedId);
        exit();
    }

    // Unblock a user
    public function unblock($blockedId) {
        Session::start();
        $blockerId = Session::get('user')['user_id'];
        if ($blockerId == $blockedId) return;

        $this->blockModel->unblockUser($blockerId, $blockedId);
        header("Location: index.php?action=profile&user_id=" . $blockedId);
        exit();
    }
}
?>