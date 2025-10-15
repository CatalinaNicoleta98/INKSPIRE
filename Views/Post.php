<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inkspire Feed</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f4f4f4; }
        .feed { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; padding: 20px; }
        .post { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; }
        .post img { width: 100%; height: 200px; object-fit: cover; }
        .content { padding: 10px; }
        .floating-btn { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #007BFF; color: white; border: none; border-radius: 50%; font-size: 32px; cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background: white; padding: 20px; border-radius: 10px; max-width: 500px; margin: 10% auto; }
        .close { float: right; cursor: pointer; font-size: 24px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>

<div class="feed">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post" data-id="<?= $post['post_id'] ?>">
                <?php if (!empty($post['image_url'])): ?>
                    <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image">
                <?php endif; ?>
                <div class="content">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <p><?= htmlspecialchars($post['description']) ?></p>
                    <small>By <?= htmlspecialchars($post['username']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">No posts yet. Be the first to create one!</p>
    <?php endif; ?>
</div>

<!-- Floating Button -->
<button class="floating-btn" id="openModal">+</button>

<!-- Modal -->
<div id="postModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h3>Create New Post</h3>
        <form method="POST" action="index.php?action=createPost" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required><br><br>
            <textarea name="description" placeholder="Description" required></textarea><br><br>
            <input type="text" name="tags" placeholder="Tags (comma-separated)"><br><br>
            <input type="file" name="image" accept="image/*"><br><br>
            <button type="submit">Post</button>
        </form>
    </div>
</div>

<!-- Post Detail Modal -->
<div id="viewModal" class="modal">
  <div class="modal-content" style="max-height:80vh;overflow-y:auto;">
    <span class="close" id="closeView">&times;</span>
    <div id="viewContent">
      <img id="viewImage" src="" style="width:100%; border-radius:8px;" alt="">
      <h3 id="viewTitle"></h3>
      <p id="viewDescription"></p>
      <small id="viewAuthor"></small>
      <p id="viewTags" style="color:#666;"></p>
      <div id="viewExtras" style="margin-top:10px;">
        <span>❤️ Likes: <span id="likeCount">0</span></span> |
        <span>💬 Comments: <span id="commentCount">0</span></span>
      </div>
    </div>
  </div>
</div>

<script>
const modal = document.getElementById("postModal");
const open = document.getElementById("openModal");
const close = document.getElementById("closeModal");

const viewModal = document.getElementById("viewModal");
const closeView = document.getElementById("closeView");

// Open/close create post modal
open.onclick = () => modal.style.display = "block";
close.onclick = () => modal.style.display = "none";

// Handle closing of modals when clicking outside modal content
window.onclick = e => {
    if (e.target === modal) modal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
};

// Open post detail modal
document.querySelectorAll('.post').forEach(post => {
    post.addEventListener('click', () => {
        const postId = post.getAttribute('data-id');
        fetch(`index.php?action=viewPost&id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById("viewImage").src = data.image_url || '';
                    document.getElementById("viewTitle").innerText = data.title || '';
                    document.getElementById("viewDescription").innerText = data.description || '';
                    document.getElementById("viewAuthor").innerText = data.username ? `By ${data.username} — ${data.created_at || ''}` : '';
                    document.getElementById("viewTags").innerText = data.tags ? `Tags: ${data.tags}` : '';
                    document.getElementById("likeCount").innerText = data.likes || 0;
                    document.getElementById("commentCount").innerText = data.comments || 0;
                    viewModal.style.display = "block";
                }
            });
    });
});

closeView.onclick = () => viewModal.style.display = "none";
</script>

</body>
</html>