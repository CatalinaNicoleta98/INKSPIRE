<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
                <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'assets/default-avatar.png') ?>"
                     alt="profile" class="w-9 h-9 rounded-full object-cover border border-indigo-200">
                <div>
                  <p class="text-sm font-semibold text-gray-800">
                    <?= htmlspecialchars($post['username']) ?>
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
            <?php if ($post['user_id'] === $user['user_id']): ?>
              <div class="absolute top-3 right-3 z-20">
                <div class="relative">
                  <button class="post-options flex items-center justify-center w-8 h-8 rounded-full bg-white/70 text-gray-600 hover:text-gray-900 shadow-sm transition" data-post-id="<?= $post['post_id'] ?>" title="Post settings">
                    ⚙️
                  </button>
                  <div class="options-menu hidden absolute right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-30 min-w-[150px] overflow-hidden">
                    <button class="edit-post block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition" data-post-id="<?= $post['post_id'] ?>">✏️ Edit</button>
                    <button class="delete-post block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition" data-post-id="<?= $post['post_id'] ?>">🗑️ Delete</button>
                    <button class="toggle-privacy block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                            data-post-id="<?= $post['post_id'] ?>"
                            data-public="<?= $post['is_public'] ?? 1 ?>">
                      <?= (!empty($post['is_public']) && $post['is_public']) ? '🔒 Make Private' : '🌍 Make Public' ?>
                    </button>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <h3 class="text-lg font-semibold text-gray-800">
              <?= htmlspecialchars(is_array($post['title']) ? implode(', ', $post['title']) : $post['title']) ?>
            </h3>

            <p class="text-gray-600 text-sm mt-1">
              <?= htmlspecialchars(is_array($post['description']) ? implode(', ', $post['description']) : $post['description']) ?>
            </p>

            <?php if (!empty($post['image_url'])): ?>
              <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image" class="w-full rounded-lg mt-4 shadow-sm">
            <?php endif; ?>

            <?php if (!empty($post['tags'])): ?>
              <div class="mt-2 text-sm text-indigo-500">
                #<?= htmlspecialchars(is_array($post['tags']) ? implode(' #', $post['tags']) : str_replace(',', ' #', $post['tags'])) ?>
              </div>
            <?php endif; ?>

            <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
              <span class="like-btn cursor-pointer transition hover:scale-110" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:#ef4444;' : '' ?>">❤️ <?= $post['likes'] ?></span>
              <span class="comment-toggle cursor-pointer transition hover:scale-110" data-id="<?= $post['post_id'] ?>">💬 <?= is_array($post['comments']) ? count($post['comments']) : ($post['comments'] ?? 0) ?></span>
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
  const toggle = e.target.closest('.comment-toggle');
  if (!toggle) return;
  const postId = toggle.dataset.id;
  const section = document.getElementById(`comments-${postId}`);
  if (section) {
    section.classList.toggle('hidden');
    if (!section.dataset.loaded) {
      loadComments(postId);
      section.dataset.loaded = "true";
    }
  }
});
</script>

<script>
async function loadComments(postId) {
  const list = document.querySelector(`#commentsList-${postId}`);
  if (!list) return;
  list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";
  try {
    const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
    const comments = await res.json();
    if (Array.isArray(comments) && comments.length > 0) {
      list.innerHTML = comments.map(c => `
        <div class="comment-item bg-indigo-50 p-2 rounded-md shadow-sm mb-1 flex justify-between items-start" data-comment-id="${c.comment_id}">
          <div>
            <p class="text-gray-700 text-sm">${c.text}</p>
            <p class="text-xs text-gray-500">@${c.username} • ${c.created_at}</p>
          </div>
          ${c.owned ? `
            <div class="relative">
              <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
              <div class="options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
                <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
                <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
              </div>
            </div>` : ''}
        </div>
      `).join('');
    } else {
      list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
    }
  } catch (err) {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

document.addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;
  const postId = submitBtn.dataset.id;
  const input = document.querySelector(`#newCommentInput-${postId}`);
  const text = (input?.value || '').trim();
  if (!text) return;
  try {
    const res = await fetch('index.php?action=addComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(postId)}&text=${encodeURIComponent(text)}`
    });
    const data = await res.json();
    if (data.success) {
      input.value = '';
      loadComments(postId);
    }
  } catch {
    alert('Failed to post comment.');
  }
});

