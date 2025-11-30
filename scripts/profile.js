// scripts/profile.js

// Profile picture lightbox
document.addEventListener('DOMContentLoaded', () => {
  const profilePic = document.getElementById('profilePic');
  const lightbox   = document.getElementById('profilePicLightbox');
  const enlarged   = document.getElementById('profilePicEnlarged');

  if (profilePic && lightbox && enlarged) {
    profilePic.addEventListener('click', () => {
      enlarged.src = profilePic.src;
      lightbox.classList.remove('hidden');
    });

    lightbox.addEventListener('click', () => {
      lightbox.classList.add('hidden');
    });
  }
});

// Followers / Following dropdowns
document.addEventListener('DOMContentLoaded', () => {
  const followerToggle    = document.querySelector('.follower-toggle');
  const followingToggle   = document.querySelector('.following-toggle');
  const followerDropdown  = followerToggle?.querySelector('.dropdown-menu') || null;
  const followingDropdown = followingToggle?.querySelector('.dropdown-menu') || null;

  function closeAllDropdowns(e) {
    if (followerToggle && followerDropdown && !followerToggle.contains(e.target)) {
      followerDropdown.classList.remove('active');
    }
    if (followingToggle && followingDropdown && !followingToggle.contains(e.target)) {
      followingDropdown.classList.remove('active');
    }
  }

  followerToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (followerDropdown) {
      followerDropdown.classList.toggle('active');
    }
    if (followingDropdown) {
      followingDropdown.classList.remove('active');
    }
  });

  followingToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (followingDropdown) {
      followingDropdown.classList.toggle('active');
    }
    if (followerDropdown) {
      followerDropdown.classList.remove('active');
    }
  });

  document.addEventListener('click', closeAllDropdowns);
});

// Block user confirmation modal
document.addEventListener('click', (e) => {
  const blockLink = e.target.closest('a[href*="action=block"]');
  if (!blockLink) return;

  e.preventDefault();
  const modal      = document.getElementById('blockUserModal');
  const cancelBtn  = document.getElementById('cancelBlockBtn');
  const confirmBtn = document.getElementById('confirmBlockBtn');

  if (!modal || !cancelBtn || !confirmBtn) {
    // Fallback: just go directly
    window.location.href = blockLink.href;
    return;
  }

  window.userToBlockHref = blockLink.href;
  modal.classList.remove('hidden');
});

document.addEventListener('click', (e) => {
  const cancelBtn = e.target.closest('#cancelBlockBtn');
  if (!cancelBtn) return;

  const modal = document.getElementById('blockUserModal');
  if (modal) modal.classList.add('hidden');
  window.userToBlockHref = null;
});

document.addEventListener('click', (e) => {
  const confirmBtn = e.target.closest('#confirmBlockBtn');
  if (!confirmBtn) return;

  if (window.userToBlockHref) {
    window.location.href = window.userToBlockHref;
  }
});