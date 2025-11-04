<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php $user = Session::get('user'); ?>
<?php include __DIR__ . '/layout/Header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Explore</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen pt-[70px]">
  <div class="flex justify-center items-center  w-full lg:pr-[250px] md:px-[200px] sm:px-4 box-border">
    <main class="feed w-full max-w-[900px] mx-auto px-2 space-y-6">
      <?php if (!empty($posts)): ?>
        <div class="columns-3 md:columns-2 sm:columns-1 gap-6 [column-fill:_balance]">
          <?php foreach ($posts as $post): ?>
            <div class="post inline-block w-full mb-6 bg-white rounded-xl shadow-md overflow-hidden break-inside-avoid relative transition transform hover:-translate-y-1 hover:shadow-lg">
              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>"
                     alt="Post image"
                     class="w-full max-h-[400px] object-cover object-center cursor-pointer transition-transform duration-300 hover:scale-[1.03] rounded-t-xl">
              <?php endif; ?>
              <div class="p-4">
                <div class="flex items-center justify-between mb-3 relative">
                  <div class="flex items-center gap-3">
                    <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'assets/default-avatar.png') ?>" 
                         alt="profile" class="w-8 h-8 rounded-full object-cover border border-indigo-200">
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
                  <?php if (!empty($user) && isset($user['user_id']) && $user['user_id'] === $post['user_id']): ?>
                  <?php endif; ?>
                </div>

                <?php if (!empty($post['title'])): ?>
                  <h3 class="font-semibold text-indigo-600 text-lg mb-1"><?= htmlspecialchars($post['title']) ?></h3>
                <?php endif; ?>

                <?php if (!empty($post['description'])): ?>
                  <p class="text-gray-700 text-sm mb-2"><?= htmlspecialchars($post['description']) ?></p>
                <?php endif; ?>

                <?php if (!empty($post['tags'])): ?>
                  <p class="text-xs text-indigo-400 italic">#<?= htmlspecialchars(str_replace(',', ' #', $post['tags'])) ?></p>
                <?php endif; ?>
              </div>
              <div class="absolute bottom-3 right-3 bg-black/50 text-white rounded-full px-3 py-1 text-sm flex items-center gap-3">
                <span class="like-btn cursor-pointer transition" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:#f87171;' : '' ?>">❤️ <?= $post['likes'] ?></span>
                <span class="comment-btn cursor-pointer" data-id="<?= $post['post_id'] ?>">💬 <?= $post['comment_count'] ?? count($post['comments'] ?? []) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-500 italic mt-10">No posts yet. Be the first to create one!</p>
      <?php endif; ?>
    </main>

    <?php include __DIR__ . '/layout/Sidebar.php'; ?>
    <?php include __DIR__ . '/layout/Rightbar.php'; ?>
  </div>


  <!-- Post Modal -->
  <div id="postModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[100]">
    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg">
      <span id="closeModal" class="float-right text-gray-500 cursor-pointer text-2xl">&times;</span>
      <h3 class="text-xl font-semibold text-indigo-500 mb-4">Create New Post</h3>
      <form id="createPostForm" method="POST" action="index.php?action=createPost" enctype="multipart/form-data" class="space-y-3">
        <input type="text" name="title" placeholder="Title" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <textarea name="description" placeholder="Description" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"></textarea>
        <input type="text" name="tags" placeholder="Tags (comma-separated)" class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600">
        <button type="button" id="submitPostBtn" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-md py-2 hover:from-indigo-500 hover:to-purple-500 transition">Post</button>
      </form>
      <p id="postError" class="text-center text-red-500 text-sm font-medium hidden"></p>
    </div>
  </div>

  <!-- Lightbox -->
  <div id="lightbox" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-[1000]">
    <img id="lightboxImg" src="" alt="Full image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-lg">
  </div>

  <!-- Comments Modal -->
  <div id="commentsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg relative">
      <span id="closeComments" class="absolute top-3 right-4 text-gray-500 cursor-pointer text-2xl">&times;</span>
      <h3 class="text-xl font-semibold text-indigo-500 mb-4 text-center">Comments</h3>

      <!-- Modal Comments Layout -->
      <div class="flex flex-col h-[70vh]" data-context="modal">
        <div id="commentsList" class="comments-list flex-1 overflow-y-auto text-gray-600 text-sm p-1">
          <p class="text-center text-gray-400 italic">Loading...</p>
        </div>

        <div class="bg-white border-t border-indigo-100 p-2 sticky bottom-0">
          <div class="flex items-center gap-2">
            <input id="newCommentInput" type="text" placeholder="Add a comment..."
                  class="comment-input flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
            <button id="submitComment"
                    class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const postModal = document.getElementById('postModal');
    const closeModal = document.getElementById('closeModal');
    closeModal.onclick = () => postModal.classList.add('hidden');
    window.onclick = e => { if (e.target === postModal) postModal.classList.add('hidden'); };

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    document.querySelectorAll('.post img').forEach(img => {
      img.addEventListener('click', () => {
        lightboxImg.src = img.src;
        lightbox.classList.remove('hidden');
      });
    });
    lightbox.addEventListener('click', () => lightbox.classList.add('hidden'));

    document.querySelectorAll('.like-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const postId = btn.getAttribute('data-id');
        try {
          const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`, { cache: 'no-store' });
          const data = await response.json();
          if (data.success) {
            btn.innerHTML = `❤️ ${data.likes}`;
            btn.style.color = data.liked ? '#f87171' : 'white';
          }
        } catch (err) {
          console.error('Like error', err);
        }
      });
    });

    // Handle post creation via AJAX to display validation errors in modal
    const createPostForm = document.getElementById('createPostForm');
    const submitPostBtn = document.getElementById('submitPostBtn');
    if (createPostForm && submitPostBtn) {
      submitPostBtn.addEventListener('click', async () => {
        const errorEl = document.getElementById('postError');
        if (errorEl) {
          errorEl.textContent = '';
          errorEl.classList.add('hidden');
        }

        const formData = new FormData(createPostForm);
        try {
          const res = await fetch('index.php?action=createPost', { method: 'POST', body: formData });
          const data = await res.json();

          if (data.success) {
            location.reload();
          } else if (data.error) {
            if (errorEl) {
              errorEl.textContent = data.error;
              errorEl.classList.remove('hidden');
            }
          } else {
            if (errorEl) {
              errorEl.textContent = '⚠️ Unexpected error. Please try again.';
              errorEl.classList.remove('hidden');
            }
          }
        } catch (err) {
          if (errorEl) {
            errorEl.textContent = '⚠️ Network or server error. Please try again.';
            errorEl.classList.remove('hidden');
          }
        }
      });
    }
  </script>
</body>
</html>
</body>
</html>
<script>
let currentPostId = null;
let replyTarget = null;

// Renders a single comment (used by loader and live updates)
function renderComment(c, postId, level = 0) {
  const repliesCount = c.replies ? c.replies.length : 0;
  return `
    <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2" data-comment-id="${c.comment_id}">
      <div class="w-full">
        <p class="text-gray-700 text-sm">${c.text}</p>
        <p class="text-xs text-gray-500">@${c.username} • ${c.created_at}</p>
        <div class="flex gap-2 mt-1">
          <button class="reply-btn text-xs text-indigo-500" data-comment-id="${c.comment_id}" data-username="${c.username}" data-post-id="${postId}">↩️ Reply</button>
          ${repliesCount > 0 ? `<button class="toggle-replies text-xs text-indigo-400" data-comment-id="${c.comment_id}" data-post-id="${postId}">Show replies (${repliesCount})</button>` : ''}
        </div>
        <div class="replies hidden mt-2 ml-6">
          ${c.replies && c.replies.length > 0 ? c.replies.map(r => renderComment(r, postId, level + 1)).join('') : ''}
        </div>
      </div>
      ${c.owned ? `
        <div class="comment-tools absolute top-2 right-2">
          <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
          <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
            <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
            <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
          </div>
        </div>` : ''}
    </div>`;
}
// Utility: Scroll to element smoothly within modal
function scrollToComment(commentId) {
  const modal = document.getElementById('commentsModal');
  const commentEl = document.querySelector(`#commentsList .comment-item[data-comment-id="${commentId}"]`);
  if (commentEl && modal) {
    // Use scrollIntoView but only within the modal's scroll context
    commentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

// Open comments modal and load comments for a post
document.addEventListener('click', (e) => {
  const toggle = e.target.closest('.comment-btn');
  if (!toggle) return;
  const postId = toggle.dataset.id;
  currentPostId = postId;
  const commentsModal = document.getElementById('commentsModal');
  const commentsList = document.getElementById('commentsList');
  if (commentsModal && commentsList) {
    commentsModal.classList.remove('hidden');
    loadComments(postId);
  }
  const closeComments = document.getElementById('closeComments');
  if (closeComments) closeComments.onclick = () => {
    commentsModal.classList.add('hidden');
    replyTarget = null;
    const ind = document.querySelector('#commentsModal .reply-indicator');
    if (ind) ind.remove();
  };
});

// Load comments for the current post, optionally scroll to a comment
async function loadComments(postId, { scrollTo = null } = {}) {
  const list = document.getElementById('commentsList');
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
    // If scrollTo, scroll to that comment after render
    if (scrollTo) setTimeout(() => scrollToComment(scrollTo), 100);
  } catch {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

// Handle reply - show indicator above input
document.getElementById('commentsModal').addEventListener('click', (e) => {
  const replyBtn = e.target.closest('.reply-btn');
  if (!replyBtn) return;
  replyTarget = {
    commentId: replyBtn.dataset.commentId,
    username: replyBtn.dataset.username,
    postId: replyBtn.dataset.postId
  };
  const commentBox = document.getElementById('newCommentInput');
  if (!commentBox) return;
  // Remove any existing indicator
  const prev = document.querySelector('#commentsModal .reply-indicator');
  if (prev) prev.remove();
  // Insert indicator
  const indicator = document.createElement('div');
  indicator.className = "reply-indicator text-xs text-indigo-600 mb-1 flex justify-between items-center";
  indicator.innerHTML = `<span>Replying to @${replyTarget.username}</span> <button class="cancel-reply text-red-500 text-xs">Cancel</button>`;
  const parent = commentBox.closest('.bg-white');
  if (parent && !parent.querySelector('.reply-indicator')) parent.prepend(indicator);
  commentBox.focus();
});
// Cancel reply
document.getElementById('commentsModal').addEventListener('click', (e) => {
  if (e.target.closest('.cancel-reply')) {
    const indicator = e.target.closest('.reply-indicator');
    if (indicator) indicator.remove();
    replyTarget = null;
  }
});

// Show/hide replies
document.getElementById('commentsModal').addEventListener('click', (e) => {
  const toggleBtn = e.target.closest('.toggle-replies');
  if (!toggleBtn) return;
  const commentItem = toggleBtn.closest('.comment-item');
  const replies = commentItem.querySelector('.replies');
  if (!replies) return;
  const isHidden = replies.classList.contains('hidden');
  replies.classList.toggle('hidden');
  toggleBtn.textContent = isHidden ? 'Hide replies' : `Show replies (${replies.children.length})`;
});

// Submit comment/reply
document.getElementById('commentsModal').addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;
  const postId = currentPostId;
  const input = document.getElementById('newCommentInput');
  if (!input) return;
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
      // Reset input and reply indicator
      input.value = '';
      const indicator = document.querySelector('#commentsModal .reply-indicator');
      if (indicator) indicator.remove();
      const newComment = data.comment; // expect full comment payload
      // If API only returns IDs, ensure backend sends the comment object (id, text, username, created_at, owned)
      if (newComment && newComment.comment_id) {
        const list = document.getElementById('commentsList');
        if (parentId) {
          // Append to parent's replies and ensure they are visible
          const parentItem = list.querySelector(`.comment-item[data-comment-id="${parentId}"]`);
          if (parentItem) {
            const repliesContainer = parentItem.querySelector('.replies');
            if (repliesContainer) {
              repliesContainer.classList.remove('hidden');
              const toggleBtn = parentItem.querySelector(`.toggle-replies[data-comment-id="${parentId}"]`);
              if (toggleBtn) toggleBtn.textContent = 'Hide replies';
              repliesContainer.insertAdjacentHTML('beforeend', renderComment(newComment, postId));
              // Scroll to the new reply
              setTimeout(() => {
                const el = repliesContainer.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
              }, 50);
            }
          }
        } else {
          // Insert new main comment at the top (optimistic UX)
          list.insertAdjacentHTML('afterbegin', renderComment(newComment, postId));
          // Smooth scroll to the just-added comment
          setTimeout(() => {
            const el = list.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }, 50);
        }
        // Update comment counter on the tile
        const commentToggle = document.querySelector(`.comment-btn[data-id="${postId}"]`);
        if (commentToggle) {
          if (typeof data.count !== 'undefined') {
            commentToggle.innerHTML = `💬 ${data.count}`;
          } else {
            const parts = commentToggle.textContent.trim().split(' ');
            const num = parseInt(parts[1], 10);
            if (!isNaN(num)) commentToggle.innerHTML = `💬 ${num + 1}`;
          }
        }
      }
      // Clear replyTarget last after DOM injection
      replyTarget = null;
    }
  } catch {
    alert('Failed to post comment.');
  }
});

