<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
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
          <div class="post bg-white rounded-xl shadow-md p-6 mb-6 w-full max-w-[700px] hover:shadow-lg transition relative">
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
                   class="w-full max-h-[500px] object-cover object-center rounded-lg mt-4 shadow-sm">
            <?php endif; ?>

            <?php if (!empty($post['tags'])): ?>
              <div class="mt-2 text-sm text-indigo-500">
                #<?= htmlspecialchars(is_array($post['tags']) ? implode(' #', $post['tags']) : str_replace(',', ' #', $post['tags'])) ?>
              </div>
            <?php endif; ?>

            <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
              <span class="like-btn cursor-pointer transition hover:scale-110" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:#ef4444;' : '' ?>">❤️ <?= $post['likes'] ?></span>
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

  <script>

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

  </script>

<script>
document.addEventListener('click', (e) => {
  // Ignore clicks from comment-related actions to prevent flicker
  if (
    e.target.closest('.comment-submit') ||
    e.target.closest('.add-comment') ||
    e.target.closest('.reply-btn') ||
    e.target.closest('.cancel-reply') ||
    e.target.closest('.js-edit-form') ||
    e.target.closest('.edit-comment') ||
    e.target.closest('.delete-comment')
  ) {
    return;
  }

  const toggle = e.target.closest('.comment-toggle');
  if (!toggle || document.querySelector('.fixed.inset-0.bg-black')) return;
  const postId = toggle.dataset.id;
  const section = document.getElementById(`comments-${postId}`);
  if (section) {
    section.classList.toggle('hidden');
    if (!section.dataset.loaded) {
      loadComments(postId).then(() => {
        section.dataset.loaded = "true";
      });
    }
  }
});
</script>

