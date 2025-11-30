let currentPostId = null;
let replyTarget = null;

//file shares between home, profile
// -------- Render Comment --------
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
      ${(c.owned || window.IS_ADMIN_VIEW)
        ? `
          <div class="comment-tools absolute top-2 right-2">
            <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
            <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
              ${c.owned ? `<button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>` : ''}
              <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
            </div>
          </div>
        `
        : ''}
    </div>
  `;
}

// -------- Open Comments / Load Comments --------
document.addEventListener('click', (e) => {
    const toggle = e.target.closest('.comment-toggle');
    if (!toggle) return;

    const postId = toggle.dataset.id;
    currentPostId = postId;

    const section = document.getElementById(`comments-${postId}`);
    if (section) {
        section.classList.toggle('hidden');
        if (!section.dataset.loaded) {
            loadComments(postId);
            section.dataset.loaded = "true";
        }
    }
});

// -------- Load Comments from Server --------
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
    } catch {
        list.innerHTML = "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
    }
}
window.loadComments = loadComments;

// -------- Reply to a Comment --------
document.addEventListener('click', (e) => {
    const replyBtn = e.target.closest('.reply-btn');
    if (!replyBtn) return;

    replyTarget = {
        commentId: replyBtn.dataset.commentId,
        username: replyBtn.dataset.username,
        postId: replyBtn.dataset.postId
    };

    const input = document.getElementById(`newCommentInput-${replyTarget.postId}`);
    const parent = input.closest('.add-comment');

    const indicator = document.createElement('div');
    indicator.className = "reply-indicator text-xs text-indigo-600 mb-1 flex justify-between items-center";
    indicator.innerHTML = `<span>Replying to @${replyTarget.username}</span><button class="cancel-reply text-red-500 text-xs">Cancel</button>`;

    if (!parent.querySelector('.reply-indicator')) parent.prepend(indicator);

    input.focus();
});

// -------- Cancel Reply --------
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('cancel-reply')) {
        const indicator = e.target.closest('.reply-indicator');
        if (indicator) indicator.remove();
        replyTarget = null;
    }
});

// -------- Show / Hide Replies --------
document.addEventListener('click', (e) => {
    const toggleBtn = e.target.closest('.toggle-replies');
    if (!toggleBtn) return;

    const commentItem = toggleBtn.closest('.comment-item');
    const replies = commentItem.querySelector('.replies');
    if (!replies) return;

    const isHidden = replies.classList.contains('hidden');
    replies.classList.toggle('hidden');

    toggleBtn.textContent = isHidden
        ? 'Hide replies'
        : `Show replies (${replies.children.length})`;
});

// -------- Submit Comment / Reply --------
document.addEventListener('click', async (e) => {
    const submitBtn = e.target.closest('.comment-submit');
    if (!submitBtn) return;

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const postId = submitBtn.dataset.postId;
    const input = document.querySelector(`#newCommentInput-${postId}`);
    const text = input.value.trim();
    if (!text) return;

    const parentId = replyTarget && replyTarget.postId === postId
        ? replyTarget.commentId
        : null;

    try {
        const res = await fetch('index.php?action=addComment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:
                `post_id=${encodeURIComponent(postId)}&text=${encodeURIComponent(text)}`
                + (parentId ? `&parent_id=${encodeURIComponent(parentId)}` : '')
        });

        const data = await res.json();

        if (data.success) {
            input.value = '';
            const newComment = data.comment;
            const list = document.querySelector(`#commentsList-${postId}`);
            const html = renderComment(newComment, postId);

            const indicator = document.querySelector('.reply-indicator');
            if (indicator) indicator.remove();
            replyTarget = null;

            const emptyMsg = list.querySelector('.text-center.text-gray-400.italic');
            if (emptyMsg) emptyMsg.remove();

            if (parentId) {
                const parentItem = list.querySelector(`.comment-item[data-comment-id="${parentId}"]`);
                const replies = parentItem.querySelector('.replies');

                replies.classList.remove('hidden');
                replies.insertAdjacentHTML('beforeend', html);

                const toggleBtn = parentItem.querySelector('.toggle-replies');
                if (toggleBtn) toggleBtn.textContent = 'Hide replies';

            } else {
                list.insertAdjacentHTML('afterbegin', html);
            }

            const commentToggle = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
            if (commentToggle && data.count !== undefined) {
                commentToggle.innerHTML = `💬 ${data.count}`;
            }
        }

    } catch {
        alert('Failed to post comment.');
    }
});

