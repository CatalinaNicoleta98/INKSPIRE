<?php require_once __DIR__ . '/../../helpers/Session.php'; ?>
<?php include __DIR__ . '/../layout/Header.php'; ?>
<?php include __DIR__ . '/../layout/Sidebar.php'; ?>
<?php include __DIR__ . '/../layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Post</title>
</head>

<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[70px]">
    <div class="feed w-full max-w-[700px] mx-auto space-y-6">

      <?php if (!empty($posts)): ?>
        <?php $post = $posts[0]; ?>
        
        <div class="post bg-white rounded-xl shadow-md p-6 mb-6 w-full max-w-[700px] hover:shadow-lg transition relative">

          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>">
                <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'uploads/default_avatar.png') ?>"
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
                  <span class="text-gray-400">
                    <?= $post['is_public'] ? '🌍' : '👥' ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <h3 class="text-lg font-semibold text-gray-800 break-words">
            <?= htmlspecialchars($post['title']) ?>
          </h3>

          <p class="text-gray-600 text-sm mt-1 break-words">
            <?= htmlspecialchars($post['description']) ?>
          </p>

          <?php if (!empty($post['image_url'])): ?>
            <img src="<?= htmlspecialchars($post['image_url']) ?>"
                 class="w-full max-h-[500px] object-cover object-center rounded-lg mt-4 shadow-sm">
          <?php endif; ?>

          <?php if (!empty($post['tags'])): ?>
            <div class="mt-2 text-sm text-indigo-500">
              #<?= htmlspecialchars(str_replace(',', ' #', $post['tags'])) ?>
            </div>
          <?php endif; ?>

          <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
            <span class="like-btn cursor-pointer transition hover:scale-110" 
                  data-id="<?= $post['post_id'] ?>" 
                  style="<?= !empty($post['liked']) ? 'color:#ef4444;' : '' ?>">
              ❤️ <?= $post['likes'] ?>
            </span>

            <span class="comment-toggle cursor-pointer transition hover:scale-110" 
                  data-id="<?= $post['post_id'] ?>">
              💬 <?= count($post['comments'] ?? []) ?>
            </span>
          </div>

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

      <?php endif; ?>
    </div>
  </div>

<script>
// Auto-open comments on single post page
document.addEventListener("DOMContentLoaded", () => {
  const postId = <?= intval($post['post_id']) ?>;
  const section = document.getElementById(`comments-${postId}`);
  const list = document.getElementById(`commentsList-${postId}`);

  if (section && list) {
    section.classList.remove('hidden');

    // Load comments immediately
    fetch(`index.php?action=getCommentsByPost&post_id=${postId}`)
      .then(res => res.json())
      .then(comments => {
        if (Array.isArray(comments) && comments.length > 0) {
          list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
        } else {
          list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
        }
      })
      .catch(() => {
        list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
      });
  }
});
</script>

<script>
// auto-scroll for notifications
<?php if (!empty($_GET['comment_id'])): ?>
document.addEventListener("DOMContentLoaded", () => {
    const highlightId = "<?= intval($_GET['comment_id']) ?>";

    const waitForComments = setInterval(() => {
        const target = document.querySelector(`[data-comment-id='${highlightId}']`);
        if (!target) return;

        clearInterval(waitForComments);

        let current = target.closest('.replies');
        while (current) {
            current.classList.remove('hidden');
            const parentCommentItem = current.closest('.comment-item');
            if (parentCommentItem) {
                const toggleBtn = parentCommentItem.querySelector('.toggle-replies');
                if (toggleBtn) toggleBtn.textContent = "Hide replies";
            }
            current = current.parentElement.closest('.replies');
        }

        target.scrollIntoView({ behavior: "smooth", block: "center" });
        target.classList.add("ring-2", "ring-indigo-400");
        setTimeout(() => target.classList.remove("ring-2", "ring-indigo-400"), 2000);
    }, 200);
});
<?php endif; ?>
</script>