<script>
const IS_ADMIN_VIEW = <?= (!empty($_SESSION['admin_view']) && !empty($user['is_admin'])) ? 'true' : 'false' ?>;
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
      ${(c.owned || IS_ADMIN_VIEW)
        ? `
          <div class="comment-tools absolute top-2 right-2">
            <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
            <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
              ${c.owned ? `<button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>` : ''}
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

// Show/hide replies
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

// Override comment submission to include reply target
document.addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;
  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation(); // stronger guard to prevent any other click handlers from firing
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
        // Remove 'No comments yet' placeholder if present
        const emptyMsg = list.querySelector('.text-center.text-gray-400.italic');
        if (emptyMsg) emptyMsg.remove();
        // Preserve scroll position
        const prevScroll = list.scrollTop;
        const wasAtBottom = Math.abs(list.scrollHeight - list.scrollTop - list.clientHeight) < 5;

        // Build from backend payload
        const newComment = data.comment;
        // Render HTML using global renderer
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

        // --- PATCH: Keep section open and mark as loaded ---
        const section = document.getElementById(`comments-${postId}`);
        if (section) {
          section.classList.remove('hidden');
          section.dataset.loaded = "true";
        }
        // --- PATCH: Scroll to the new comment/reply ---
        const newEl = list.querySelector(`.comment-item[data-comment-id="${newComment.comment_id}"]`);
        if (newEl) {
          newEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Restore scroll position to avoid jump (if not scrolled to new comment)
        if (wasAtBottom) {
          list.scrollTop = list.scrollHeight;
        } else {
          list.scrollTop = prevScroll;
        }
      }

      // Update the comment count on the post dynamically
      const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
      if (commentToggle && data.count !== undefined) {
        commentToggle.innerHTML = `💬 ${data.count}`;
      }
    }
  } catch {
    alert('Failed to post comment.');
  }
});

// custom delete confirmation inside DWP
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  e.stopPropagation();
  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;

  // create overlay confirmation (unified design)
  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50";
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-del bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del-comment bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  // Prevent background clicks from toggling comments, but allow overlay buttons to work
  overlay.addEventListener('click', ev => {
    const isModalBackground = ev.target === overlay;
    const isButton = ev.target.closest('.cancel-del, .confirm-del-comment');
    if (isModalBackground && !isButton) ev.stopPropagation();
  });
  document.body.appendChild(overlay);
});

// handle confirmation actions
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

        // Find and remove the deleted comment smoothly (without reload)
        const deleted = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        if (deleted) {
          deleted.style.transition = 'opacity 0.3s ease';
          deleted.style.opacity = '0';
          setTimeout(() => deleted.remove(), 300);
        }

        // Update the comment count dynamically
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

// Inline edit for comments (profile-style: non-destructive DOM updates)
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;
  e.preventDefault();

  const commentItem = editBtn.closest('.comment-item');
  if (!commentItem) return;

  // Target ONLY the text paragraph, not the whole container
  const textEl = commentItem.querySelector('p.text-gray-700.text-sm');
  if (!textEl) return;

  // Prevent multiple editors on the same comment
  if (commentItem.querySelector('.js-edit-form')) return;

  // Preserve original plain text for cancel
  if (!commentItem.dataset.originalText) {
    commentItem.dataset.originalText = textEl.textContent;
  }

  // Build lightweight edit form
  const form = document.createElement('div');
  form.className = 'js-edit-form mt-2';
  form.innerHTML = `
    <textarea class="js-edit-text w-full border border-indigo-300 rounded-md p-2 text-sm resize-y focus:ring-2 focus:ring-indigo-300 focus:outline-none">${textEl.textContent.trim()}</textarea>
    <div class="mt-2 flex justify-end gap-2">
      <button class="js-save-edit bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
      <button class="js-cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 transition" type="button">Cancel</button>
    </div>
  `;

  // Hide original text and insert editor right after it, keeping replies, buttons, etc.
  textEl.hidden = true;
  textEl.insertAdjacentElement('afterend', form);

  // Hide options menu if open
  const optionsMenu = editBtn.closest('.comment-options-menu');
  if (optionsMenu) optionsMenu.classList.add('hidden');

  // Focus textarea
  const ta = form.querySelector('.js-edit-text');
  ta.focus();
  ta.setSelectionRange(ta.value.length, ta.value.length);
});

// Save & Cancel handlers (non-destructive, no full list reload)
document.addEventListener('click', async (e) => {
  // Cancel
  const cancelBtn = e.target.closest('.js-cancel-edit');
  if (cancelBtn) {
    const form = cancelBtn.closest('.js-edit-form');
    const commentItem = cancelBtn.closest('.comment-item');
    const textEl = commentItem.querySelector('p.text-gray-700.text-sm');
    // Restore original text and clean up
    if (commentItem.dataset.originalText !== undefined) {
      textEl.textContent = commentItem.dataset.originalText;
    }
    textEl.hidden = false;
    form.remove();
    return;
  }

  // Save
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

  // Disable controls while saving
  Array.from(form.querySelectorAll('button, textarea')).forEach(el => el.disabled = true);

  try {
    const res = await fetch('index.php?action=editComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
    });
    const data = await res.json();
    if (data.success) {
      // Update just the text content in place (safe: textContent)
      textEl.textContent = newText;
      textEl.hidden = false;
      form.remove();
      // Update cache for subsequent cancels
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

// handle comment options menu toggle
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
<!--
  // 3-dot post management menu for feed posts (edit, delete, toggle privacy)
-->
<script>
// 3-dot post management menu for feed posts (edit, delete, toggle privacy)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.post-options');
  const openMenus = document.querySelectorAll('.post-options-menu:not(.hidden)');
  openMenus.forEach(m => m.classList.add('hidden'));
  if (btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');
  }
});

// inline edit post in feed
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-post');
  if (!editBtn) return;
  const postCard = editBtn.closest('.post');
  const postId = editBtn.dataset.postId;
  const titleEl = postCard.querySelector('h3');
  const descEl = postCard.querySelector('p.text-gray-600');
  const oldTitle = titleEl ? titleEl.textContent.trim() : '';
  const oldDesc = descEl ? descEl.textContent.trim() : '';

  const form = document.createElement('div');
  form.className = 'post-edit-form';
  // persist originals so Cancel can restore without reload
  form.dataset.originalTitle = oldTitle;
  form.dataset.originalDesc = oldDesc;
  form.innerHTML = `
    <input type="text" class="edit-title w-full border border-indigo-200 rounded-md p-2 mb-2 font-semibold" value="${oldTitle}">
    <textarea class="edit-description w-full border border-indigo-200 rounded-md p-2 mb-2 text-sm">${oldDesc}</textarea>
    <div class="flex justify-end gap-2">
      <button class="save-edit bg-indigo-500 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-600" data-post-id="${postId}">Save</button>
      <button class="cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded-md text-sm hover:bg-gray-400">Cancel</button>
    </div>
  `;
  postCard.querySelector('.post-options-menu').classList.add('hidden');
  titleEl.replaceWith(form);
});

document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  const cancelBtn = e.target.closest('.cancel-edit');

  if (cancelBtn) {
    const form = cancelBtn.closest('.post-edit-form');
    const postCard = cancelBtn.closest('.post');
    if (!form || !postCard) return;

    const originalTitle = form.dataset.originalTitle || '';
    const originalDesc = form.dataset.originalDesc || '';

    // Remove any existing description paragraph to prevent duplicates
    const existingDesc = postCard.querySelector('p.text-gray-600.text-sm.mt-1');
    if (existingDesc) existingDesc.remove();

    const titleEl = document.createElement('h3');
    titleEl.className = 'text-lg font-semibold text-gray-800';
    titleEl.textContent = originalTitle;

    const descEl = document.createElement('p');
    descEl.className = 'text-gray-600 text-sm mt-1';
    descEl.textContent = originalDesc;

    form.replaceWith(titleEl);
    titleEl.insertAdjacentElement('afterend', descEl);
    return;
  }

  if (saveBtn) {
    const postId = saveBtn.dataset.postId;
    const postCard = saveBtn.closest('.post');
    const title = postCard.querySelector('.edit-title').value.trim();
    const description = postCard.querySelector('.edit-description').value.trim();
    if (!title || !description) return alert('Please fill all fields.');

    try {
      const res = await fetch('index.php?action=editPost', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}`
      });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert('Error updating post.');
      }
    } catch {
      alert('Request failed.');
    }
  }
});

