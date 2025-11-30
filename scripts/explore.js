document.addEventListener('DOMContentLoaded', () => {

  // Close create post modal (when opened from sidebar)
  const postModal = document.getElementById('postModal');
  const closeModal = document.getElementById('closeModal');

  if (postModal && closeModal) {
      closeModal.onclick = () => postModal.classList.add('hidden');
      window.onclick = e => { if (e.target === postModal) postModal.classList.add('hidden'); };
  }

  // Lightbox
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');

  document.querySelectorAll('.post img').forEach(img => {
    if (img.closest('.flex.items-center.gap-3')) return; // ignore profile pics

    img.addEventListener('click', () => {
      lightboxImg.src = img.src;
      lightbox.classList.remove('hidden');
    });
  });

  if (lightbox) {
    lightbox.addEventListener('click', () => lightbox.classList.add('hidden'));
  }

  // LIKE button AJAX
  // document.querySelectorAll('.like-btn').forEach(btn => {
  //   btn.addEventListener('click', async (e) => {
  //     e.stopPropagation();
  //     const postId = btn.getAttribute('data-id');
  //     try {
  //       const response = await fetch(`index.php?action=toggleLike&post_id=${postId}&t=${Date.now()}`, {
  //         cache: 'no-store'
  //       });
  //       const data = await response.json();
  //       if (data.success) {
  //         const icon = data.liked ? '❤️' : '🤍';
  //         btn.innerHTML = `${icon} ${data.likes}`;
  //         btn.style.color = data.liked ? '#f87171' : '#9ca3af';
  //       }
  //     } catch (err) {
  //       console.error('Like error', err);
  //     }
  //   });
  // });

  // Guest modal
  const guestModal = document.getElementById('guestModal');
  const closeGuestModal = document.getElementById('closeGuestModal');

  document.querySelectorAll('.guest-like, .guest-comment, .guest-profile').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const modalText = guestModal.querySelector('p');

      if (btn.classList.contains('guest-like')) {
        modalText.textContent = 'Please log in or register to like posts.';
      } else if (btn.classList.contains('guest-comment')) {
        modalText.textContent = 'Please log in or register to view or post comments.';
      } else {
        modalText.textContent = 'Please log in or register to view user profiles.';
      }

      guestModal.classList.remove('hidden');
    });
  });

  if (closeGuestModal) {
    closeGuestModal.addEventListener('click', () => guestModal.classList.add('hidden'));
  }

  window.addEventListener('click', (e) => {
    if (e.target === guestModal) guestModal.classList.add('hidden');
  });

  

});