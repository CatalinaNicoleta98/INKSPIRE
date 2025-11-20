<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Posts</title>
  
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <h2 class="text-center text-2xl font-semibold text-indigo-600 pt-[80px] mb-6">
    Welcome, <?= htmlspecialchars($user['username']) ?>!
  </h2>

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4">
    <div class="feed w-full max-w-[1000px] mx-auto space-y-6">

      <?php if (!empty($posts)): ?>
        <div class="columns-3 md:columns-2 sm:columns-1 gap-6 [column-fill:_balance]">
          <?php foreach ($posts as $post): ?>
            <div class="post inline-block w-full mb-6 bg-white rounded-xl shadow-md overflow-hidden break-inside-avoid transition transform hover:-translate-y-1 hover:shadow-lg relative">
              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image" class="w-full object-cover cursor-pointer transition-transform duration-300 hover:scale-[1.03]">
              <?php endif; ?>

              <div class="absolute bottom-3 right-3 bg-black/50 text-white rounded-full px-3 py-1 text-sm flex items-center gap-3">
                <span class="like-btn cursor-pointer transition" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:#f87171;' : '' ?>">❤️ <?= $post['likes'] ?></span>
                <span class="comment-btn cursor-pointer" data-id="<?= $post['post_id'] ?>">💬 <?= $post['comments'] ?? 0 ?></span>
              </div>

              <?php if ($post['user_id'] === $user['user_id']): ?>
                <div class="absolute top-3 right-3 z-20">
                  <div class="relative">
                    <button class="post-options text-gray-500 hover:text-gray-700 transition" data-post-id="<?= $post['post_id'] ?>">⋮</button>
                    <div class="options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-30">
                      <button class="edit-post block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-post-id="<?= $post['post_id'] ?>">Edit</button>
                      <button class="delete-post block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-post-id="<?= $post['post_id'] ?>">Delete</button>
                      <button class="toggle-privacy block w-full text-left px-3 py-1 text-sm text-gray-700 hover:bg-indigo-50" data-post-id="<?= $post['post_id'] ?>" data-public="<?= $post['is_public'] ?? 1 ?>">
                        <?= (!empty($post['is_public']) && $post['is_public']) ? 'Make Private' : 'Make Public' ?>
                      </button>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-500 italic mt-10">No posts yet. Be the first to create one!</p>
      <?php endif; ?>

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

      <div id="commentsList" class="text-gray-600 text-sm mb-4">
        <p class="text-center text-gray-400 italic">Loading...</p>
      </div>

      <div class="border-t border-indigo-100 pt-3 mt-3">
        <input id="newCommentInput" type="text" placeholder="Add a comment..." 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none mb-2">
        <button id="submitComment" 
                class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-md py-2 hover:from-indigo-500 hover:to-purple-500 transition text-sm">
          Post Comment
        </button>
      </div>
    </div>
  </div>

  <script>
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    document.querySelectorAll('.post img').forEach(img => {
      img.addEventListener('click', () => {
        lightboxImg.src = img.src;
        lightbox.classList.remove('hidden');
      });
    });
    lightbox.addEventListener('click', () => lightbox.classList.add('hidden'));

    // Comments system
    let currentPostId = null;
    const commentsList = document.getElementById('commentsList');
    const newCommentInput = document.getElementById('newCommentInput');
    const submitComment = document.getElementById('submitComment');
    const commentsModal = document.getElementById('commentsModal');
    const closeComments = document.getElementById('closeComments');

    // Open comments modal and load comments
    document.querySelectorAll('.comment-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const postId = btn.dataset.id;
        currentPostId = postId; // persist globally for send button
        commentsModal.classList.remove('hidden');
        commentsList.innerHTML = `<p class='text-center text-gray-400 italic'>Loading comments...</p>`;
        await loadComments(postId);
      });
    });

    closeComments.onclick = () => commentsModal.classList.add('hidden');

    // Load comments from backend
    async function loadComments(postId) {
      try {
        const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
        const comments = await res.json();

        if (comments.length > 0) {
          commentsList.innerHTML = comments.map(c => `
            <div class="bg-indigo-50 p-3 rounded-md shadow-sm mb-2">
              <p class="text-gray-700 text-sm">${c.content}</p>
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

    // Add new comment
    submitComment.addEventListener('click', async () => {
      const content = newCommentInput.value.trim();
      console.log('Posting comment for post:', currentPostId, 'Content:', content);
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

    <!-- Post management handled globally by Home.php script (edit, delete, privacy toggle) -->
  </script>
</body>
</html>