<script>
// Like toggle (same as Home/Profile)
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const postId = btn.getAttribute('data-id');
      const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`);
      const data = await response.json();
      if (data.success) {
        btn.innerHTML = `❤️ ${data.likes}`;
        btn.style.color = data.liked ? '#ef4444' : '#6b7280';
      }
    });
  });
});
</script>

<script>
// Safe global comment renderer (handles missing or flat replies)
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

// --- Unified Comments & Replies UX ---
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
  } catch (err) {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

// Unified reply system
let replyTarget = null;

document.addEventListener('click', (e) => {
  const replyBtn = e.target.closest('.reply-btn');
  if (!replyBtn) return;
  replyTarget = {
    commentId: replyBtn.dataset.commentId,
    username: replyBtn.dataset.username,
    postId: replyBtn.dataset.postId
  };
  const commentBox = document.querySelector(`#newCommentInput-${replyTarget.postId}`);
  const indicator = document.createElement('div');
  indicator.className = "reply-indicator text-xs text-indigo-600 italic mb-1";
  indicator.textContent = `Replying to @${replyTarget.username} `;
  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = "✖";
  cancelBtn.className = "ml-1 text-red-500 hover:text-red-700";
  cancelBtn.addEventListener('click', () => {
    replyTarget = null;
    indicator.remove();
    commentBox.placeholder = "Add a comment...";
  });
  indicator.appendChild(cancelBtn);

  // Remove existing indicators
  const existing = document.querySelector('.reply-indicator');
  if (existing) existing.remove();

  commentBox.parentNode.insertBefore(indicator, commentBox);
  commentBox.placeholder = `Replying to @${replyTarget.username}...`;
  commentBox.focus();
});

document.addEventListener('click', (e) => {
  // Toggle replies
  const toggleBtn = e.target.closest('.toggle-replies');
  if (!toggleBtn) return;
  const commentId = toggleBtn.dataset.commentId;
  const postId = toggleBtn.dataset.postId;
  const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
  if (!commentItem) return;
  const replies = commentItem.querySelector('.replies');
  if (!replies) return;

  if (replies.classList.contains('hidden')) {
    replies.classList.remove('hidden');
    toggleBtn.textContent = `Hide replies (${replies.children.length})`;
  } else {
    replies.classList.add('hidden');
    toggleBtn.textContent = `Show replies (${replies.children.length})`;
  }
});

// Load comments on page load
document.addEventListener('DOMContentLoaded', () => {
  const postId = <?= intval($post['post_id']) ?>;
  loadComments(postId);
});
</script>

<script>
// Override comment submission to include reply target
document.addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;
  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation();

  const postId = submitBtn.dataset.id;
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
      if (list) {
        const emptyMsg = list.querySelector('.text-center.text-gray-400.italic');
        if (emptyMsg) emptyMsg.remove();

        const prevScroll = list.scrollTop;
        const wasAtBottom = Math.abs(list.scrollHeight - list.scrollTop - list.clientHeight) < 5;

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
          }
        } else {
          const prevHeight = list.scrollHeight;
          list.insertAdjacentHTML('afterbegin', html);
          const newHeight = list.scrollHeight;
          const heightDiff = newHeight - prevHeight;
          window.scrollBy(0, heightDiff);

          requestAnimationFrame(() => {
            const newEl = list.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
            if (newEl) newEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          });
        }

        const section = document.getElementById(`comments-${postId}`);
        if (section) {
          section.classList.remove('hidden');
          section.dataset.loaded = 'true';
        }

        const newEl = list.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
        if (newEl) {
          newEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        if (wasAtBottom) {
          list.scrollTop = list.scrollHeight;
        } else {
          list.scrollTop = prevScroll;
        }
      }

      const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
      if (commentToggle && data.count !== undefined) {
        commentToggle.innerHTML = `💬 ${data.count}`;
      }
    }
  } catch {
    alert('Failed to post comment.');
  }
});

// Delete comment with confirmation
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  e.stopPropagation();

  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;

  const overlay = document.createElement('div');
  overlay.className = 'fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50';
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-del bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del-comment bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  overlay.addEventListener('click', ev => {
    const isModalBackground = ev.target === overlay;
    const isButton = ev.target.closest('.cancel-del, .confirm-del-comment');
    if (isModalBackground && !isButton) ev.stopPropagation();
  });
  document.body.appendChild(overlay);
});

