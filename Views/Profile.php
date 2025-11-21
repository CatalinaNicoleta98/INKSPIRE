<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

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

        <?php if ($profile['user_id'] !== $currentUser['user_id']): ?>
          <div class="flex justify-center gap-4 mt-6">
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

      <?php if (!empty($showPrivateNotice)): ?>
        <p class="text-gray-500 italic mt-6">This profile is private. Only public posts are visible. Follow to see all posts, followers, and following.</p>
        <p class="text-center text-sm text-gray-500 italic mb-4">Showing public posts only.</p>
      <?php endif; ?>

      <!-- Posts Feed -->
      <div class="space-y-6">
        <?php if (!empty($posts)): ?>
          <?php foreach ($posts as $post): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition relative post-card" data-post-id="<?= $post['post_id'] ?>">
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
                  if ($isOwner):
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
              <!-- Inline edit form (hidden by default) -->
              <form class="post-edit-form hidden space-y-2" data-post-id="<?= $post['post_id'] ?>">
                <input type="text" name="title" class="edit-title border border-indigo-300 rounded-md px-2 py-1 w-full font-semibold text-gray-800" value="<?= htmlspecialchars($post['title'] ?? 'Untitled') ?>">
                <textarea name="description" class="edit-desc border border-indigo-300 rounded-md px-2 py-1 w-full text-sm text-gray-700" rows="3"><?= htmlspecialchars($post['description'] ?? '') ?></textarea>
                <div class="flex gap-2 justify-end">
                  <button type="button" class="save-edit-post bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition" data-post-id="<?= $post['post_id'] ?>">Save</button>
                  <button type="button" class="cancel-edit-post bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 transition" data-post-id="<?= $post['post_id'] ?>">Cancel</button>
                </div>
              </form>

              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>"
                     alt="Post Image"
                     class="w-full max-h-[500px] object-cover object-center rounded-lg mt-4 mb-3 shadow-sm">
              <?php endif; ?>

              <?php if (!empty($post['tags'])): ?>
                <div class="mt-2 text-sm text-indigo-500">#<?= str_replace(',', ' #', htmlspecialchars($post['tags'])) ?></div>
              <?php endif; ?>

              <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
                <span class="cursor-pointer transition hover:scale-110" data-action="like">❤️ <?= htmlspecialchars($post['likes'] ?? 0) ?></span>
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

<!-- Modal for delete confirmation -->
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

<script>
let currentPostId = null;
let replyTarget = null;

// Global comment renderer for both loader and add-comment handler
function renderComment(c, postId, level = 0) {
  const repliesCount = Array.isArray(c.replies) ? c.replies.length : 0;
  const repliesHTML = Array.isArray(c.replies)
    ? c.replies.map(r => renderComment(r, postId, level + 1)).join('')
    : '';

  return `
    <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2" data-comment-id="${c.comment_id}">
      <div class="w-full">
        <p class="text-gray-700 text-sm whitespace-pre-wrap">${c.text}</p>
        <p class="text-xs text-gray-500">
          <a href="index.php?action=profile&user_id=${c.user_id}" 
             class="text-indigo-600 hover:underline">@${c.username}</a> • ${c.created_at}
        </p>
        <div class="flex gap-2 mt-1">
          <button class="reply-btn text-xs text-indigo-500" data-comment-id="${c.comment_id}" data-username="${c.username}" data-post-id="${postId}">↩️ Reply</button>
          ${repliesCount > 0
            ? `<button class="toggle-replies text-xs text-indigo-400" data-comment-id="${c.comment_id}" data-post-id="${postId}">Show replies (${repliesCount})</button>`
            : ''}
        </div>
        <div class="replies hidden mt-2 ml-6">${repliesHTML}</div>
      </div>
      ${c.owned
        ? `
          <div class="comment-tools absolute top-2 right-2">
            <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
            <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
              <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
              <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
            </div>
          </div>
        `
        : ''}
    </div>
  `;
}

// --- Open Comments and Load ---
document.addEventListener('click', (e) => {
  const toggle = e.target.closest('.comment-toggle');
  if (!toggle) return;
  const postId = toggle.dataset.id;
  currentPostId = postId;
  const section = document.getElementById(`comments-${postId}`);
  if (section) {
    section.classList.toggle('hidden');
    if (!section.dataset.loaded) {
      loadComments(postId);
      section.dataset.loaded = "true";
    }
  }
});

