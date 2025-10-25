<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
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
            <div class="post inline-block w-full mb-6 bg-white rounded-xl shadow-md overflow-hidden break-inside-avoid transition transform hover:-translate-y-1 hover:shadow-lg">
              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image" class="w-full object-cover cursor-pointer transition-transform duration-300 hover:scale-[1.03]">
              <?php endif; ?>
              <div class="absolute bottom-3 right-3 bg-black/50 text-white rounded-full px-3 py-1 text-sm flex items-center gap-3">
                <span class="like-btn cursor-pointer transition" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:#f87171;' : '' ?>">❤️ <?= $post['likes'] ?></span>
                <span class="comment-btn cursor-pointer" data-id="<?= $post['post_id'] ?>">💬 <?= $post['comments'] ?? 0 ?></span>
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
      <form method="POST" action="index.php?action=createPost" enctype="multipart/form-data" class="space-y-3">
        <input type="text" name="title" placeholder="Title" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <textarea name="description" placeholder="Description" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"></textarea>
        <input type="text" name="tags" placeholder="Tags (comma-separated)" class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-md py-2 hover:from-indigo-500 hover:to-purple-500 transition">Post</button>
      </form>
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
  </script>
<script>
let currentPostId = null;
document.addEventListener('click', (e) => {
  const toggle = e.target.closest('.comment-btn');
  if (!toggle) return;
  const postId = toggle.dataset.id;
  currentPostId = postId;
  const commentsModal = document.getElementById('commentsModal');
  const commentsList = document.getElementById('commentsList');
  if (commentsModal && commentsList) {
    commentsModal.classList.remove('hidden');
    loadComments(postId, 'modal'); // function defined in Comments.php
  }

  // Close comments modal when clicking the close button
  const closeComments = document.getElementById('closeComments');
  if (closeComments) {
    closeComments.onclick = () => commentsModal.classList.add('hidden');
  }
});
</script>
<script>
async function loadComments(postId) {
  const list = document.getElementById('commentsList');
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
          ${c.owned ? `<button class="delete-comment text-red-400 hover:text-red-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">✕</button>` : ''}
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
  const input = document.getElementById('newCommentInput');
  const text = (input?.value || '').trim();
  const activePostId = currentPostId;
  if (!text || !activePostId) return;
  try {
    const res = await fetch('index.php?action=addComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${encodeURIComponent(activePostId)}&text=${encodeURIComponent(text)}`
    });
    const data = await res.json();
    if (data.success) {
      input.value = '';
      loadComments(activePostId);
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
</script>
</body>
</html>