// -------- Comment Options Menu --------
document.addEventListener('click', (e) => {
    const menuButton = e.target.closest('.comment-options');
    const openMenu = document.querySelector('.comment-options-menu:not(.hidden)');

    if (!menuButton && !e.target.closest('.comment-options-menu')) {
        if (openMenu) openMenu.classList.add('hidden');
        return;
    }

    if (menuButton) {
        const menu = menuButton.nextElementSibling;

        if (menu.classList.contains('hidden')) {
            if (openMenu) openMenu.classList.add('hidden');
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }
});

// -------- Edit Comment --------
document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-comment');
    if (!editBtn) return;

    const commentItem = editBtn.closest('.comment-item');
    const textEl = commentItem.querySelector('p.text-gray-700');
    const originalText = textEl.textContent.trim();

    commentItem.dataset.originalText = originalText;

    textEl.outerHTML = `
        <div class="edit-container mt-1">
            <textarea class="edit-textarea w-full border border-indigo-300 rounded-md p-2 text-sm">${originalText}</textarea>
            <div class="flex justify-end gap-2 mt-2">
                <button class="save-edit bg-indigo-500 text-white text-xs px-3 py-1 rounded-md" data-comment-id="${editBtn.dataset.commentId}" data-post-id="${editBtn.dataset.postId}">Save</button>
                <button class="cancel-edit bg-gray-200 text-xs px-3 py-1 rounded-md">Cancel</button>
            </div>
        </div>
    `;

    const menu = editBtn.closest('.comment-options-menu');
    if (menu) menu.classList.add('hidden');
});

// -------- Save Edited Comment --------
document.addEventListener('click', async (e) => {
    const saveBtn = e.target.closest('.save-edit');
    if (!saveBtn) return;

    const commentItem = saveBtn.closest('.comment-item');
    const textarea = commentItem.querySelector('.edit-textarea');
    const newText = textarea.value.trim();
    const commentId = saveBtn.dataset.commentId;

    if (!newText) return;

    try {
        const res = await fetch('index.php?action=editComment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `comment_id=${encodeURIComponent(commentId)}&text=${encodeURIComponent(newText)}`
        });

        const data = await res.json();

        if (data.success) {
            const p = document.createElement('p');
            p.className = 'text-gray-700 text-sm';
            p.textContent = newText;

            commentItem.querySelector('.edit-container').replaceWith(p);
        }

    } catch {
        alert('Failed to update comment.');
    }
});

// -------- Cancel Edit --------
document.addEventListener('click', (e) => {
    const cancelBtn = e.target.closest('.cancel-edit');
    if (!cancelBtn) return;

    const commentItem = cancelBtn.closest('.comment-item');
    const originalText = commentItem.dataset.originalText;

    const el = document.createElement('p');
    el.className = 'text-gray-700 text-sm';
    el.textContent = originalText;

    commentItem.querySelector('.edit-container').replaceWith(el);
});

// -------- Delete Comment --------
document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.delete-comment');
    if (!delBtn) return;

    const commentId = delBtn.dataset.commentId;
    const postId = delBtn.dataset.postId;

    const overlay = document.createElement('div');
    overlay.className = "fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50";
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
});

// -------- Confirm Delete --------
document.addEventListener('click', async (e) => {
    const confirmBtn = e.target.closest('.confirm-delete');
    const cancelBtn = e.target.closest('.cancel-delete');
    if (!confirmBtn && !cancelBtn) return;

    const overlay = e.target.closest('.fixed.inset-0.bg-black.bg-opacity-40');

    if (cancelBtn) {
        if (overlay) overlay.remove();
        return;
    }

    if (confirmBtn) {
        const commentId = confirmBtn.dataset.commentId;
        const postId = confirmBtn.dataset.postId;

        try {
            const res = await fetch('index.php?action=deleteComment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:
                    `comment_id=${encodeURIComponent(commentId)}`
                    + `&post_id=${encodeURIComponent(postId)}`
            });

            const data = await res.json();

            if (data.success) {
                if (overlay) overlay.remove();

                const item = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                if (item) item.remove();

                const countLabel = document.querySelector(`.comment-toggle[data-id="${postId}"]`);
                if (countLabel && data.count !== undefined) {
                    countLabel.innerHTML = `💬 ${data.count}`;
                }
            }

        } catch {
            alert('Failed to delete comment.');
        }
    }
});

// -------- Submit comment on Enter --------
document.addEventListener('keydown', (e) => {
    if (!e.target.classList.contains('comment-input')) return;

    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();

        const postId = e.target.dataset.postId;
        const btn = document.querySelector(`#submitComment-${postId}`);

        if (btn) btn.click();
    }
});