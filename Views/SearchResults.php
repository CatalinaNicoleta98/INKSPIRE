<?php
// Variables expected from controller:
// $query, $activeType, $users, $posts
?>

<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    
</head>

<body class="bg-gradient-to-b from-indigo-50 to-white min-h-screen">

<div class="flex justify-center items-start w-full lg:pr-[250px] md:px-[200px] sm:px-4 box-border pt-[70px]">

    <!-- Main Feed -->
    <main class="feed w-full max-w-[900px] mx-auto px-2 space-y-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Search results for "<?= htmlspecialchars($query) ?>"
        </h1>

        <!-- FILTER BUTTONS -->
        <div class="flex gap-3 mb-4">

            <a href="index.php?action=search&type=profiles&q=<?= urlencode($query) ?>"
               class="px-4 py-2 rounded-md text-sm font-semibold 
               <?= $activeType === 'profiles' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                Profiles
            </a>

            <a href="index.php?action=search&type=tags&q=<?= urlencode($query) ?>"
               class="px-4 py-2 rounded-md text-sm font-semibold 
               <?= $activeType === 'tags' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                Tags
            </a>
        </div>

        <!-- RESULTS SECTION -->
        <?php if ($activeType === 'profiles'): ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Profiles</h2>

            <?php if (empty($users)): ?>
                <p class="text-gray-500">No profiles match your search.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($users as $u): ?>
                        <a href="index.php?action=profile&user_id=<?= $u['user_id'] ?>"
                           class="flex items-center bg-white p-3 rounded-lg shadow hover:shadow-md transition">
                            <img src="<?= htmlspecialchars($u['profile_picture'] ?? 'uploads/default.png') ?>"
                                 class="w-12 h-12 rounded-full object-cover mr-4">
                            <div>
                                <p class="font-semibold text-gray-800">@<?= htmlspecialchars($u['username']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($activeType === 'tags'): ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Posts</h2>

            <?php if (empty($posts)): ?>
                <p class="text-gray-500">No posts match your search.</p>
            <?php else: ?>
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6">
                    <?php foreach ($posts as $post): ?>
                        <?php include __DIR__ . '/templates/PostCard.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<!-- Comments Modal -->
<div id="commentsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg relative">
      <span id="closeComments" class="absolute top-3 right-4 text-gray-500 cursor-pointer text-2xl">&times;</span>
      <h3 class="text-xl font-semibold text-indigo-500 mb-4 text-center">Comments</h3>

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
    </div>
</div>

<script>
// Enable submitting comments with ENTER inside the search results comments modal
document.addEventListener('keydown', function (e) {
    const input = e.target.closest('#commentsModal .comment-input');
    if (!input) return;

    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const submit = document.getElementById('submitComment');
        if (submit) submit.click();
    }
});
</script>
</body>
<!-- FULL JS FROM EXPLORE (LIKES, LIGHTBOX, COMMENTS) -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ------------------ IMAGE LIGHTBOX ------------------ */
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');

    document.querySelectorAll('.post img').forEach(img => {
        if (img.closest('.flex.items-center.gap-3')) return;
        img.addEventListener('click', () => {
            if (!lightboxImg || !lightbox) return;
            lightboxImg.src = img.src;
            lightbox.classList.remove('hidden');
        });
    });

    if (lightbox) {
        lightbox.addEventListener('click', () => lightbox.classList.add('hidden'));
    }

    /* ------------------ LIKE BUTTON ------------------ */
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const postId = btn.getAttribute('data-id');

            try {
                const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`, {
                    cache: 'no-store'
                });
                const data = await response.json();

                if (data.success) {
                    btn.innerHTML = `❤️ ${data.likes}`;
                    btn.style.color = data.liked ? '#f87171' : 'white';
                }
            } catch (err) {
                console.error('Like error', err);
            }
        });
    });

/* ------------------ FULL EXPLORE COMMENTS SYSTEM ------------------ */

let currentPostId = null;
let replyTarget = null;

function renderComment(c, postId, level = 0) {
  const repliesCount = c.replies ? c.replies.length : 0;
  return `
    <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2" data-comment-id="${c.comment_id}">
      <div class="w-full">
        <p class="text-gray-700 text-sm">${c.text}</p>
        <p class="text-xs text-gray-500">
          <a href="index.php?action=profile&user_id=${c.user_id}"
             class="text-indigo-600 hover:underline">@${c.username}</a> • ${c.created_at}
        </p>
        <div class="flex gap-2 mt-1">
          <button class="reply-btn text-xs text-indigo-500" data-comment-id="${c.comment_id}" data-username="${c.username}" data-post-id="${postId}">↩️ Reply</button>
          ${repliesCount > 0 ? `<button class="toggle-replies text-xs text-indigo-400" data-comment-id="${c.comment_id}" data-post-id="${postId}">Show replies (${repliesCount})</button>` : ''}
        </div>
        <div class="replies hidden mt-2 ml-6">
          ${c.replies && c.replies.length > 0 ? c.replies.map(r => renderComment(r, postId, level + 1)).join('') : ''}
        </div>
      </div>
      ${c.owned ? `
        <div class="comment-tools absolute top-2 right-2">
          <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
          <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
            <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
            <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
          </div>
        </div>` : ''}
    </div>`;
}

function scrollToComment(commentId) {
  const modal = document.getElementById('commentsModal');
  const el = document.querySelector(`#commentsList .comment-item[data-comment-id="${commentId}"]`);
  if (el && modal) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function loadComments(postId, { scrollTo = null } = {}) {
  const list = document.getElementById('commentsList');
  if (!list) return;

  list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";

  const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
  const comments = await res.json();

  if (Array.isArray(comments) && comments.length > 0) {
    list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
  } else {
    list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
  }

  if (scrollTo) setTimeout(() => scrollToComment(scrollTo), 100);
}

document.querySelectorAll('.comment-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    currentPostId = btn.dataset.id;
    const modal = document.getElementById('commentsModal');
    modal.classList.remove('hidden');
    loadComments(currentPostId);
  });
});