// Inline edit menu toggle (close when clicking outside)
document.addEventListener('click', (e) => {
  // Only affect menus inside modal
  const modal = document.getElementById('commentsModal');
  if (!modal.contains(e.target)) {
    // Clicked outside modal - close all menus
    modal.querySelectorAll('.comment-options-menu').forEach(menu => menu.classList.add('hidden'));
    return;
  }
  const menuButton = e.target.closest('.comment-options');
  const openMenu = modal.querySelector('.comment-options-menu:not(.hidden)');
  // If clicked outside menus/buttons, close all
  if (!menuButton && !e.target.closest('.comment-options-menu')) {
    if (openMenu) openMenu.classList.add('hidden');
    return;
  }
  // Toggle current menu
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

// Edit comment: inline replace with textarea
document.getElementById('commentsModal').addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;
  const commentItem = editBtn.closest('.comment-item');
  if (!commentItem) return;
  const commentId = editBtn.dataset.commentId;
  const postId = editBtn.dataset.postId;
  const textEl = commentItem.querySelector('p.text-gray-700');
  if (!textEl) return;
  // Prevent multiple edit boxes
  if (commentItem.querySelector('textarea')) return;
  const originalText = textEl.textContent.trim();
  textEl.outerHTML = `
    <div class="edit-container mt-1">
      <textarea class="edit-textarea w-full border border-indigo-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">${originalText}</textarea>
      <div class="flex justify-end gap-2 mt-2">
        <button class="save-edit bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded-md" data-comment-id="${commentId}" data-post-id="${postId}">Save</button>
        <button class="cancel-edit bg-gray-200 hover:bg-gray-300 text-xs px-3 py-1 rounded-md">Cancel</button>
      </div>
    </div>
  `;
  // Close any options menu in this comment
  const tools = commentItem.querySelector('.comment-options-menu');
  if (tools) tools.classList.add('hidden');
  // Store original text for cancel
  commentItem.dataset.originalText = `${originalText}`;
});