// --- Load Comments and Replies ---
async function loadComments(postId) {
  const list = document.querySelector(`#commentsList-${postId}`);
  if (!list) return;
  list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";
  try {
    const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
    const comments = await res.json();
    if (Array.isArray(comments) && comments.length > 0) {
      list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
    } else {
      list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
    }
  } catch {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

// --- Reply System ---
document.addEventListener('click', (e) => {
  const replyBtn = e.target.closest('.reply-btn');
  if (!replyBtn) return;

  replyTarget = {
    commentId: replyBtn.dataset.commentId,
    username: replyBtn.dataset.username,
    postId: replyBtn.dataset.postId
  };

  const commentBox = document.getElementById(`newCommentInput-${replyTarget.postId}`);
  const indicator = document.createElement('div');
  indicator.className = "reply-indicator text-xs text-indigo-600 mb-1 flex justify-between items-center";
  indicator.innerHTML = `<span>Replying to @${replyTarget.username}</span> <button class="cancel-reply text-red-500 text-xs">Cancel</button>`;
  const parent = commentBox.closest('.add-comment');
  if (!parent.querySelector('.reply-indicator')) parent.prepend(indicator);
  commentBox.focus();
});

document.addEventListener('click', (e) => {
  if (e.target.closest('.cancel-reply')) {
    const indicator = e.target.closest('.reply-indicator');
    if (indicator) indicator.remove();
    replyTarget = null;
  }
});

// --- Show/Hide Replies ---
document.addEventListener('click', (e) => {
  const toggleBtn = e.target.closest('.toggle-replies');
  if (!toggleBtn) return;
  const commentItem = toggleBtn.closest('.comment-item');
  const replies = commentItem.querySelector('.replies');
  if (!replies) return;
  const isHidden = replies.classList.contains('hidden');
  replies.classList.toggle('hidden');
  toggleBtn.textContent = isHidden ? 'Hide replies' : `Show replies (${replies.children.length})`;
});

// --- Submit Comment or Reply ---
document.addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;
  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation();

  const postId = submitBtn.dataset.postId;
  const input = document.querySelector(`#newCommentInput-${postId}`);
  const text = input.value.trim();
  if (!text) return;

  const parentId = replyTarget && replyTarget.postId === postId ? replyTarget.commentId : null;

  try {
    const res = await fetch('index.php?action=addComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}&text=${encodeURIComponent(text)}${parentId ? `&parent_id=${encodeURIComponent(parentId)}` : ''}`
    });
    const data = await res.json();
    if (data.success) {
      input.value = '';
      replyTarget = null;
      const indicator = document.querySelector('.reply-indicator');
      if (indicator) indicator.remove();

      const list = document.querySelector(`#commentsList-${postId}`);
      if (!list) return;
      // Remove 'No comments yet' placeholder if present
      const emptyMsg = list.querySelector('.text-center.text-gray-400.italic');
      if (emptyMsg) emptyMsg.remove();
      const newComment = data.comment;
      const html = renderComment(newComment, postId);

      if (parentId) {
        const parentItem = list.querySelector(`.comment-item[data-comment-id="${parentId}"]`);
        const repliesContainer = parentItem ? parentItem.querySelector('.replies') : null;
        if (repliesContainer) {
          repliesContainer.classList.remove('hidden');
          repliesContainer.insertAdjacentHTML('beforeend', html);
          const toggleBtn = parentItem.querySelector(`.toggle-replies[data-comment-id="${parentId}"]`);
          if (toggleBtn) toggleBtn.textContent = 'Hide replies';
          const newEl = repliesContainer.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
          if (newEl) newEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      } else {
        // Preserve scroll position relative to viewport to avoid post jump
        const prevHeight = list.scrollHeight;
        list.insertAdjacentHTML('afterbegin', html);
        const newHeight = list.scrollHeight;
        const heightDiff = newHeight - prevHeight;

        // Adjust page scroll by the height difference so the post doesn't move
        window.scrollBy(0, heightDiff);

        // Then smoothly scroll to the new comment
        requestAnimationFrame(() => {
          const newEl = list.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
          if (newEl) newEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
      }

      // Keep section open
      const section = document.getElementById(`comments-${postId}`);
      if (section) {
        section.classList.remove('hidden');
        section.dataset.loaded = "true";
      }

      // Update comment counter
      const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
      if (commentToggle && data.count !== undefined) {
        commentToggle.innerHTML = `💬 ${data.count}`;
      }
    }
  } catch {
    alert('Failed to post comment.');
  }
});

// --- Menu Toggle (Edit/Delete) ---
document.addEventListener('click', (e) => {
  const menuButton = e.target.closest('.comment-options');
  const openMenu = document.querySelector('.comment-options-menu:not(.hidden)');
  if (!menuButton && !e.target.closest('.comment-options-menu')) {
    if (openMenu) openMenu.classList.add('hidden');
    return;
  }
  if (menuButton) {
    const menu = menuButton.nextElementSibling;
    if (menu.classList.contains('hidden')) {
      if (openMenu) openMenu.classList.add('hidden');
      menu.classList.remove('hidden');
    } else {
      menu.classList.add('hidden');
    }
  }
});

// --- Edit Comment ---
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;

  const commentItem = editBtn.closest('.comment-item');
  const textEl = commentItem.querySelector('p.text-gray-700');
  const originalText = textEl.textContent.trim();
  commentItem.dataset.originalText = originalText;

  textEl.outerHTML = `
    <div class="edit-container mt-1">
      <textarea class="edit-textarea w-full border border-indigo-300 rounded-md p-2 text-sm">${originalText}</textarea>
      <div class="flex justify-end gap-2 mt-2">
        <button class="save-edit bg-indigo-500 text-white text-xs px-3 py-1 rounded-md" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
        <button class="cancel-edit bg-gray-200 text-xs px-3 py-1 rounded-md">Cancel</button>
      </div>
    </div>
  `;
  const optionsMenu = editBtn.closest('.comment-options-menu');
  if (optionsMenu) optionsMenu.classList.add('hidden');
});