// Handle confirmation actions for delete
document.addEventListener('click', async (e) => {
  const cancel = e.target.closest('.cancel-del');
  const confirm = e.target.closest('.confirm-del-comment');
  const overlay = document.querySelector('.fixed.inset-0.bg-black');

  if (cancel && overlay) overlay.remove();

  if (confirm) {
    const commentId = confirm.dataset.commentId;
    const postId = confirm.dataset.postId;
    try {
      const res = await fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `comment_id=${encodeURIComponent(commentId)}`
      });
      const data = await res.json();
      if (data.success) {
        if (overlay) overlay.remove();

        const deleted = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        if (deleted) {
          deleted.style.transition = 'opacity 0.3s ease';
          deleted.style.opacity = '0';
          setTimeout(() => deleted.remove(), 300);
        }

        const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
        if (commentToggle && data.count !== undefined) {
          commentToggle.innerHTML = `💬 ${data.count}`;
        }
      }
    } catch {
      alert('Error deleting comment.');
    }
  }
});

// Inline edit for comments
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;
  e.preventDefault();

  const commentItem = editBtn.closest('.comment-item');
  if (!commentItem) return;

  const textEl = commentItem.querySelector('p.text-gray-700.text-sm');
  if (!textEl) return;

  if (commentItem.querySelector('.js-edit-form')) return;

  if (!commentItem.dataset.originalText) {
    commentItem.dataset.originalText = textEl.textContent;
  }

  const form = document.createElement('div');
  form.className = 'js-edit-form mt-2';
  form.innerHTML = `
    <textarea class="js-edit-text w-full border border-indigo-300 rounded-md p-2 text-sm resize-y focus:ring-2 focus:ring-indigo-300 focus:outline-none">${textEl.textContent.trim()}</textarea>
    <div class="mt-2 flex justify-end gap-2">
      <button class="js-save-edit bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
      <button class="js-cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 transition" type="button">Cancel</button>
    </div>
  `;

  textEl.hidden = true;
  textEl.insertAdjacentElement('afterend', form);

  const optionsMenu = editBtn.closest('.comment-options-menu');
  if (optionsMenu) optionsMenu.classList.add('hidden');

  const ta = form.querySelector('.js-edit-text');
  ta.focus();
  ta.setSelectionRange(ta.value.length, ta.value.length);
});

// Save & Cancel handlers for edit
document.addEventListener('click', async (e) => {
  const cancelBtn = e.target.closest('.js-cancel-edit');
  if (cancelBtn) {
    const form = cancelBtn.closest('.js-edit-form');
    const commentItem = cancelBtn.closest('.comment-item');
    const textEl = commentItem.querySelector('p.text-gray-700.text-sm');
    if (commentItem.dataset.originalText !== undefined) {
      textEl.textContent = commentItem.dataset.originalText;
    }
    textEl.hidden = false;
    form.remove();
    return;
  }

  const saveBtn = e.target.closest('.js-save-edit');
  if (!saveBtn) return;

  const commentItem = saveBtn.closest('.comment-item');
  const form = saveBtn.closest('.js-edit-form');
  const textEl = commentItem.querySelector('p.text-gray-700.text-sm');
  const textarea = form.querySelector('.js-edit-text');

  const commentId = saveBtn.dataset.commentId;
  const postId = saveBtn.dataset.postId;
  const newText = textarea.value.trim();
  if (!newText) return;

  Array.from(form.querySelectorAll('button, textarea')).forEach(el => el.disabled = true);

  try {
    const res = await fetch('index.php?action=editComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
    });
    const data = await res.json();
    if (data.success) {
      textEl.textContent = newText;
      textEl.hidden = false;
      form.remove();
      commentItem.dataset.originalText = newText;
    } else {
      alert('Error updating comment.');
      Array.from(form.querySelectorAll('button, textarea')).forEach(el => el.disabled = false);
    }
  } catch {
    alert('Error updating comment.');
    Array.from(form.querySelectorAll('button, textarea')).forEach(el => el.disabled = false);
  }
});

// Comment options menu toggle
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.comment-options');
  const openMenus = document.querySelectorAll('.comment-options-menu:not(.hidden)');
  openMenus.forEach(m => m.classList.add('hidden'));
  if (btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');
  }
});
</script>

</body>
</html>