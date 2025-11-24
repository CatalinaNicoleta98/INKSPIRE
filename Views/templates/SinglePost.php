<?php require_once __DIR__ . '/../../helpers/Session.php'; ?>
<?php include __DIR__ . '/../layout/Header.php'; ?>
<?php include __DIR__ . '/../layout/Sidebar.php'; ?>
<?php include __DIR__ . '/../layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Post</title>
</head>

<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

  <div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[70px]">
    <div class="feed w-full max-w-[700px] mx-auto space-y-6">

      <?php if (!empty($posts)): ?>
        <?php $post = $posts[0]; ?>
        
        <div class="post bg-white rounded-xl shadow-md p-6 mb-6 w-full max-w-[700px] hover:shadow-lg transition relative">

          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>">
                <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'uploads/default-avatar.png') ?>"
                     alt="profile" class="w-9 h-9 rounded-full object-cover border border-indigo-200 hover:ring-2 hover:ring-indigo-300 transition">
              </a>
              <div>
                <p class="text-sm font-semibold">
                  <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>" 
                     class="text-gray-800 hover:text-indigo-600 hover:underline transition">
                     <?= htmlspecialchars($post['username']) ?>
                  </a>
                </p>
                <div class="flex items-center gap-1 text-xs text-gray-500">
                  <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                  <span class="text-gray-400">
                    <?= $post['is_public'] ? '🌍' : '👥' ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <h3 class="text-lg font-semibold text-gray-800 break-words">
            <?= htmlspecialchars($post['title']) ?>
          </h3>

          <p class="text-gray-600 text-sm mt-1 break-words">
            <?= htmlspecialchars($post['description']) ?>
          </p>

          <?php if (!empty($post['image_url'])): ?>
            <img src="<?= htmlspecialchars($post['image_url']) ?>"
                 class="w-full max-h-[500px] object-cover object-center rounded-lg mt-4 shadow-sm">
          <?php endif; ?>

          <?php if (!empty($post['tags'])): ?>
            <div class="mt-2 text-sm text-indigo-500">
              #<?= htmlspecialchars(str_replace(',', ' #', $post['tags'])) ?>
            </div>
          <?php endif; ?>

          <div class="flex items-center gap-6 mt-3 text-lg text-gray-600">
            <?php $liked = !empty($post['liked']); ?>
            <span class="like-btn cursor-pointer transition hover:scale-110" 
                  data-id="<?= $post['post_id'] ?>" 
                  style="<?= $liked ? 'color:#ef4444;' : '#9ca3af;' ?>">
              <?= $liked ? '❤️' : '🤍' ?> <?= $post['likes'] ?>
            </span>

            <span class="comment-toggle cursor-pointer transition hover:scale-110" 
                  data-id="<?= $post['post_id'] ?>">
              💬 <?= count($post['comments'] ?? []) ?>
            </span>
          </div>

          <div class="comments-section mt-4 hidden" id="comments-<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
            <div id="commentsList-<?= $post['post_id'] ?>" 
                 class="comments-list text-gray-600 text-sm space-y-2 max-h-60 overflow-y-auto p-2 bg-gray-50 rounded-md border border-indigo-100"
                 data-post-id="<?= $post['post_id'] ?>">
              <p class="text-center text-gray-400 italic">Loading comments...</p>
            </div>

            <div class="add-comment flex items-center gap-2 mt-3 bg-white border-t border-indigo-100 pt-2 pb-2 px-2 rounded-b-md sticky bottom-0 z-10" data-post-id="<?= $post['post_id'] ?>">
              <input type="text" placeholder="Add a comment..."
                    id="newCommentInput-<?= $post['post_id'] ?>"
                    class="comment-input flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none"
                    data-id="<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
              <button id="submitComment-<?= $post['post_id'] ?>"
                      class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition"
                      data-id="<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
                </svg>
              </button>
            </div>
          </div>

        </div>

      <?php endif; ?>
    </div>
  </div>

<script>
// Auto-open comments on single post page
document.addEventListener("DOMContentLoaded", () => {
  const postId = <?= intval($post['post_id']) ?>;
  const section = document.getElementById(`comments-${postId}`);
  const list = document.getElementById(`commentsList-${postId}`);

  if (section && list) {
    section.classList.remove('hidden');

    // Load comments immediately
    fetch(`index.php?action=getCommentsByPost&post_id=${postId}`)
      .then(res => res.json())
      .then(comments => {
        if (Array.isArray(comments) && comments.length > 0) {
          list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
        } else {
          list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
        }
      })
      .catch(() => {
        list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
      });
  }
});
</script>