// --- Save Edited Comment (No Page Reload) ---
document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  if (!saveBtn) return;

  const commentItem = saveBtn.closest('.comment-item');
  const textarea = commentItem.querySelector('.edit-textarea');
  const newText = textarea.value.trim();
  const commentId = saveBtn.dataset.commentId;
  if (!newText) return;

  try {
    const res = await fetch('index.php?action=editComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
    });
    const data = await res.json();

    if (data.success) {
      // Replace textarea with updated comment text instantly
      const newComment = document.createElement('p');
      newComment.className = 'text-gray-700 text-sm';
      newComment.textContent = newText;
      commentItem.querySelector('.edit-container').replaceWith(newComment);
    }
  } catch {
    alert('Failed to update comment.');
  }
});

// --- Cancel Edit (Instantly Revert Without Reload) ---
document.addEventListener('click', (e) => {
  const cancelBtn = e.target.closest('.cancel-edit');
  if (!cancelBtn) return;

  const commentItem = cancelBtn.closest('.comment-item');
  const originalText = commentItem.dataset.originalText || '';
  const editContainer = commentItem.querySelector('.edit-container');
  if (editContainer) {
    const originalEl = document.createElement('p');
    originalEl.className = 'text-gray-700 text-sm';
    originalEl.textContent = originalText;
    editContainer.replaceWith(originalEl);
  }
});

