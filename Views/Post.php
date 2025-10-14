<?php
require_once __DIR__ . '/../helpers/Session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inkspire Feed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f7f7f7;
        }
        .feed-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        .post {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .post:hover {
            transform: scale(1.02);
        }
        .post img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post .content {
            padding: 10px;
        }
        .post .content h3 {
            margin: 0;
        }
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #007BFF;
            color: white;
            font-size: 32px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>

<div class="feed-container">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="">
                <div class="content">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <p>By <?= htmlspecialchars($post['username']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">No posts yet. Be the first to create one!</p>
    <?php endif; ?>
</div>

<!-- Floating Create Post Button -->
<button class="floating-btn" id="openModal">+</button>

<!-- Create Post Modal -->
<div id="postModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h3>Create New Post</h3>
        <form method="POST" action="index.php?action=createPost" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required><br><br>
            <textarea name="description" placeholder="Description" required></textarea><br><br>
            <input type="file" name="image" accept="image/*" required><br><br>
            <input type="text" name="tags" placeholder="Tags (comma-separated)"><br><br>
            <button type="submit">Post</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("postModal");
    const btn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");

    btn.onclick = () => modal.style.display = "block";
    closeBtn.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };
</script>

</body>
</html>