document.getElementById('closeComments')?.addEventListener('click', () => {
  const modal = document.getElementById('commentsModal');
  modal.classList.add('hidden');
  replyTarget = null;
  document.querySelector('.reply-indicator')?.remove();
});

/* ------------------ ADD NEW COMMENT (EXACT EXPLORE BEHAVIOR) ------------------ */
document.getElementById('submitComment')?.addEventListener('click', async (e) => {
    e.preventDefault();

    const input = document.getElementById('newCommentInput');
    const text = input.value.trim();
    if (!text || !currentPostId) return;

    const parentId = replyTarget ? replyTarget.commentId : null;

    const formData = new FormData();
    formData.append('post_id', currentPostId);
    formData.append('text', text);
    if (parentId) formData.append('parent_id', parentId);

    try {
        const res = await fetch("index.php?action=addComment", {
            method: "POST",
            body: formData,
        });

        const data = await res.json();

        if (data.success && data.comment) {
            const newComment = data.comment;
            const list = document.getElementById("commentsList");

            input.value = "";
            document.querySelector(".reply-indicator")?.remove();

            /* ---------------- REPLY BEHAVIOR (EXACT EXPLORE) ---------------- */
            if (parentId) {
                const parentItem = list.querySelector(`.comment-item[data-comment-id="${parentId}"]`);
                const repliesContainer = parentItem.querySelector(".replies");

                repliesContainer.classList.remove("hidden");

                const toggle = parentItem.querySelector(".toggle-replies");
                if (toggle) toggle.textContent = "Hide replies";

                repliesContainer.insertAdjacentHTML(
                    "beforeend",
                    renderComment(newComment, currentPostId)
                );

                setTimeout(() => {
                    const newEl = repliesContainer.querySelector(
                        `.comment-item[data-comment-id="${newComment.comment_id}"]`
                    );
                    if (newEl) newEl.scrollIntoView({ behavior: "smooth", block: "center" });
                }, 50);

                replyTarget = null;
                return;
            }

            /* ---------------- NEW TOP-LEVEL COMMENT (EXACT EXPLORE) ---------------- */
            list.insertAdjacentHTML("afterbegin", renderComment(newComment, currentPostId));

            setTimeout(() => {
                const newEl = list.querySelector(
                    `.comment-item[data-comment-id="${newComment.comment_id}"]`
                );
                if (newEl) newEl.scrollIntoView({ behavior: "smooth", block: "center" });
            }, 50);

            replyTarget = null;
        }
    } catch (err) {
        console.error("Failed to add comment", err);
    }
});