<script>
// auto-scroll for notifications
<?php if (!empty($_GET['comment_id'])): ?>
document.addEventListener("DOMContentLoaded", () => {
    const highlightId = "<?= intval($_GET['comment_id']) ?>";

    const waitForComments = setInterval(() => {
        const target = document.querySelector(`[data-comment-id='${highlightId}']`);
        if (!target) return;

        clearInterval(waitForComments);

        let current = target.closest('.replies');
        while (current) {
            current.classList.remove('hidden');
            const parentCommentItem = current.closest('.comment-item');
            if (parentCommentItem) {
                const toggleBtn = parentCommentItem.querySelector('.toggle-replies');
                if (toggleBtn) toggleBtn.textContent = "Hide replies";
            }
            current = current.parentElement.closest('.replies');
        }

        target.scrollIntoView({ behavior: "smooth", block: "center" });
        target.classList.add("ring-2", "ring-indigo-400");
        setTimeout(() => target.classList.remove("ring-2", "ring-indigo-400"), 2000);
    }, 200);
});
<?php endif; ?>
</script>

<script>
// Like toggle (same as Home/Profile)
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const postId = btn.getAttribute('data-id');
      const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`);
      const data = await response.json();
      if (data.success) {
        const icon = data.liked ? '❤️' : '🤍';
        btn.innerHTML = `${icon} ${data.likes}`;
        btn.style.color = data.liked ? '#ef4444' : '#9ca3af';
      }
    });
  });
});
</script>

<script>
// Safe global comment renderer (handles missing or flat replies)
function renderComment(c, postId, level = 0) {
  const repliesCount = Array.isArray(c.replies) ? c.replies.length : 0;
  const repliesHTML = Array.isArray(c.replies)
    ? c.replies.map(r => renderComment(r, postId, level + 1)).join('')
    : '';

  return `
    <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2" data-comment-id="${c.comment_id}">
      <div class="w-full">
        <p class="text-gray-700 text-sm whitespace-pre-wrap">${c.text}</p>
        <p class="text-xs text-gray-500">
          <a href="index.php?action=profile&user_id=${c.user_id}" 
             class="text-indigo-600 hover:underline">@${c.username}</a> • ${c.created_at}
        </p>
        <div class="flex gap-2 mt-1">
          <button class="reply-btn text-xs text-indigo-500" data-comment-id="${c.comment_id}" data-username="${c.username}" data-post-id="${postId}">↩️ Reply</button>
          ${repliesCount > 0
            ? `<button class="toggle-replies text-xs text-indigo-400" data-comment-id="${c.comment_id}" data-post-id="${postId}">Show replies (${repliesCount})</button>`
            : ''}
        </div>
        <div class="replies hidden mt-2 ml-6">${repliesHTML}</div>
      </div>
      ${c.owned
        ? `
          <div class="comment-tools absolute top-2 right-2">
            <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
            <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
              <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
              <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
            </div>
          </div>
        `
        : ''}
    </div>
  `;
}

// --- Unified Comments & Replies UX ---
async function loadComments(postId) {
  const list = document.querySelector(`#commentsList-${postId}`);
  if (!list) return;
  list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";

  try {
    const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
    const comments = await res.json();

    if (Array.isArray(comments) && comments.length > 0) {
      list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
    } else {
      list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
    }
  } catch (err) {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

// Unified reply system
let replyTarget = null;

document.addEventListener('click', (e) => {
  const replyBtn = e.target.closest('.reply-btn');
  if (!replyBtn) return;
  replyTarget = {
    commentId: replyBtn.dataset.commentId,
    username: replyBtn.dataset.username,
    postId: replyBtn.dataset.postId
  };
  const commentBox = document.querySelector(`#newCommentInput-${replyTarget.postId}`);
  const indicator = document.createElement('div');
  indicator.className = "reply-indicator text-xs text-indigo-600 italic mb-1";
  indicator.textContent = `Replying to @${replyTarget.username} `;
  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = "✖";
  cancelBtn.className = "ml-1 text-red-500 hover:text-red-700";
  cancelBtn.addEventListener('click', () => {
    replyTarget = null;
    indicator.remove();
    commentBox.placeholder = "Add a comment...";
  });
  indicator.appendChild(cancelBtn);

  // Remove existing indicators
  const existing = document.querySelector('.reply-indicator');
  if (existing) existing.remove();

  commentBox.parentNode.insertBefore(indicator, commentBox);
  commentBox.placeholder = `Replying to @${replyTarget.username}...`;
  commentBox.focus();
});

document.addEventListener('click', (e) => {
  // Toggle replies
  const toggleBtn = e.target.closest('.toggle-replies');
  if (!toggleBtn) return;
  const commentId = toggleBtn.dataset.commentId;
  const postId = toggleBtn.dataset.postId;
  const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
  if (!commentItem) return;
  const replies = commentItem.querySelector('.replies');
  if (!replies) return;

  if (replies.classList.contains('hidden')) {
    replies.classList.remove('hidden');
    toggleBtn.textContent = `Hide replies (${replies.children.length})`;
  } else {
    replies.classList.add('hidden');
    toggleBtn.textContent = `Show replies (${replies.children.length})`;
  }
});

// Load comments on page load
document.addEventListener('DOMContentLoaded', () => {
  const postId = <?= intval($post['post_id']) ?>;
  loadComments(postId);
});
</script>

</body>
</html>