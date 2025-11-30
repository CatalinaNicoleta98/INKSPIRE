//file shared between explore,home,profile,search results (PostCard template) and notifications (SinglePost template)

// Like / Unlike post on Profile page
document.addEventListener('click', async (e) => {
    const likeSpan = e.target.closest('[data-action="like"]');
    if (!likeSpan) return;

    const card = likeSpan.closest('[data-post-id]');
    const postId = card?.dataset?.postId;
    if (!postId) {
        console.error('Missing post_id for like/unlike action');
        return;
    }

    try {
        const res = await fetch('index.php?action=toggleLike', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${encodeURIComponent(postId)}`
        });

        const data = await res.json();

        if (data.success) {
            const icon = data.liked ? '❤️' : '🤍';
            likeSpan.innerHTML = `${icon} ${data.likes}`;
            likeSpan.style.color = data.liked ? '#ef4444' : '#9ca3af';
        } else {
            alert(data.message || 'Error updating like.');
        }
    } catch (err) {
        console.error(err);
        alert('Error sending like request.');
    }

    
});