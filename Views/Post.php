<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
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
                    <?php if (!empty($post['tags'])): ?>
                        <p style="color:#666;font-size:14px;">Tags: <?= htmlspecialchars($post['tags']) ?></p>
                    <?php endif; ?>
                    <div class="like-section" style="margin-top:8px;">
                        <button 
                            class="like-btn" 
                            data-id="<?= $post['post_id'] ?>" 
                            style="background:none;border:none;cursor:pointer;font-size:18px;<?= !empty($post['liked']) ? 'color:red;' : 'color:black;' ?>">
                            ❤️
                        </button>
                        <span class="like-count" id="likes-<?= $post['post_id'] ?>"><?= htmlspecialchars($post['likes']) ?></span>
                    </div>
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
        <button id="modalLikeBtn" style="background:none;border:none;cursor:pointer;font-size:20px;">❤️</button>
        <span>Likes: <span id="modalLikeCount">0</span></span> |
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


// Post detail modal with like button sync
let currentModalPostId = null;
const modalLikeBtn = document.getElementById("modalLikeBtn");

document.querySelectorAll('.post').forEach(post => {
  post.addEventListener('click', () => {
    const postId = post.getAttribute('data-id');
    currentModalPostId = postId;
    fetch(`index.php?action=viewPost&id=${postId}`)
      .then(response => response.json())
      .then(data => {
        if (data) {
          document.getElementById("viewImage").src = data.image_url || '';
          document.getElementById("viewTitle").innerText = data.title || '';
          document.getElementById("viewDescription").innerText = data.description || '';
          document.getElementById("viewAuthor").innerText = data.username ? `By ${data.username} — ${data.created_at || ''}` : '';
          document.getElementById("viewTags").innerText = data.tags ? `Tags: ${data.tags}` : '';
          document.getElementById("modalLikeCount").innerText = data.likes || 0;
          modalLikeBtn.style.color = data.liked ? 'red' : 'black';
          document.getElementById("commentCount").innerText = data.comments || 0;
          viewModal.style.display = "block";
        }
      });
  });
});

closeView.onclick = () => viewModal.style.display = "none";

// Unified Like Functionality (Feed + Modal)
async function toggleLike(postId, btnElement, updateModal = false) {
  try {
    const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`, { cache: 'no-store' });
    const data = await response.json();

    if (data.success) {
      // Update feed button + count
      const feedCount = document.getElementById(`likes-${postId}`);
      if (feedCount) feedCount.textContent = data.likes;

      if (btnElement) btnElement.style.color = data.liked ? 'red' : 'black';

      // Update modal if open for same post
      if (updateModal && currentModalPostId == postId) {
        const modalLikeCount = document.getElementById('modalLikeCount');
        const modalLikeBtn = document.getElementById('modalLikeBtn');
        if (modalLikeCount) modalLikeCount.textContent = data.likes;
        if (modalLikeBtn) modalLikeBtn.style.color = data.liked ? 'red' : 'black';
      }
    }
  } catch (error) {
    console.error('Error toggling like:', error);
  }
}

// Modal Like Handling
modalLikeBtn.addEventListener('click', (e) => {
  e.stopPropagation();
  if (currentModalPostId) {
    toggleLike(currentModalPostId, modalLikeBtn, true);
  }
});

// Feed Like Handling
document.querySelectorAll('.like-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const postId = btn.getAttribute('data-id');
    toggleLike(postId, btn, false);
  });
});
</script>
</div> <!-- closes .main-content -->
</body>
</html>