// custom delete confirmation inside DWP
document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;

  // create overlay confirmation
  const overlay = document.createElement('div');
  overlay.className = "fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50";
  overlay.innerHTML = `
    <div class="bg-white rounded-lg p-4 text-center shadow-lg max-w-xs w-full">
      <p class="text-gray-700 mb-4 text-sm">Are you sure you want to delete this comment?</p>
      <div class="flex justify-center gap-3">
        <button class="cancel-del bg-gray-300 text-gray-700 px-3 py-1 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
});

// handle confirmation actions
document.addEventListener('click', async (e) => {
  const cancel = e.target.closest('.cancel-del');
  const confirm = e.target.closest('.confirm-del');
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
        loadComments(postId);
      }
    } catch {
      alert('Error deleting comment.');
    }
  }
});

// edit existing comment inline within the DWP
document.addEventListener('click', (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;
  const commentItem = editBtn.closest('.comment-item');
  // This ensures the comment element is found and highlights it during editing.
  const textEl = commentItem.querySelector('p.text-gray-700');
  if (!textEl) {
    console.warn('Edit failed: comment text element not found.');
    return;
  }
  const oldText = textEl.textContent.trim();
  commentItem.dataset.oldText = oldText;
  commentItem.classList.add('ring-2', 'ring-indigo-300', 'bg-indigo-50'); // small visual highlight

  // create textarea for editing (handles nested structure)
  const textarea = document.createElement('textarea');
  textarea.value = oldText;
  textarea.className = "w-full text-sm border border-indigo-200 rounded-md p-1 mb-1 focus:ring-2 focus:ring-indigo-300 focus:outline-none";
  // insert before the old text element, then remove it
  textEl.parentNode.insertBefore(textarea, textEl);
  textEl.remove();

  // create action buttons
  const actions = document.createElement('div');
  actions.className = "flex gap-2 mt-1";
  actions.innerHTML = `
    <button class="save-edit bg-indigo-500 text-white text-xs px-3 py-1 rounded-md hover:bg-indigo-600 transition" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
    <button class="cancel-edit bg-gray-300 text-gray-700 text-xs px-3 py-1 rounded-md hover:bg-gray-400 transition">Cancel</button>
  `;
  commentItem.appendChild(actions);
});

// handle save and cancel for inline edit
document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  const cancelBtn = e.target.closest('.cancel-edit');

  if (cancelBtn) {
    const commentItem = cancelBtn.closest('.comment-item');
    const textarea = commentItem.querySelector('textarea');
    // Restore original comment text on cancel
    const oldText = commentItem.dataset.oldText || '';
    const p = document.createElement('p');
    p.className = "text-gray-700 text-sm";
    p.textContent = oldText;
    textarea.parentNode.insertBefore(p, textarea);
    textarea.remove();
    cancelBtn.parentElement.remove();
    commentItem.classList.remove('ring-2', 'ring-indigo-300', 'bg-indigo-50');
    return;
  }

  if (saveBtn) {
    const commentItem = saveBtn.closest('.comment-item');
    const textarea = commentItem.querySelector('textarea');
    const commentId = saveBtn.dataset.commentId;
    const postId = saveBtn.dataset.postId;
    const newText = textarea.value.trim();
    if (!newText) return;
    try {
      const res = await fetch('index.php?action=editComment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
      });
      const data = await res.json();
      if (data.success) {
        commentItem.classList.remove('ring-2', 'ring-indigo-300', 'bg-indigo-50');
        loadComments(postId);
      }
    } catch {
      alert('Error updating comment.');
    }
  }
});

// handle comment options menu toggle
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.comment-options');
  const openMenus = document.querySelectorAll('.options-menu:not(.hidden)');
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
  const openMenus = document.querySelectorAll('.options-menu:not(.hidden)');
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
  form.innerHTML = `
    <input type="text" class="edit-title w-full border border-indigo-200 rounded-md p-2 mb-2" value="${oldTitle}">
    <textarea class="edit-description w-full border border-indigo-200 rounded-md p-2 mb-2">${oldDesc}</textarea>
    <div class="flex gap-2">
      <button class="save-edit bg-indigo-500 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-600" data-post-id="${postId}">Save</button>
      <button class="cancel-edit bg-gray-300 text-gray-700 px-3 py-1 rounded-md text-sm hover:bg-gray-400">Cancel</button>
    </div>
  `;
  postCard.querySelector('.options-menu').classList.add('hidden');
  titleEl.replaceWith(form);
});

document.addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('.save-edit');
  const cancelBtn = e.target.closest('.cancel-edit');

  if (cancelBtn) {
    window.location.reload();
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
        alert('Post updated successfully!');
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
    <div class="bg-white rounded-lg p-5 text-center shadow-lg max-w-xs w-full">
      <p class="text-gray-700 mb-4 text-sm">Are you sure you want to delete this post?</p>
      <div class="flex justify-center gap-3">
        <button class="cancel-del bg-gray-300 text-gray-700 px-3 py-1 rounded-md hover:bg-gray-400 transition">Cancel</button>
        <button class="confirm-del bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition" data-post-id="${postId}">Delete</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
});

document.addEventListener('click', async (e) => {
  const cancel = e.target.closest('.cancel-del');
  const confirm = e.target.closest('.confirm-del');
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
        alert('Post deleted!');
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
      alert('Privacy updated!');
      window.location.reload();
    } else {
      alert('Error updating privacy.');
    }
  } catch {
    alert('Privacy request failed.');
  }
});
</script>
</html>