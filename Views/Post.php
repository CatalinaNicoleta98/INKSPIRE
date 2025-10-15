<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inkspire Feed</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }

        .feed {
            column-count: 3;
            column-gap: 15px;
            padding: 90px 20px 20px 260px;
        }

        .post {
            display: inline-block;
            margin-bottom: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            break-inside: avoid;
            position: relative;
        }

        .post img {
            width: 100%;
            height: auto;
            display: block;
            cursor: pointer;
        }

        .overlay {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0,0,0,0.5);
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .overlay span { cursor: pointer; }

        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 32px;
            cursor: pointer;
        }

        .lightbox {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .lightbox img {
            max-width: 90%;
            max-height: 90%;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            border-radius: 10px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        }
        .close { float: right; cursor: pointer; font-size: 24px; }
    </style>
</head>
<body>

<h2 style="text-align:center; padding-top:70px;">Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>

<div class="feed">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post" data-id="<?= $post['post_id'] ?>">
                <?php if (!empty($post['image_url'])): ?>
                    <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image">
                <?php endif; ?>
                <div class="overlay">
                    <span class="like-btn" data-id="<?= $post['post_id'] ?>" style="<?= !empty($post['liked']) ? 'color:red;' : '' ?>">❤️ <?= $post['likes'] ?></span>
                    <span class="comment-btn" data-id="<?= $post['post_id'] ?>">💬 <?= $post['comments'] ?? 0 ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">No posts yet. Be the first to create one!</p>
    <?php endif; ?>
</div>

<button class="floating-btn" id="openModal">+</button>

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

<div id="lightbox" class="lightbox">
  <img id="lightboxImg" src="" alt="Full image">
</div>

<div id="commentsModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeComments">&times;</span>
    <h3>Comments</h3>
    <div id="commentsList"><p style="text-align:center;color:#888;">Loading...</p></div>
  </div>
</div>

<script>
const postModal = document.getElementById('postModal');
const openModal = document.getElementById('openModal');
const closeModal = document.getElementById('closeModal');
openModal.onclick = () => postModal.style.display = 'flex';
closeModal.onclick = () => postModal.style.display = 'none';
window.onclick = e => { if (e.target === postModal) postModal.style.display = 'none'; };

const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
document.querySelectorAll('.post img').forEach(img => {
  img.addEventListener('click', () => {
    lightboxImg.src = img.src;
    lightbox.style.display = 'flex';
  });
});
lightbox.addEventListener('click', () => lightbox.style.display = 'none');

const commentsModal = document.getElementById('commentsModal');
const closeComments = document.getElementById('closeComments');
closeComments.onclick = () => commentsModal.style.display = 'none';
document.querySelectorAll('.comment-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const postId = btn.getAttribute('data-id');
    commentsModal.style.display = 'flex';
    document.getElementById('commentsList').innerHTML = `<p style=\"text-align:center;color:#888;\">Loading comments for post #${postId}...</p>`;
  });
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
        btn.style.color = data.liked ? 'red' : 'white';
      }
    } catch (err) {
      console.error('Like error', err);
    }
  });
});
</script>

</body>
</html>