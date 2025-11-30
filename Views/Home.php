<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Home</title>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[70px]">
    <div class="feed w-full max-w-[700px] mx-auto space-y-6">

      <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
          <div class="post bg-white rounded-xl shadow-md p-6 mb-6 w-full max-w-[700px] hover:shadow-lg transition relative" data-post-id="<?= $post['post_id'] ?>">
            <!-- Author info and privacy icon (privacy icon beside date) -->
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3">
                <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>">
                  <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'assets/default-avatar.png') ?>"
                       alt="profile" class="w-9 h-9 rounded-full object-cover border border-indigo-200 hover:ring-2 hover:ring-indigo-300 transition">
                </a>
                <div>
                  <p class="text-sm font-semibold">
                    <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>" 
                       class="text-gray-800 hover:text-indigo-600 hover:underline transition">
                       <?= htmlspecialchars($post['username']) ?>
                    </a>
                  </p>
                  <div class="flex items-center gap-1 text-xs text-gray-500">
                    <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                    <span title="<?= $post['is_public'] ? 'Public' : 'Private' ?>" class="text-gray-400">
                      <?= $post['is_public'] ? '🌍' : '👥' ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <?php
                $isOwner = ($post['user_id'] === $user['user_id']);
                $isAdminView = !empty($_SESSION['admin_view']) && !empty($user['is_admin']);
                if ($isOwner):
            ?>
              <div class="absolute top-3 right-3 z-20">
                <div class="relative">
                  <button class="post-options flex items-center justify-center w-8 h-8 rounded-full bg-white/70 text-gray-600 hover:text-gray-900 shadow-sm transition" data-post-id="<?= $post['post_id'] ?>" title="Post settings">
                    ⚙️
                  </button>
                  <div class="post-options-menu hidden absolute right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-30 min-w-[150px] overflow-hidden">
                    <button class="edit-post block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition" data-post-id="<?= $post['post_id'] ?>">✏️ Edit</button>
                    <button class="delete-post block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition" data-post-id="<?= $post['post_id'] ?>">🗑️ Delete</button>
                    <button class="toggle-privacy block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                            data-post-id="<?= $post['post_id'] ?>"
                            data-public="<?= $post['is_public'] ?? 1 ?>">
                      <?= (!empty($post['is_public']) && $post['is_public']) ? '👥 Make Private' : '🌍 Make Public' ?>
                    </button>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <?php if (!$isOwner && $isAdminView): ?>
              <div class="absolute top-3 right-3 z-20">
                  <button class="delete-post text-red-600 hover:text-red-800 text-sm" data-post-id="<?= $post['post_id'] ?>">🗑️ Delete</button>
              </div>
            <?php endif; ?>
            <h3 class="text-lg font-semibold text-gray-800 break-words">
              <?= htmlspecialchars(is_array($post['title']) ? implode(', ', $post['title']) : $post['title']) ?>
            </h3>

            <p class="text-gray-600 text-sm mt-1 break-words">
              <?= htmlspecialchars(is_array($post['description']) ? implode(', ', $post['description']) : $post['description']) ?>
            </p>

            <?php if (!empty($post['image_url'])): ?>
              <img src="<?= htmlspecialchars($post['image_url']) ?>"
                   alt="Post image"
                   class="post-image w-full max-h-[500px] object-cover object-center rounded-lg mt-4 shadow-sm">
            <?php endif; ?>

            <?php if (!empty($post['tags'])): ?>
              <div class="mt-2 text-sm text-indigo-500">
                #<?= htmlspecialchars(is_array($post['tags']) ? implode(' #', $post['tags']) : str_replace(',', ' #', $post['tags'])) ?>
              </div>
            <?php endif; ?>

            <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
              <?php $liked = !empty($post['liked']); ?>
              <span data-action="like" class="cursor-pointer transition hover:scale-110" style="<?= $liked ? 'color:#ef4444;' : '#9ca3af;' ?>">
                <?= $liked ? '❤️' : '🤍' ?> <?= $post['likes'] ?>
              </span>
              <span class="comment-toggle cursor-pointer transition hover:scale-110" data-id="<?= $post['post_id'] ?>">💬 <?= htmlspecialchars($post['comment_count'] ?? (is_array($post['comments']) ? count($post['comments']) : 0)) ?></span>
            </div>

            <!-- Inline Comments Section -->
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
        <p class="text-center text-gray-500 italic mt-10">No posts yet.</p>
      <?php endif; ?>

    </div>
  </div>

  <!-- Expose admin view flag for external JS -->
  <script>
    window.IS_ADMIN_VIEW = <?= (!empty($_SESSION['admin_view']) && !empty($user['is_admin'])) ? 'true' : 'false' ?>;
  </script>

  <!-- Split JS files -->
  <script src="scripts/like.js"></script>
  <script src="scripts/comments.js"></script>
  <script src="scripts/home_post.js"></script>
  
</body>
</html>