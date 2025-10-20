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

      <div class="flex flex-col h-[70vh]">
        <div id="commentsList" class="flex-1 overflow-y-auto text-gray-600 text-sm p-1">
          <p class="text-center text-gray-400 italic">Loading...</p>
        </div>

        <div class="bg-white border-t border-indigo-100 p-2">
          <div class="flex items-center gap-2">
            <input id="newCommentInput" type="text" placeholder="Add a comment..."
                   class="flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
            <button id="submitComment"
                    class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition">
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

    const commentsModal = document.getElementById('commentsModal');
    const commentsList = document.getElementById('commentsList');
    const closeComments = document.getElementById('closeComments');
    const newCommentInput = document.getElementById('newCommentInput');
    const submitComment = document.getElementById('submitComment');

    let currentPostId = null;

    // Open comments modal
    document.querySelectorAll('.comment-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        currentPostId = btn.getAttribute('data-id');
        commentsModal.classList.remove('hidden');
        commentsList.innerHTML = `<p class='text-center text-gray-400 italic'>Loading comments...</p>`;
        await loadComments(currentPostId);
      });
    });

    closeComments.onclick = () => commentsModal.classList.add('hidden');

    // Load comments
    async function loadComments(postId) {
      try {
        const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
        const comments = await res.json();

        if (comments.length > 0) {
          commentsList.innerHTML = comments.map(c => `
            <div class="bg-indigo-50 p-3 rounded-md shadow-sm mb-2">
              <p class="text-gray-700 text-sm">${c.text}</p>
              <p class="text-xs text-gray-500 mt-1">@${c.username} • ${c.created_at}</p>
            </div>
          `).join('');
        } else {
          commentsList.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet. Be the first!</p>";
        }
      } catch (err) {
        commentsList.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
      }
    }

    // Submit comment
    submitComment.addEventListener('click', async () => {
      const content = newCommentInput.value.trim();
      if (!content || !currentPostId) return;

      const formData = new FormData();
      formData.append('post_id', currentPostId);
      formData.append('content', content);

      const res = await fetch('index.php?action=addComment', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        newCommentInput.value = '';
        await loadComments(currentPostId);
      } else {
        alert(data.message || 'Error posting comment.');
      }
    });

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
</body>
</html>