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

            <div id="comments-<?= $post['post_id'] ?>" class="hidden mt-4">
              <?php 
                $context = 'inline';
                include __DIR__ . '/Comments.php';
              ?>
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
      loadComments(postId); // function defined in Comments.php
      section.dataset.loaded = "true";
    }
  }
});
</script>

</body>
</html>