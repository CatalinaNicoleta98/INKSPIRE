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

            <div class="comments-section mt-4 border-t border-indigo-100 pt-3 hidden" id="comments-<?= $post['post_id'] ?>">
              <div class="comments-list space-y-2 text-sm text-gray-700"></div>
              <div class="add-comment flex items-center gap-2 mt-3">
                <input type="text" placeholder="Add a comment..." class="comment-input flex-1 px-3 py-2 border border-indigo-200 rounded-md focus:ring-2 focus:ring-indigo-300 focus:outline-none" data-id="<?= $post['post_id'] ?>">
                <button class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white px-4 py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition" data-id="<?= $post['post_id'] ?>">Send</button>
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

  // Load comments dynamically
  document.querySelectorAll('.comment-toggle').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-id');
      const section = document.getElementById(`comments-${id}`);
      const list = section.querySelector('.comments-list');
      section.classList.toggle('hidden');

      if (!section.classList.contains('hidden')) {
        list.innerHTML = "<p class='text-gray-400 italic'>Loading comments...</p>";
        const res = await fetch(`index.php?action=getCommentsByPost&post_id=${id}`);
        const comments = await res.json();

        if (comments.length > 0) {
          list.innerHTML = comments.map(c => `
            <div class="bg-indigo-50 p-2 rounded-md shadow-sm">
              <p class="text-gray-700 text-sm">${c.text}</p>
              <p class="text-xs text-gray-500 mt-1">@${c.username} • ${c.created_at}</p>
            </div>
          `).join('');
        } else {
          list.innerHTML = "<p class='text-gray-400 italic'>No comments yet.</p>";
        }
      }
    });
  });

  // Add comment
  document.querySelectorAll('.comment-submit').forEach(btn => {
    btn.addEventListener('click', async () => {
      const postId = btn.getAttribute('data-id');
      const input = document.querySelector(`.comment-input[data-id='${postId}']`);
      const content = input.value.trim();
      if (!content) return;

      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('content', content);

      const res = await fetch('index.php?action=addComment', {
        method: 'POST',
        body: formData
      });

      const data = await res.json();
      if (data.success) {
        input.value = '';
        const toggle = document.querySelector(`.comment-toggle[data-id='${postId}']`);
        toggle.click(); // close
        toggle.click(); // reopen to refresh comments
      } else {
        alert(data.message || 'Error adding comment.');
      }
    });
  });
  </script>

</body>
</html>