/* ------------------ REPLY / TOGGLE / EDIT / DELETE (MATCH EXPLORE) ------------------ */

const commentsModalEl = document.getElementById('commentsModal');

if (commentsModalEl) {
    commentsModalEl.addEventListener('click', (e) => {
        // REPLY
        const replyBtnEl = e.target.closest('.reply-btn');
        if (replyBtnEl) {
            replyTarget = {
                commentId: replyBtnEl.dataset.commentId,
                username: replyBtnEl.dataset.username,
                postId: replyBtnEl.dataset.postId
            };
            const commentBox = document.getElementById('newCommentInput');
            if (commentBox) {
                const prev = document.querySelector('#commentsModal .reply-indicator');
                if (prev) prev.remove();

                const indicator = document.createElement('div');
                indicator.className = "reply-indicator text-xs text-indigo-600 mb-1 flex justify-between items-center";
                indicator.innerHTML = `<span>Replying to @${replyTarget.username}</span> <button class="cancel-reply text-red-500 text-xs">Cancel</button>`;

                const parent = commentBox.closest('.bg-white');
                if (parent && !parent.querySelector('.reply-indicator')) {
                    parent.prepend(indicator);
                }
                commentBox.focus();
            }
            return;
        }

        // CANCEL REPLY
        const cancelReplyBtn = e.target.closest('.cancel-reply');
        if (cancelReplyBtn) {
            const indicator = cancelReplyBtn.closest('.reply-indicator');
            if (indicator) indicator.remove();
            replyTarget = null;
            return;
        }

        // TOGGLE REPLIES
        const toggleBtn = e.target.closest('.toggle-replies');
        if (toggleBtn) {
            const commentItem = toggleBtn.closest('.comment-item');
            const replies = commentItem?.querySelector('.replies');
            if (!replies) return;

            const isHidden = replies.classList.contains('hidden');
            replies.classList.toggle('hidden');
            toggleBtn.textContent = isHidden
                ? 'Hide replies'
                : `Show replies (${replies.children.length})`;
            return;
        }

        // START EDIT
        const editBtn = e.target.closest('.edit-comment');
        if (editBtn) {
            const commentItem = editBtn.closest('.comment-item');
            if (!commentItem) return;
            const textEl = commentItem.querySelector('p.text-gray-700');
            if (!textEl) return;
            if (commentItem.querySelector('textarea')) return; // prevent multiple editors

            const originalText = textEl.textContent.trim();
            textEl.outerHTML = `
              <div class="edit-container mt-1">
                <textarea class="edit-textarea w-full border border-indigo-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">${originalText}</textarea>
                <div class="flex justify-end gap-2 mt-2">
                  <button class="save-edit bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded-md" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
                  <button class="cancel-edit bg-gray-200 hover:bg-gray-300 text-xs px-3 py-1 rounded-md">Cancel</button>
                </div>
              </div>
            `;
            const tools = commentItem.querySelector('.comment-options-menu');
            if (tools) tools.classList.add('hidden');
            commentItem.dataset.originalText = originalText;
            return;
        }

        // SAVE EDIT
        const saveBtn = e.target.closest('.save-edit');
        if (saveBtn) {
            const commentItem = saveBtn.closest('.comment-item');
            const textarea = commentItem?.querySelector('.edit-textarea');
            const newText = textarea?.value.trim();
            const commentId = saveBtn.dataset.commentId;
            if (!newText || !commentId) return;

            fetch('index.php?action=editComment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const editContainer = commentItem.querySelector('.edit-container');
                    if (editContainer) {
                        editContainer.outerHTML = `<p class="text-gray-700 text-sm"></p>`;
                        const p = commentItem.querySelector('p.text-gray-700');
                        if (p) p.textContent = newText;
                    }
                })
                .catch(() => alert('Failed to update comment.'));
            return;
        }

        // CANCEL EDIT
        const cancelEditBtn = e.target.closest('.cancel-edit');
        if (cancelEditBtn) {
            const commentItem = cancelEditBtn.closest('.comment-item');
            const orig = commentItem?.dataset?.originalText || '';
            const editContainer = commentItem?.querySelector('.edit-container');
            if (editContainer) {
                editContainer.outerHTML = `<p class="text-gray-700 text-sm"></p>`;
                const p = commentItem.querySelector('p.text-gray-700');
                if (p) p.textContent = orig;
            }
            return;
        }

        // DELETE COMMENT (OPEN OVERLAY)
        const delBtn = e.target.closest('.delete-comment');
        if (delBtn) {
            const commentId = delBtn.dataset.commentId;
            const postId = delBtn.dataset.postId;
            if (!commentId || !postId) return;

            const prev = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-40');
            if (prev) prev.remove();

            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[1100]';
            overlay.innerHTML = `
              <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
                <p class="text-gray-700 mb-5 text-base font-medium">🗑️ Are you sure you want to delete this comment?</p>
                <div class="flex justify-center gap-4">
                  <button class="cancel-delete bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">Cancel</button>
                  <button class="confirm-delete bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" data-comment-id="${commentId}" data-post-id="${postId}">Delete</button>
                </div>
              </div>
            `;
            document.body.appendChild(overlay);
            return;
        }
    });
}