// delete post confirmation modal
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-post');
  if (!delBtn) return;
  const postId = delBtn.dataset.postId;

  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50";
  overlay.innerHTML = `
    <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
      <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this post?</p>
      <div class="flex justify-center gap-4">
        <button class="cancel-del bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del-post bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
});

document.addEventListener('click', async (e) => {
  const cancel = e.target.closest('.cancel-del');
  const confirm = e.target.closest('.confirm-del-post');
  const overlay = document.querySelector('.fixed.inset-0.bg-black');
  if (cancel && overlay) overlay.remove();
  if (confirm) {
    const postId = confirm.dataset.postId;
    try {
      const res = await fetch('index.php?action=deletePost', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${encodeURIComponent(postId)}`
      });
      const data = await res.json();
      if (data.success) {
        overlay.remove();
        window.location.reload();
      } else {
        alert('Error deleting post.');
      }
    } catch {
      alert('Delete request failed.');
    }
  }
});

// toggle post privacy
document.addEventListener('click', async (e) => {
  const privacyBtn = e.target.closest('.toggle-privacy');
  if (!privacyBtn) return;
  const postId = privacyBtn.dataset.postId;
  const isPublic = privacyBtn.dataset.public === '1' ? 0 : 1;

  try {
    const res = await fetch('index.php?action=changePrivacy', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}&is_public=${encodeURIComponent(isPublic)}`
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      console.error('Error updating privacy.');
    }
  } catch {
    console.error('Privacy request failed.');
  }
});
</script>
</html>