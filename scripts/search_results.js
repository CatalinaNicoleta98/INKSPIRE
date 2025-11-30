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
    // document.querySelectorAll('.like-btn').forEach(btn => {
    //     btn.addEventListener('click', async (e) => {
    //         e.stopPropagation();
    //         const postId = btn.getAttribute('data-id');

    //         try {
    //             const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`, {
    //                 cache: 'no-store'
    //             });
    //             const data = await response.json();

    //             if (data.success) {
    //                 btn.innerHTML = `❤️ ${data.likes}`;
    //                 btn.style.color = data.liked ? '#f87171' : 'white';
    //             }
    //         } catch (err) {
    //             console.error('Like error', err);
    //         }
    //     });
    // });

    /* ------------------ FULL EXPLORE COMMENTS SYSTEM ------------------ */

    // let currentPostId = null;
    // let replyTarget = null;

    // function renderComment(c, postId, level = 0) {
    //   const repliesCount = c.replies ? c.replies.length : 0;
    //   return `
    //     <div class="comment-item relative bg-indigo-50 p-2 rounded-md shadow-sm mb-2" data-comment-id="${c.comment_id}">
    //       <div class="w-full">
    //         <p class="text-gray-700 text-sm">${c.text}</p>
    //         <p class="text-xs text-gray-500">
    //           <a href="index.php?action=profile&user_id=${c.user_id}"
    //              class="text-indigo-600 hover:underline">@${c.username}</a> • ${c.created_at}
    //         </p>
    //         <div class="flex gap-2 mt-1">
    //           <button class="reply-btn text-xs text-indigo-500" data-comment-id="${c.comment_id}" data-username="${c.username}" data-post-id="${postId}">↩️ Reply</button>
    //           ${repliesCount > 0 ? `<button class="toggle-replies text-xs text-indigo-400" data-comment-id="${c.comment_id}" data-post-id="${postId}">Show replies (${repliesCount})</button>` : ''}
    //         </div>
    //         <div class="replies hidden mt-2 ml-6">
    //           ${c.replies && c.replies.length > 0 ? c.replies.map(r => renderComment(r, postId, level + 1)).join('') : ''}
    //         </div>
    //       </div>
    //       ${c.owned ? `
    //         <div class="comment-tools absolute top-2 right-2">
    //           <button class="comment-options text-gray-400 hover:text-gray-600 transition" data-comment-id="${c.comment_id}" data-post-id="${postId}">⋮</button>
    //           <div class="comment-options-menu hidden absolute right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-md z-10">
    //             <button class="edit-comment block w-full text-left px-3 py-1 text-sm hover:bg-indigo-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Edit</button>
    //             <button class="delete-comment block w-full text-left px-3 py-1 text-sm text-red-600 hover:bg-red-50" data-comment-id="${c.comment_id}" data-post-id="${postId}">Delete</button>
    //           </div>
    //         </div>` : ''}
    //     </div>`;
    // }

    // document.addEventListener('keydown', function (e) {
    //     const input = e.target.closest('#commentsModal .comment-input');
    //     if (!input) return;

    //     if (e.key === 'Enter' && !e.shiftKey) {
    //         e.preventDefault();
    //         const submit = document.getElementById('submitComment');
    //         if (submit) submit.click();
    //     }
    // });

    // async function loadComments(postId) {
    //     const list = document.getElementById('commentsList');
    //     if (!list) return;

    //     list.innerHTML = "<p class='text-center text-gray-400 italic'>Loading...</p>";

    //     try {
    //         const res = await fetch(`index.php?action=getCommentsByPost&post_id=${postId}`);
    //         const comments = await res.json();

    //         if (Array.isArray(comments) && comments.length > 0) {
    //             list.innerHTML = comments.map(c => renderComment(c, postId)).join('');
    //         } else {
    //             list.innerHTML = "<p class='text-center text-gray-400 italic'>No comments yet.</p>";
    //         }
    //     } catch {
    //         list.innerHTML = "<p class='text-center text-red-400 italic'>Failed to load comments</p>";
    //     }
    // }

    // document.querySelectorAll('.comment-btn').forEach(btn => {
    //   btn.addEventListener('click', () => {
    //     currentPostId = btn.dataset.id;
    //     document.getElementById('commentsModal').classList.remove('hidden');
    //     loadComments(currentPostId);
    //   });
    // });

    // document.getElementById('closeComments')?.addEventListener('click', () => {
    //   document.getElementById('commentsModal').classList.add('hidden');
    //   replyTarget = null;
    //   document.querySelector('.reply-indicator')?.remove();
    // });

    // document.getElementById('submitComment')?.addEventListener('click', async (e) => {
    //     e.preventDefault();

    //     const input = document.getElementById('newCommentInput');
    //     const text = input.value.trim();
    //     if (!text || !currentPostId) return;

    //     const parentId = replyTarget ? replyTarget.commentId : null;

    //     const formData = new FormData();
    //     formData.append('post_id', currentPostId);
    //     formData.append('text', text);
    //     if (parentId) formData.append('parent_id', parentId);

    //     try {
    //         const res = await fetch("index.php?action=addComment", {
    //             method: "POST",
    //             body: formData,
    //         });

    //         const data = await res.json();

    //         if (data.success && data.comment) {
    //             input.value = "";
    //             document.querySelector(".reply-indicator")?.remove();
    //             replyTarget = null;

    //             const list = document.getElementById("commentsList");

    //             if (parentId) {
    //                 const parentItem = list.querySelector(`.comment-item[data-comment-id="${parentId}"]`);
    //                 const repliesContainer = parentItem.querySelector(".replies");
    //                 repliesContainer.classList.remove("hidden");

    //                 repliesContainer.insertAdjacentHTML(
    //                     "beforeend",
    //                     renderComment(data.comment, currentPostId)
    //                 );
    //             } else {
    //                 list.insertAdjacentHTML("afterbegin", renderComment(data.comment, currentPostId));
    //             }
    //         }
    //     } catch (err) {
    //         console.error("Failed to add comment", err);
    //     }
    // });

});