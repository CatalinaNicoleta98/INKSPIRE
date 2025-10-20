<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[70px]">
    <div class="w-full max-w-[700px] mx-auto space-y-8">

      <!-- Profile Header -->
      <div class="bg-white rounded-xl shadow-md p-6 text-center">
        <img src="<?= htmlspecialchars($profile['profile_picture'] ?? 'uploads/default.png') ?>" alt="Profile Picture"
             class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-indigo-200 shadow-sm">
        <h2 class="text-2xl font-semibold text-gray-800 mt-3"><?= htmlspecialchars($profile['username']) ?></h2>
        <p class="text-gray-600 text-sm italic mt-1"><?= htmlspecialchars($profile['bio'] ?? 'No bio yet.') ?></p>

        <div class="flex justify-center gap-10 mt-5 text-gray-700">
          <div><strong class="text-indigo-600"><?= htmlspecialchars($profile['followers'] ?? 0) ?></strong><br><span class="text-sm">Followers</span></div>
          <div><strong class="text-indigo-600"><?= htmlspecialchars($profile['following'] ?? 0) ?></strong><br><span class="text-sm">Following</span></div>
          <div><strong class="text-indigo-600"><?= count($posts ?? []) ?></strong><br><span class="text-sm">Posts</span></div>
        </div>
      </div>

      <!-- Posts Feed -->
      <div class="space-y-6">
        <?php if (!empty($posts)): ?>
          <?php foreach ($posts as $post): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition relative">
              <?php if (!empty($post['image_url'])): ?>
                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post Image" class="w-full rounded-lg mb-4 object-cover shadow-sm">
              <?php endif; ?>
              <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($post['title'] ?? 'Untitled') ?></h3>
              <?php if (!empty($post['description'])): ?>
                <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($post['description']) ?></p>
              <?php endif; ?>

              <?php if (!empty($post['tags'])): ?>
                <div class="mt-2 text-sm text-indigo-500">#<?= str_replace(',', ' #', htmlspecialchars($post['tags'])) ?></div>
              <?php endif; ?>

              <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
                <span class="cursor-pointer transition hover:scale-110">❤️ <?= htmlspecialchars($post['likes'] ?? 0) ?></span>
                <span class="cursor-pointer transition hover:scale-110 comment-toggle" data-id="<?= $post['post_id'] ?>">💬 <?= htmlspecialchars($post['comments'] ?? 0) ?></span>
              </div>
              <?php 
                $context = 'inline';
                include __DIR__ . '/Comments.php';
              ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center text-gray-500 italic">No posts yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

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