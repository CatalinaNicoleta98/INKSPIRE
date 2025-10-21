<?php
// Unified Comments View (for Inline + Modal Contexts)
// Use $context = 'inline' (Home/Profile) or $context = 'modal' (Explore)
?>

<?php if (isset($context) && $context === 'modal'): ?>
  <!-- Modal Comments Layout -->
  <div class="flex flex-col h-[70vh]" data-context="modal">
    <div id="commentsList" class="comments-list flex-1 overflow-y-auto text-gray-600 text-sm p-1">
      <p class="text-center text-gray-400 italic">Loading...</p>
    </div>

    <div class="bg-white border-t border-indigo-100 p-2 sticky bottom-0">
      <div class="flex items-center gap-2">
        <input id="newCommentInput" type="text" placeholder="Add a comment..."
              class="comment-input flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button id="submitComment"
                class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
          </svg>
        </button>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- Inline Comments Layout (Home/Profile) -->
  <div class="comments-section mt-4 hidden" id="comments-<?= $post['post_id'] ?>" data-post-id="<?= $post['post_id'] ?>" data-context="inline">
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
<?php endif; ?>

<script>
// ============================
// COMMENT COMPONENT LOGIC
// Handles: Load, Post, Delete
// Context-aware: inline or modal
// ============================

async function loadComments(postId, context = 'inline') {
  const list = document.querySelector(`#commentsList-${postId}`) || document.querySelector(`#commentsList`);
  if (!list) return;
  list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";

  try {
    const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
    const comments = await res.json();

    if (comments.length > 0) {
      list.innerHTML = comments.map(c => `
        <div class="comment-item bg-indigo-50 p-2 rounded-md shadow-sm mb-1 flex justify-between items-start" data-comment-id="${c.comment_id}">
          <div>
            <p class="text-gray-700 text-sm">${c.text}</p>
            <p class="text-xs text-gray-500">@${c.username} • ${c.created_at}</p>
          </div>
          ${c.owned ? `<button class="delete-comment text-red-400 hover:text-red-600 transition" data-comment-id="${c.comment_id}">✕</button>` : ''}
        </div>
      `).join('');
    } else {
      list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
    }
  } catch (error) {
    list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
  }
}

// POST COMMENT
document.addEventListener('click', async (e) => {
  const submitBtn = e.target.closest('.comment-submit');
  if (!submitBtn) return;

  const postId = submitBtn.dataset.id;
  const input = document.querySelector(`#newCommentInput-${postId}`) || document.querySelector(`#newCommentInput`);
  const text = input.value.trim();
  if (!text) return;

  try {
    const res = await fetch('index.php?action=addComment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${postId}&text=${encodeURIComponent(text)}`
    });
    const data = await res.json();
    if (data.success) {
      input.value = '';
      const context = submitBtn.closest('[data-context]')?.dataset.context || 'inline';
      loadComments(postId, context);
    }
  } catch (error) {
    console.error('Failed to post comment');
  }
});

// DELETE COMMENT (delegated safely)
if (!window.commentDeleteBound) {
  document.addEventListener('click', async (e) => {
    const deleteBtn = e.target.closest('.delete-comment');
    if (!deleteBtn) return;

    const commentId = deleteBtn.dataset.commentId;
    const commentItem = deleteBtn.closest('.comment-item');
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
      const res = await fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `comment_id=${commentId}`
      });
      const data = await res.json();
      if (data.success) {
        const postId = commentItem.closest('[data-post-id]')?.dataset.postId;
        commentItem.remove();
        if (postId) loadComments(postId); // Refresh after delete
      } else {
        alert(data.message || 'Error deleting comment.');
      }
    } catch (error) {
      alert('Request failed.');
    }
  });

  // Flag to prevent re-binding
  window.commentDeleteBound = true;
}
</script>