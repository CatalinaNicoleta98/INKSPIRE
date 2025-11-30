<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>
<?php
// Ensure block flags always exist to avoid warnings
$isBlocked = $isBlocked ?? false;
$isAdminBlocked = $isAdminBlocked ?? false;
$isProfileAdminBlocked = $isProfileAdminBlocked ?? false;
$isCurrentUserBlocked = isset($currentUser['is_active']) && (int)$currentUser['is_active'] === 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Profile</title>
  
  <style>
  /* Dropdown styling for click-based toggle */
  .dropdown-menu {
    display: none;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    margin-top: 0.5rem;
    width: 14rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    padding: 0.75rem;
    z-index: 9999;
  }
  .dropdown-menu.active {
    display: block;
  }
  </style>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[70px]">
    <div class="w-full max-w-[700px] mx-auto space-y-8">

      <!-- Profile Header -->
      <div class="bg-white rounded-xl shadow-md p-6 text-center">
        <div class="relative">
          <img src="<?= htmlspecialchars($profile['profile_picture'] ?? 'uploads/default.png') ?>" alt="Profile Picture"
               class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-indigo-200 shadow-sm cursor-pointer"
               id="profilePic">
        </div>
        <!-- Lightbox Modal -->
        <div id="profilePicLightbox" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
          <img src="<?= htmlspecialchars($profile['profile_picture'] ?? 'uploads/default.png') ?>" alt="Enlarged Profile Picture"
               class="max-w-[90%] max-h-[90%] rounded-lg shadow-2xl border-4 border-white" id="profilePicEnlarged">
        </div>
        <h2 class="text-2xl font-semibold text-gray-800 mt-3"><?= htmlspecialchars($profile['username']) ?></h2>
        <p class="text-gray-600 text-sm italic mt-1"><?= htmlspecialchars($profile['bio'] ?? 'No bio yet.') ?></p>

        <div class="flex justify-center gap-10 mt-5 text-gray-700 relative">
          <?php if (!empty($canSeeSocialLists)): ?>
            <!-- Followers -->
            <div class="relative cursor-pointer follower-toggle">
              <strong class="text-indigo-600"><?= htmlspecialchars($profile['followers_count'] ?? 0) ?></strong><br>
              <span class="text-sm">Followers</span>
              <?php if (!empty($followersList)): ?>
                <div class="dropdown-menu">
                  <p class="text-xs text-gray-500 mb-2">Followers:</p>
                  <?php foreach ($followersList as $follower): ?>
                    <a href="index.php?action=profile&amp;user_id=<?= htmlspecialchars($follower['user_id']) ?>" class="flex items-center gap-2 mb-2 hover:bg-indigo-50 p-1 rounded">
                      <img src="<?= htmlspecialchars($follower['profile_picture'] ?? 'uploads/default.png') ?>" class="w-6 h-6 rounded-full object-cover">
                      <span class="text-sm text-gray-700"><?= htmlspecialchars($follower['username']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Following -->
            <div class="relative cursor-pointer following-toggle">
              <strong class="text-indigo-600"><?= htmlspecialchars($profile['following_count'] ?? 0) ?></strong><br>
              <span class="text-sm">Following</span>
              <?php if (!empty($followingList)): ?>
                <div class="dropdown-menu">
                  <p class="text-xs text-gray-500 mb-2">Following:</p>
                  <?php foreach ($followingList as $following): ?>
                    <a href="index.php?action=profile&amp;user_id=<?= htmlspecialchars($following['user_id']) ?>" class="flex items-center gap-2 mb-2 hover:bg-indigo-50 p-1 rounded">
                      <img src="<?= htmlspecialchars($following['profile_picture'] ?? 'uploads/default.png') ?>" class="w-6 h-6 rounded-full object-cover">
                      <span class="text-sm text-gray-700"><?= htmlspecialchars($following['username']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="flex items-center">
              <span class="text-sm text-gray-500 italic">Followers and following are hidden for private profiles.</span>
            </div>
          <?php endif; ?>

          <!-- Posts -->
          <div>
            <strong class="text-indigo-600"><?= count($posts ?? []) ?></strong><br>
            <span class="text-sm">Posts</span>
          </div>
        </div>

        <?php if ($profile['user_id'] !== $currentUser['user_id'] && !$isAdminBlocked && !$isProfileAdminBlocked): ?>
          <div class="flex flex-col items-center gap-2 mt-6">
            <?php if ($isCurrentUserBlocked): ?>
              <div class="flex justify-center gap-4">
                <!-- Disabled Follow / Unfollow Button -->
                <button
                  class="bg-gray-200 text-gray-400 px-4 py-2 rounded-md cursor-not-allowed"
                  type="button"
                  disabled
                >
                  <?= $isFollowing ? 'Following' : 'Follow' ?>
                </button>

                <!-- Disabled Block / Unblock Button -->
                <button
                  class="bg-gray-200 text-gray-400 px-4 py-2 rounded-md cursor-not-allowed"
                  type="button"
                  disabled
                >
                  <?= $isBlocked ? 'Blocked' : 'Block' ?>
                </button>
              </div>
              <p class="text-xs text-gray-500 italic mt-1">
                Your account is blocked. You cannot follow or block other users.
              </p>
            <?php else: ?>
              <div class="flex justify-center gap-4">
                <!-- Follow / Unfollow Button -->
                <?php if ($isFollowing): ?>
                  <a href="index.php?action=unfollow&user_id=<?= htmlspecialchars($profile['user_id']) ?>"
                     class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                     Unfollow
                  </a>
                <?php else: ?>
                  <a href="index.php?action=follow&user_id=<?= htmlspecialchars($profile['user_id']) ?>"
                     class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition">
                     Follow
                  </a>
                <?php endif; ?>

                <!-- Block / Unblock Button -->
                <?php if ($isBlocked): ?>
                  <a href="index.php?action=unblock&user_id=<?= htmlspecialchars($profile['user_id']) ?>"
                     class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                     Unblock
                  </a>
                <?php else: ?>
                  <a href="index.php?action=block&user_id=<?= htmlspecialchars($profile['user_id']) ?>"
                     class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">
                     Block
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>

      <?php if (!empty($isAdminBlocked)): ?>
        <p class="text-center text-red-500 font-semibold italic mt-6">
          You have been blocked by an administrator.
        </p>
      <?php endif; ?>

      <?php if (!empty($isProfileAdminBlocked)): ?>
        <p class="text-center text-red-500 font-semibold italic mt-6">
          This user has been blocked by an administrator.
        </p>
      <?php endif; ?>

      <?php if (!empty($showPrivateNotice)): ?>
        <p class="text-gray-500 italic mt-6">This profile is private. Only public posts are visible. Follow to see all posts, followers, and following.</p>
        <p class="text-center text-sm text-gray-500 italic mb-4">Showing public posts only.</p>
      <?php endif; ?>

      <!-- Posts Feed -->
      <div class="space-y-6">
        <?php if (!empty($isProfileAdminBlocked)): ?>
          <p class="text-center text-gray-500 italic mt-6">No posts available.</p>
        <?php endif; ?>
        <?php if (!empty($posts)): ?>
          <?php foreach ($posts as $post): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition relative post-card" data-post-id="<?= $post['post_id'] ?>">
              <?php if (!empty($post['is_sticky']) && $post['is_sticky'] == 1): ?>
                <div class="absolute top-1 right-3 bg-indigo-300/80 text-white text-xs font-semibold px-2 py-1 rounded-md shadow-sm z-20">
                  📌 Pinned
                </div>
              <?php endif; ?>
              <!-- User Header -->
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <img src="<?= htmlspecialchars($profile['profile_picture'] ?? 'uploads/default.png') ?>"
                       alt="User Avatar"
                       class="w-10 h-10 rounded-full object-cover border border-indigo-100 shadow-sm">
                  <div>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($profile['username']) ?></p>
                    <div class="flex items-center text-xs text-gray-500 gap-2">
                      <span>
                        <?= htmlspecialchars(date('M j, Y', strtotime($post['created_at'] ?? ''))) ?>
                      </span>
                      <span>•</span>
                      <span class="text-indigo-500 privacy-icon"><?= ($post['is_public'] ?? 1) ? '🌍' : '👥' ?></span>
                    </div>
                  </div>
                </div>
              <?php
                  $isOwner = ($post['user_id'] === $currentUser['user_id']);
                  $isAdminView = !empty($_SESSION['admin_view']) && !empty($currentUser['is_admin']);
                  if ($isOwner && !$isAdminBlocked):
              ?>
                <div class="relative">
                  <button class="post-options flex items-center justify-center w-7 h-7 rounded-full bg-white/70 text-gray-600 hover:text-gray-900 shadow-sm transition"
                          data-post-id="<?= $post['post_id'] ?>" data-public="<?= $post['is_public'] ?? 1 ?>" title="Post settings">⚙️</button>
                  <div class="post-options-menu hidden absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-md z-10">
                    <button class="edit-post block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition" data-post-id="<?= $post['post_id'] ?>">✏️ Edit</button>
                    <button class="delete-post block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition" data-post-id="<?= $post['post_id'] ?>">🗑️ Delete</button>
                    <button class="privacy-post block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                            data-post-id="<?= $post['post_id'] ?>" data-public="<?= $post['is_public'] ?? 1 ?>">
                      <?= ($post['is_public'] ?? 1) ? '👥 Make Private' : '🌍 Make Public' ?>
                    </button>
                    <button class="sticky-post block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                            data-post-id="<?= $post['post_id'] ?>"
                            data-sticky="<?= $post['is_sticky'] ?? 0 ?>">
                      <?= ($post['is_sticky'] ?? 0) ? '📌 Unpin from profile' : '📌 Pin on profile' ?>
                    </button>
                  </div>
                </div>
              <?php endif; ?>
              <?php if (!$isOwner && $isAdminView): ?>
                  <div class="absolute top-3 right-3 z-20">
                      <button class="delete-post text-red-600 hover:text-red-800 text-sm" data-post-id="<?= $post['post_id'] ?>">🗑️ Delete</button>
                  </div>
              <?php endif; ?>
              </div>

              <div class="post-content-view" data-post-id="<?= $post['post_id'] ?>">
                <h3 class="text-lg font-semibold text-gray-800 post-title break-words"><?= htmlspecialchars($post['title'] ?? 'Untitled') ?></h3>
                <?php if (!empty($post['description'])): ?>
                  <p class="text-gray-600 text-sm mt-1 post-desc break-words"><?= htmlspecialchars($post['description']) ?></p>
                <?php else: ?>
                  <p class="text-gray-600 text-sm mt-1 post-desc break-words"></p>
                <?php endif; ?>
              </div>

              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>"
                     alt="Post Image"
                     class="post-image w-full max-h-[500px] object-cover object-center rounded-lg mt-4 mb-3 shadow-sm">
              <?php endif; ?>

              <?php if (!empty($post['tags'])): ?>
                <div class="mt-2 text-sm text-indigo-500">#<?= str_replace(',', ' #', htmlspecialchars($post['tags'])) ?></div>
              <?php endif; ?>

              <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
                <?php $liked = !empty($post['liked']); ?>
                <span class="cursor-pointer transition hover:scale-110"
                      data-action="like"
                      style="<?= $liked ? 'color:#ef4444;' : '#9ca3af;' ?>">
                  <?= $liked ? '❤️' : '🤍' ?> <?= htmlspecialchars($post['likes'] ?? 0) ?>
                </span>
                <span class="cursor-pointer transition hover:scale-110 comment-toggle" data-id="<?= $post['post_id'] ?>">💬 <?= htmlspecialchars($post['comments'] ?? 0) ?></span>
              </div>

              <!-- Comments Section -->
              <div class="comments-section mt-4 hidden" id="comments-<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
                <div id="commentsList-<?= $post['post_id'] ?>" 
                     class="comments-list text-gray-600 text-sm space-y-2 max-h-60 overflow-y-auto p-2 bg-gray-50 rounded-md border border-indigo-100"
                     data-post-id="<?= $post['post_id'] ?>">
                  <p class="text-center text-gray-400 italic">Loading comments...</p>
                </div>
                <div class="add-comment flex items-center gap-2 mt-3 bg-white border-t border-indigo-100 pt-2 pb-2 px-2 rounded-b-md sticky bottom-0 z-10" data-post-id="<?= $post['post_id'] ?>">
                  <input type="text" placeholder="Add a comment..."
                         id="newCommentInput-<?= $post['post_id'] ?>"
                         class="comment-input flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none"
                         data-id="<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
                  <button id="submitComment-<?= $post['post_id'] ?>"
                          class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition"
                          data-id="<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center text-gray-500 italic">No posts yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

<!-- Modal for delete confirmation (kept for future use if needed) -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
    <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
    <div class="flex justify-center gap-4">
      <button id="cancelDeleteBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
      <button id="confirmDeleteBtn" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Delete</button>
    </div>
  </div>
</div>

<!-- Modal for post deletion -->
<div id="deletePostModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
    <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this post?</p>
    <div class="flex justify-center gap-4">
      <button id="cancelDeletePostBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
      <button id="confirmDeletePostBtn" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Delete</button>
    </div>
  </div>
</div>

<!-- Modal for blocking confirmation -->
<div id="blockUserModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
    <p class="text-gray-700 mb-5 text-base font-medium">🚫 Are you sure you want to block this user?</p>
    <div class="flex justify-center gap-4">
      <button id="cancelBlockBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
      <button id="confirmBlockBtn" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Block</button>
    </div>
  </div>
</div>

  <!-- Admin view flag for shared comments.js -->
  <script>
    window.IS_ADMIN_VIEW = <?= (!empty($_SESSION['admin_view']) && !empty($currentUser['is_admin'])) ? 'true' : 'false' ?>;
  </script>

  <!-- Shared + profile-specific scripts -->
  <script src="scripts/like.js"></script>
  <script src="scripts/comments.js"></script>
  <script src="scripts/profile_post.js"></script>
  <script src="scripts/profile.js"></script>
</body>
</html>