// --- Delete Comment Confirmation ---
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;
  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50";
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-delete bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-delete bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
      </div>
    </div>`;
  document.body.appendChild(overlay);
});

document.addEventListener('click', async (e) => {
  const confirmBtn = e.target.closest('.confirm-delete');
  const cancelBtn = e.target.closest('.cancel-delete');
  if (!confirmBtn && !cancelBtn) return;
  const overlay = e.target.closest('.fixed.inset-0.bg-black.bg-opacity-40');
  if (cancelBtn && overlay) overlay.remove();
  if (confirmBtn) {
    const commentId = confirmBtn.dataset.commentId;
    const postId = confirmBtn.dataset.postId;

    try {
      const res = await fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `comment_id=${encodeURIComponent(commentId)}&post_id=${encodeURIComponent(postId)}`
      });
      const data = await res.json();
      if (data.success) {
        overlay.remove();

        // Instantly remove comment from DOM
        const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        if (commentItem) commentItem.remove();

        // Update counter dynamically if available
        const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
        if (commentToggle && data.count !== undefined) {
          commentToggle.innerHTML = `💬 ${data.count}`;
        }
      }
    } catch {
      alert('Failed to delete comment.');
    }
  }
});
</script>

<script>
// Toggle post settings menu
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.post-options');
  const openMenus = document.querySelectorAll('.post-options-menu:not(.hidden)');
  openMenus.forEach(m => m.classList.add('hidden'));
  if (btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');
    e.stopPropagation();
  }
});

// Close all post menus when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.post-options-menu') && !e.target.closest('.post-options')) {
    document.querySelectorAll('.post-options-menu').forEach(m => m.classList.add('hidden'));
  }
});

// Edit post
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-post');
  if (!editBtn) return;
  const postId = editBtn.dataset.postId;
  const card = document.querySelector(`.post-card[data-post-id="${postId}"]`);
  card.querySelector('.post-options-menu').classList.add('hidden');
  card.querySelector('.post-content-view').classList.add('hidden');
  card.querySelector('.post-edit-form').classList.remove('hidden');
});

// Save edit
document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit-post');
  if (!saveBtn) return;
  const postId = saveBtn.dataset.postId;
  const card = document.querySelector(`.post-card[data-post-id="${postId}"]`);
  const title = card.querySelector('.edit-title').value.trim();
  const desc = card.querySelector('.edit-desc').value.trim();
  if (!title) return alert('Title cannot be empty.');
  const res = await fetch('index.php?action=editPost', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `post_id=${postId}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(desc)}`
  });
  const data = await res.json();
  if (data.success) location.reload();
  else alert('Error updating post.');
});

// Cancel edit
document.addEventListener('click', (e) => {
  const cancelBtn = e.target.closest('.cancel-edit-post');
  if (!cancelBtn) return;
  const postId = cancelBtn.dataset.postId;
  const card = document.querySelector(`.post-card[data-post-id="${postId}"]`);
  card.querySelector('.post-edit-form').classList.add('hidden');
  card.querySelector('.post-content-view').classList.remove('hidden');
});

// Delete post
document.addEventListener('click', (e) => {
  const delBtn = e.target.closest('.delete-post');
  if (!delBtn) return;
  const postId = delBtn.dataset.postId;
  document.getElementById('deletePostModal').classList.remove('hidden');
  window.postToDelete = postId;
});

document.getElementById('cancelDeletePostBtn').addEventListener('click', () => {
  window.postToDelete = null;
  document.getElementById('deletePostModal').classList.add('hidden');
});

document.getElementById('confirmDeletePostBtn').addEventListener('click', async () => {
  if (!window.postToDelete) return;
  const res = await fetch('index.php?action=deletePost', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `post_id=${encodeURIComponent(window.postToDelete)}`
  });
  const data = await res.json();
  if (data.success) location.reload();
  else alert('Error deleting post.');
  document.getElementById('deletePostModal').classList.add('hidden');
  window.postToDelete = null;
});

// Privacy toggle
document.addEventListener('click', async (e) => {
  const privacyBtn = e.target.closest('.privacy-post');
  if (!privacyBtn) return;
  const postId = privacyBtn.dataset.postId;
  const isPublic = privacyBtn.dataset.public === '1' ? 0 : 1;
  const res = await fetch('index.php?action=changePrivacy', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `post_id=${encodeURIComponent(postId)}&is_public=${encodeURIComponent(isPublic)}`
  });
  const data = await res.json();
  if (data.success) location.reload();
  else alert('Error changing privacy.');
});

// --- Block User Confirmation ---
document.addEventListener('click', (e) => {
  const blockBtn = e.target.closest('a[href*="action=block"]');
  if (!blockBtn) return;
  e.preventDefault();
  window.userToBlock = blockBtn.href;
  document.getElementById('blockUserModal').classList.remove('hidden');
});

document.getElementById('cancelBlockBtn').addEventListener('click', () => {
  document.getElementById('blockUserModal').classList.add('hidden');
  window.userToBlock = null;
});

document.getElementById('confirmBlockBtn').addEventListener('click', () => {
  if (window.userToBlock) {
    window.location.href = window.userToBlock;
  }
});
</script>
<script>
// Like / Unlike post on Profile page (final reliable version)
document.addEventListener('click', async (e) => {
  // Detect if clicked element or its parent is the like button
  const likeSpan = e.target.closest('[data-action="like"]');
  if (!likeSpan) return;

  // Get the post card and ID
  const card = likeSpan.closest('[data-post-id]');
  const postId = card?.dataset?.postId;
  if (!postId) {
    console.error('Missing post_id for like/unlike action');
    return;
  }

  try {
    const res = await fetch('index.php?action=toggleLike', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}`
    });
    const data = await res.json();

    if (data.success) {
      likeSpan.innerHTML = `❤️ ${data.likes}`;
    } else {
      alert(data.message || 'Error updating like.');
    }
  } catch (err) {
    console.error(err);
    alert('Error sending like request.');
  }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const profilePic = document.getElementById('profilePic');
  const lightbox = document.getElementById('profilePicLightbox');
  const enlargedPic = document.getElementById('profilePicEnlarged');

  if (profilePic && lightbox && enlargedPic) {
    profilePic.addEventListener('click', () => {
      enlargedPic.src = profilePic.src;
      lightbox.classList.remove('hidden');
    });

    lightbox.addEventListener('click', () => {
      lightbox.classList.add('hidden');
    });
  }
});
</script>
</body>
<script>
// Toggle dropdowns on click (Followers & Following)
document.addEventListener('DOMContentLoaded', () => {
  const followerToggle = document.querySelector('.follower-toggle');
  const followingToggle = document.querySelector('.following-toggle');
  const followerDropdown = followerToggle?.querySelector('.dropdown-menu');
  const followingDropdown = followingToggle?.querySelector('.dropdown-menu');

  function closeAllDropdowns(e) {
    if (!followerToggle.contains(e.target)) followerDropdown?.classList.remove('active');
    if (!followingToggle.contains(e.target)) followingDropdown?.classList.remove('active');
  }

  followerToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    followerDropdown?.classList.toggle('active');
    followingDropdown?.classList.remove('active');
  });

  followingToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    followingDropdown?.classList.toggle('active');
    followerDropdown?.classList.remove('active');
  });

  document.addEventListener('click', closeAllDropdowns);
});
</script>
</html>