// INLINE OPTIONS MENU TOGGLE (⋮)
document.addEventListener('click', (e) => {
    const modal = document.getElementById('commentsModal');
    if (!modal) return;

    const menuButton = e.target.closest('.comment-options');
    const openMenu = modal.querySelector('.comment-options-menu:not(.hidden)');

    if (!menuButton && !e.target.closest('.comment-options-menu')) {
        if (openMenu) openMenu.classList.add('hidden');
        return;
    }

    if (menuButton) {
        const menu = menuButton.nextElementSibling;
        if (!menu) return;
        if (menu.classList.contains('hidden')) {
            if (openMenu && openMenu !== menu) openMenu.classList.add('hidden');
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }
});

// CONFIRM / CANCEL DELETE OVERLAY
document.addEventListener('click', (e) => {
    const confirmBtn = e.target.closest('.confirm-delete');
    const cancelBtn = e.target.closest('.cancel-delete');
    if (!confirmBtn && !cancelBtn) return;

    const overlay = e.target.closest('.fixed.inset-0.bg-black.bg-opacity-40');
    if (cancelBtn && overlay) {
        overlay.remove();
        return;
    }

    if (confirmBtn && overlay) {
        const commentId = confirmBtn.dataset.commentId;
        const postId = confirmBtn.dataset.postId;
        if (!commentId || !postId) {
            overlay.remove();
            return;
        }

        fetch('index.php?action=deleteComment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `comment_id=${encodeURIComponent(commentId)}&post_id=${encodeURIComponent(postId)}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const list = document.getElementById('commentsList');
                    const toRemove = list?.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                    if (toRemove) toRemove.remove();

                    const commentToggle = document.querySelector(`.comment-btn[data-id="${postId}"]`);
                    if (commentToggle && typeof data.count !== 'undefined') {
                        commentToggle.innerHTML = `💬 ${data.count}`;
                    }
                }
            })
            .catch(() => alert('Failed to delete comment.'))
            .finally(() => {
                overlay.remove();
            });
    }
});

});
</script>
</html>