// Save edited comment (inline update)
document.getElementById('commentsModal').addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  if (!saveBtn) return;
  const commentItem = saveBtn.closest('.comment-item');
  const textarea = commentItem.querySelector('.edit-textarea');
  const newText = textarea.value.trim();
  const commentId = saveBtn.dataset.commentId;
  const postId = saveBtn.dataset.postId;
  if (!newText) return;
  try {
    const res = await fetch('index.php?action=editComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
    });
    const data = await res.json();
    if (data.success) {
      // Replace edit container with updated text node
      const editContainer = commentItem.querySelector('.edit-container');
      if (editContainer) {
        editContainer.outerHTML = `<p class="text-gray-700 text-sm"></p>`;
        const p = commentItem.querySelector('p.text-gray-700');
        if (p) p.textContent = newText; // safe text injection
      }
    }
  } catch {
    alert('Failed to update comment.');
  }
});

// Cancel edit: restore original text without reload
document.getElementById('commentsModal').addEventListener('click', (e) => {
  const cancelBtn = e.target.closest('.cancel-edit');
  if (!cancelBtn) return;
  const commentItem = cancelBtn.closest('.comment-item');
  const orig = commentItem?.dataset?.originalText || '';
  const editContainer = commentItem.querySelector('.edit-container');
  if (editContainer) {
    editContainer.outerHTML = `<p class="text-gray-700 text-sm"></p>`;
    const p = commentItem.querySelector('p.text-gray-700');
    if (p) p.textContent = orig;
  }
});

// Delete comment: show overlay confirmation
document.getElementById('commentsModal').addEventListener('click', (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;
  // Remove any previous overlay
  const prev = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-40');
  if (prev) prev.remove();
  // Unified confirmation overlay
  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[1100]";
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-delete bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-delete bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
});

// Confirm/cancel delete
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
        if (overlay) overlay.remove();
        // Remove the comment element from DOM (replies go with it if parent)
        const list = document.getElementById('commentsList');
        const toRemove = list.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        if (toRemove) toRemove.remove();
        // Update counter on the tile
        const commentToggle = document.querySelector(`.comment-btn[data-id="${postId}"]`);
        if (commentToggle && typeof data.count !== 'undefined') {
          commentToggle.innerHTML = `💬 ${data.count}`;
        }
      }
    } catch {
      alert('Failed to delete comment.');
    }
  }
});
</script>
</body>
</html>