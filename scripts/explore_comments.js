// =======================================
// comments.js — Explore comments system shared also by postcard template for search results
// =======================================
document.addEventListener('DOMContentLoaded', () => {
  // Global-ish state for this module
  let currentPostId = null;
  let replyTarget = null;

  // Normalize admin view flag (PHP sets window.IS_ADMIN_VIEW as "true"/"false")
  const IS_ADMIN_VIEW =
    window.IS_ADMIN_VIEW === true || window.IS_ADMIN_VIEW === 'true';

  // -----------------------
  // RENDER HELPERS
  // -----------------------

  // Render one comment (recursively for replies)
  function renderComment(c, postId) {
    const repliesCount = c.replies ? c.replies.length : 0;

    const toolsHtml =
      (c.owned || IS_ADMIN_VIEW)
        ? `
      <div class="comment-tools absolute top-2 right-2">
        <button class="comment-options text-gray-400 hover:text-gray-600 transition"
                data-comment-id="${c.comment_id}"
                data-post-id="${postId}">
          ⋮
        </button>
        <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
          ${c.owned
            ? `<button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50"
                       data-comment-id="${c.comment_id}"
                       data-post-id="${postId}">
                 Edit
               </button>`
            : ''}
          <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50"
                  data-comment-id="${c.comment_id}"
                  data-post-id="${postId}">
            Delete
          </button>
        </div>
      </div>`
        : '';

    return `
      <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2"
           data-comment-id="${c.comment_id}">
        <div class="w-full">
          <p class="text-gray-700 text-sm">${c.text}</p>
          <p class="text-xs text-gray-500">
            <a href="index.php?action=profile&user_id=${c.user_id}"
               class="text-indigo-600 hover:underline">@${c.username}</a>
            • ${c.created_at}
          </p>

          <div class="flex gap-2 mt-1">
            <button class="reply-btn text-xs text-indigo-500"
                    data-comment-id="${c.comment_id}"
                    data-username="${c.username}"
                    data-post-id="${postId}">
              ↩️ Reply
            </button>
            ${
              repliesCount > 0
                ? `<button class="toggle-replies text-xs text-indigo-400"
                           data-comment-id="${c.comment_id}"
                           data-post-id="${postId}">
                     Show replies (${repliesCount})
                   </button>`
                : ''
            }
          </div>

          <div class="replies hidden mt-2 ml-6">
            ${
              c.replies && c.replies.length > 0
                ? c.replies.map(r => renderComment(r, postId)).join('')
                : ''
            }
          </div>
        </div>

        ${toolsHtml}
      </div>
    `;
  }

  // Scroll a given comment into view inside the modal
  function scrollToComment(commentId) {
    const commentEl = document.querySelector(
      `#commentsList .comment-item[data-comment-id="${commentId}"]`
    );
    if (!commentEl) return;
    commentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  // -----------------------
  // LOADING COMMENTS
  // -----------------------

  async function loadComments(postId, options = {}) {
    const commentsList = document.getElementById('commentsList');
    if (!commentsList) return;

    commentsList.innerHTML =
      "<p class='text-center text-gray-400 italic'>Loading...</p>";

    try {
      const res = await fetch(
        `index.php?action=getCommentsByPost&post_id=${postId}`
      );
      const comments = await res.json();

      if (Array.isArray(comments) && comments.length > 0) {
        commentsList.innerHTML = comments
          .map(c => renderComment(c, postId))
          .join('');
      } else {
        commentsList.innerHTML =
          "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
      }

      if (options.scrollTo) {
        setTimeout(() => scrollToComment(options.scrollTo), 100);
      }
    } catch (err) {
      console.error(err);
      commentsList.innerHTML =
        "<p class='text-center text-red-400 italic'>Error loading comments.</p>";
    }
  }

  // -----------------------
  // OPEN / CLOSE MODAL
  // -----------------------

  const commentsModal = document.getElementById('commentsModal');
  const closeComments = document.getElementById('closeComments');

  // Open modal for a given post
  document.addEventListener('click', e => {
    const toggle = e.target.closest('.comment-btn');
    if (!toggle) return;

    const postId = toggle.dataset.id;
    currentPostId = postId;

    if (commentsModal) {
      commentsModal.classList.remove('hidden');
      loadComments(postId);
    }
  });

  // Close modal (X button)
  if (closeComments && commentsModal) {
    closeComments.addEventListener('click', () => {
      commentsModal.classList.add('hidden');
      replyTarget = null;
      const ind = commentsModal.querySelector('.reply-indicator');
      if (ind) ind.remove();
    });
  }

  // -----------------------
  // REPLY HANDLING
  // -----------------------

  // Click "Reply"
  if (commentsModal) {
    commentsModal.addEventListener('click', e => {
      const replyBtn = e.target.closest('.reply-btn');
      if (!replyBtn) return;

      replyTarget = {
        commentId: replyBtn.dataset.commentId,
        username: replyBtn.dataset.username,
        postId: replyBtn.dataset.postId
      };

      const commentBox = document.getElementById('newCommentInput');
      if (!commentBox) return;

      // Remove existing indicator if any
      const prev = commentsModal.querySelector('.reply-indicator');
      if (prev) prev.remove();

      // Insert indicator above input
      const indicator = document.createElement('div');
      indicator.className =
        'reply-indicator text-xs text-indigo-600 mb-1 flex justify-between items-center';
      indicator.innerHTML = `
        <span>Replying to @${replyTarget.username}</span>
        <button class="cancel-reply text-red-500 text-xs">Cancel</button>
      `;

      const parent = commentBox.closest('.bg-white');
      if (parent && !parent.querySelector('.reply-indicator')) {
        parent.prepend(indicator);
      }

      commentBox.focus();
    });

    // Cancel reply
    commentsModal.addEventListener('click', e => {
      if (!e.target.closest('.cancel-reply')) return;
      const indicator = e.target.closest('.reply-indicator');
      if (indicator) indicator.remove();
      replyTarget = null;
    });
  }

  // -----------------------
  // SHOW / HIDE REPLIES
  // -----------------------
  if (commentsModal) {
    commentsModal.addEventListener('click', e => {
      const toggleBtn = e.target.closest('.toggle-replies');
      if (!toggleBtn) return;

      const commentItem = toggleBtn.closest('.comment-item');
      if (!commentItem) return;

      const replies = commentItem.querySelector('.replies');
      if (!replies) return;

      const isHidden = replies.classList.contains('hidden');
      replies.classList.toggle('hidden');

      if (isHidden) {
        toggleBtn.textContent = 'Hide replies';
      } else {
        const count = replies.children.length;
        toggleBtn.textContent = `Show replies (${count})`;
      }
    });
  }

  // -----------------------
  // ADD COMMENT / REPLY
  // -----------------------
  if (commentsModal) {
    commentsModal.addEventListener('click', async e => {
      const submitBtn = e.target.closest('.comment-submit');
      if (!submitBtn) return;

      const postId = currentPostId;
      const input = document.getElementById('newCommentInput');
      if (!input) return;

      const text = input.value.trim();
      if (!text) return;

      const parentId =
        replyTarget && replyTarget.postId === postId
          ? replyTarget.commentId
          : null;

      try {
        const res = await fetch('index.php?action=addComment', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body:
            `post_id=${encodeURIComponent(postId)}` +
            `&text=${encodeURIComponent(text)}` +
            (parentId
              ? `&parent_id=${encodeURIComponent(parentId)}`
              : '')
        });

        const data = await res.json();

        if (!data.success) return;

        input.value = '';

        const indicator = commentsModal.querySelector('.reply-indicator');
        if (indicator) indicator.remove();

        const newComment = data.comment;
        const commentsList = document.getElementById('commentsList');
        if (!newComment || !newComment.comment_id || !commentsList) {
          replyTarget = null;
          return;
        }

        if (parentId) {
          // Add as reply
          const parentItem = commentsList.querySelector(
            `.comment-item[data-comment-id="${parentId}"]`
          );
          if (parentItem) {
            const repliesContainer =
              parentItem.querySelector('.replies');
            if (repliesContainer) {
              repliesContainer.classList.remove('hidden');

              const toggleBtn = parentItem.querySelector(
                `.toggle-replies[data-comment-id="${parentId}"]`
              );
              if (toggleBtn) toggleBtn.textContent = 'Hide replies';

              repliesContainer.insertAdjacentHTML(
                'beforeend',
                renderComment(newComment, postId)
              );

              setTimeout(() => {
                const el = repliesContainer.querySelector(
                  `.comment-item[data-comment-id="${newComment.comment_id}"]`
                );
                if (el) {
                  el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                  });
                }
              }, 50);
            }
          }
        } else {
          // Add as top-level comment
          commentsList.insertAdjacentHTML(
            'afterbegin',
            renderComment(newComment, postId)
          );

          setTimeout(() => {
            const el = commentsList.querySelector(
              `.comment-item[data-comment-id="${newComment.comment_id}"]`
            );
            if (el) {
              el.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
            }
          }, 50);
        }

        // Update 💬 counter under post card
        const commentToggle = document.querySelector(
          `.comment-btn[data-id="${postId}"]`
        );
        if (commentToggle) {
          if (typeof data.count !== 'undefined') {
            commentToggle.innerHTML = `💬 ${data.count}`;
          } else {
            const parts = commentToggle.textContent
              .trim()
              .split(' ');
            const num = parseInt(parts[1], 10);
            if (!isNaN(num)) {
              commentToggle.innerHTML = `💬 ${num + 1}`;
            }
          }
        }

        replyTarget = null;
      } catch (err) {
        console.error(err);
        alert('Failed to post comment.');
      }
    });
  }

  // -----------------------
  // COMMENT OPTIONS MENU
  // -----------------------
  document.addEventListener('click', e => {
    const modal = document.getElementById('commentsModal');
    if (!modal) return;

    const openMenu = modal.querySelector(
      '.comment-options-menu:not(.hidden)'
    );

    const menuButton = e.target.closest('.comment-options');

    // Clicked outside modal: close all menus
    if (!modal.contains(e.target)) {
      if (openMenu) openMenu.classList.add('hidden');
      return;
    }

    // Clicked neither menu nor button: close
    if (
      !menuButton &&
      !e.target.closest('.comment-options-menu')
    ) {
      if (openMenu) openMenu.classList.add('hidden');
      return;
    }

    // Toggle clicked menu
    if (menuButton) {
      const menu = menuButton.nextElementSibling;
      if (!menu) return;

      if (menu.classList.contains('hidden')) {
        if (openMenu) openMenu.classList.add('hidden');
        menu.classList.remove('hidden');
      } else {
        menu.classList.add('hidden');
      }
    }
  });

  // -----------------------
  // EDIT COMMENT
  // -----------------------
  if (commentsModal) {
    commentsModal.addEventListener('click', e => {
      const editBtn = e.target.closest('.edit-comment');
      if (!editBtn) return;

      const commentItem = editBtn.closest('.comment-item');
      if (!commentItem) return;

      const textEl = commentItem.querySelector('p.text-gray-700');
      if (!textEl) return;

      // prevent double editors
      if (commentItem.querySelector('.edit-textarea')) return;

      const originalText = textEl.textContent.trim();
      commentItem.dataset.originalText = originalText;

      textEl.outerHTML = `
        <div class="edit-container mt-1">
          <textarea class="edit-textarea w-full border border-indigo-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">${originalText}</textarea>
          <div class="flex justify-end gap-2 mt-2">
            <button class="save-edit bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded-md"
                    data-comment-id="${editBtn.dataset.commentId}"
                    data-post-id="${editBtn.dataset.postId}">
              Save
            </button>
            <button class="cancel-edit bg-gray-200 hover:bg-gray-300 text-xs px-3 py-1 rounded-md">
              Cancel
            </button>
          </div>
        </div>
      `;

      const tools = commentItem.querySelector('.comment-options-menu');
      if (tools) tools.classList.add('hidden');
    });

    // Save edit
    commentsModal.addEventListener('click', async e => {
      const saveBtn = e.target.closest('.save-edit');
      if (!saveBtn) return;

      const commentItem = saveBtn.closest('.comment-item');
      if (!commentItem) return;

      const textarea = commentItem.querySelector('.edit-textarea');
      if (!textarea) return;

      const newText = textarea.value.trim();
      if (!newText) return;

      const commentId = saveBtn.dataset.commentId;

      try {
        const res = await fetch('index.php?action=editComment', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body:
            `comment_id=${encodeURIComponent(commentId)}` +
            `&text=${encodeURIComponent(newText)}`
        });
        const data = await res.json();

        if (!data.success) return;

        const editContainer = commentItem.querySelector(
          '.edit-container'
        );
        if (editContainer) {
          editContainer.outerHTML =
            '<p class="text-gray-700 text-sm"></p>';
          const p = commentItem.querySelector('p.text-gray-700');
          if (p) p.textContent = newText;
        }
      } catch (err) {
        console.error(err);
        alert('Failed to update comment.');
      }
    });

    // Cancel edit
    commentsModal.addEventListener('click', e => {
      const cancelBtn = e.target.closest('.cancel-edit');
      if (!cancelBtn) return;

      const commentItem = cancelBtn.closest('.comment-item');
      if (!commentItem) return;

      const originalText = commentItem.dataset.originalText || '';
      const editContainer = commentItem.querySelector('.edit-container');

      if (editContainer) {
        editContainer.outerHTML =
          '<p class="text-gray-700 text-sm"></p>';
        const p = commentItem.querySelector('p.text-gray-700');
        if (p) p.textContent = originalText;
      }
    });
  }

  // -----------------------
  // DELETE COMMENT (with overlay)
  // -----------------------
  if (commentsModal) {
    commentsModal.addEventListener('click', e => {
      const delBtn = e.target.closest('.delete-comment');
      if (!delBtn) return;

      const commentId = delBtn.dataset.commentId;
      const postId = delBtn.dataset.postId;

      const oldOverlay = document.querySelector(
        '.fixed.inset-0.bg-black.bg-opacity-40'
      );
      if (oldOverlay) oldOverlay.remove();

      const overlay = document.createElement('div');
      overlay.className =
        'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[1100]';
      overlay.innerHTML = `
        <div class="bg-white rounded-xl p-6 text-center shadow-xl max-w-sm w-full">
          <p class="text-gray-700 mb-5 text-base font-medium">
            🗑️ Are you sure you want to delete this comment?
          </p>
          <div class="flex justify-center gap-4">
            <button class="cancel-delete bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
              Cancel
            </button>
            <button class="confirm-delete bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition"
                    data-comment-id="${commentId}"
                    data-post-id="${postId}">
              Delete
            </button>
          </div>
        </div>
      `;
      document.body.appendChild(overlay);
    });
  }

  // Submit comment using ENTER (Shift+Enter = newline)
  document.addEventListener('keydown', function(e) {
    if (!e.target.classList.contains('comment-input')) return;

    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      const btn = document.getElementById('submitComment');
      if (btn) btn.click();
    }
  });

  // Confirm / cancel delete (overlay)
  document.addEventListener('click', async e => {
    const overlay = e.target.closest(
      '.fixed.inset-0.bg-black.bg-opacity-40'
    );
    const confirmBtn = e.target.closest('.confirm-delete');
    const cancelBtn = e.target.closest('.cancel-delete');

    if (!confirmBtn && !cancelBtn) return;

    if (cancelBtn && overlay) {
      overlay.remove();
      return;
    }

    if (!confirmBtn) return;

    const commentId = confirmBtn.dataset.commentId;
    const postId = confirmBtn.dataset.postId;

    try {
      const res = await fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body:
          `comment_id=${encodeURIComponent(commentId)}` +
          `&post_id=${encodeURIComponent(postId)}`
      });

      const data = await res.json();
      if (!data.success) return;

      if (overlay) overlay.remove();

      const list = document.getElementById('commentsList');
      if (list) {
        const toRemove = list.querySelector(
          `.comment-item[data-comment-id="${commentId}"]`
        );
        if (toRemove) toRemove.remove();
      }

      const commentToggle = document.querySelector(
        `.comment-btn[data-id="${postId}"]`
      );
      if (commentToggle && typeof data.count !== 'undefined') {
        commentToggle.innerHTML = `💬 ${data.count}`;
      }
    } catch (err) {
      console.error(err);
      alert('Failed to delete comment.');
    }
  });
});