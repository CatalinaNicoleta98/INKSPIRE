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
            <div class="flex gap-2">
              <button class="edit-comment text-indigo-500 hover:text-indigo-700 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">✎</button>
              <button class="delete-comment text-red-400 hover:text-red-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">✕</button>
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

document.addEventListener('click', async (e) => {
  const delBtn = e.target.closest('.delete-comment');
  if (!delBtn) return;
  const commentId = delBtn.dataset.commentId;
  const postId = delBtn.dataset.postId;
  if (!confirm('Delete this comment?')) return;
  try {
    const res = await fetch('index.php?action=deleteComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}`
    });
    const data = await res.json();
    if (data.success) loadComments(postId);
    else alert(data.message || 'Error deleting comment.');
  } catch {
    alert('Error sending request.');
  }
});

// edit existing comment inline
document.addEventListener('click', async (e) => {
  const editBtn = e.target.closest('.edit-comment');
  if (!editBtn) return;
  const commentId = editBtn.dataset.commentId;
  const postId = editBtn.dataset.postId;
  const newText = prompt('Edit your comment:');
  if (!newText) return;
  try {
    const res = await fetch('index.php?action=editComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
    });
    const data = await res.json();
    if (data.success) loadComments(postId);
    else alert(data.message || 'Error editing comment.');
  } catch {
    alert('Error sending request.');
  }
});
</script>

</body>
</html>