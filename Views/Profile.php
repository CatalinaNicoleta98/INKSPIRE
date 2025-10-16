


<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    .profile-container {
      margin-left: 260px;
      margin-right: 260px;
      padding-top: 80px;
    }
    .profile-header {
      background: white;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .profile-header img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
    }
    .profile-header h2 {
      margin: 10px 0 5px 0;
    }
    .profile-header p {
      color: #555;
      font-size: 14px;
    }
    .stats {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-top: 10px;
    }
    .stats div {
      text-align: center;
    }
    .profile-posts {
      margin-top: 30px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 15px;
    }
    .profile-posts .post {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      overflow: hidden;
      text-align: center;
    }
    .profile-posts img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      cursor: pointer;
    }
    .profile-posts h4 {
      margin: 10px 0;
    }
  </style>
</head>
<body>

<div class="profile-container">
  <div class="profile-header">
    <img src="<?= htmlspecialchars($profile['profile_picture'] ?? 'uploads/default.png') ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($profile['username']) ?></h2>
    <p><?= htmlspecialchars($profile['bio'] ?? 'No bio yet.') ?></p>

    <div class="stats">
      <div><strong><?= htmlspecialchars($profile['followers'] ?? 0) ?></strong><br>Followers</div>
      <div><strong><?= htmlspecialchars($profile['following'] ?? 0) ?></strong><br>Following</div>
      <div><strong><?= count($posts ?? []) ?></strong><br>Posts</div>
    </div>
  </div>

  <div class="profile-posts">
    <?php if (!empty($posts)): ?>
      <?php foreach ($posts as $post): ?>
        <div class="post" data-id="<?= $post['post_id'] ?>">
          <?php if (!empty($post['image_url'])): ?>
            <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post Image">
          <?php endif; ?>
          <h4><?= htmlspecialchars($post['title'] ?? 'Untitled') ?></h4>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">No posts yet.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>