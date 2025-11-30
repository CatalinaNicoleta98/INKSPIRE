// =========================
// HEADER.JS (External Script)
// =========================

// Elements
const profileBtn = document.getElementById("profileBtn");
const dropdown = document.getElementById("dropdownMenu");
const notifBtn = document.getElementById("notifBtn");
const notifDropdown = document.getElementById("notifDropdown");

const mobileProfileBtn = document.getElementById("mobileProfileBtn");
const mobileDropdown = document.getElementById("mobileDropdownMenu");

// ---------------------------
// PROFILE DROPDOWN (Desktop)
// ---------------------------
if (profileBtn && dropdown) {
  profileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.toggle("hidden");
  });
}

// ---------------------------
// PROFILE DROPDOWN (Mobile)
// ---------------------------
if (mobileProfileBtn && mobileDropdown) {
  mobileProfileBtn.addEventListener("click", (e) => {
    e.stopPropagation();

    if (mobileDropdown.classList.contains("hidden")) {
      mobileDropdown.classList.remove("hidden");
      setTimeout(() => {
        mobileDropdown.classList.remove("translate-y-2", "opacity-0");
      }, 10);
    } else {
      mobileDropdown.classList.add("translate-y-2", "opacity-0");
      setTimeout(() => {
        mobileDropdown.classList.add("hidden");
      }, 200);
    }
  });
}

// ---------------------------
// NOTIFICATIONS DROPDOWN
// ---------------------------
if (notifBtn && notifDropdown) {
  notifBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle("hidden");
  });
}

// ---------------------------
// CLOSE on outside click
// ---------------------------
window.addEventListener("click", (e) => {
  if (profileBtn && dropdown && !profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.add("hidden");
  }
  if (notifBtn && notifDropdown && !notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
    notifDropdown.classList.add("hidden");
  }
  if (mobileDropdown && mobileProfileBtn &&
      !mobileDropdown.contains(e.target) && !mobileProfileBtn.contains(e.target)) {
    mobileDropdown.classList.add("translate-y-2", "opacity-0");
    setTimeout(() => {
      mobileDropdown.classList.add("hidden");
    }, 200);
  }
});

// ---------------------------
// AJAX HELPERS
// ---------------------------
async function notifRequest(url, method = 'GET') {
  const res = await fetch(url, { method });
  return res.json();
}

function updateBadge(count) {
  const badge = document.querySelector('#notifBtn span');

  if (!badge && count > 0) {
    const span = document.createElement('span');
    span.className = "absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full shadow";
    span.textContent = count;
    document.querySelector('#notifBtn').appendChild(span);

  } else if (badge) {
    if (count > 0) badge.textContent = count;
    else badge.remove();
  }
}

// ---------------------------
// NOTIFICATION BUTTON ACTIONS
// ---------------------------
document.addEventListener('click', async (e) => {

  // MARK READ
  if (e.target.classList.contains('js-mark-read')) {
    const id = e.target.dataset.id;
    const container = e.target.closest('.flex.items-start');

    const res = await notifRequest(`index.php?action=markNotificationRead&id=${id}`);
    if (res.success) {
      container.style.opacity = 0;
      setTimeout(() => container.remove(), 200);
      updateBadge(res.unread);
    }
  }

  // MARK UNREAD
  if (e.target.classList.contains('js-mark-unread')) {
    const id = e.target.dataset.id;
    const container = e.target.closest('.flex.items-start');

    const res = await notifRequest(`index.php?action=markNotificationUnread&id=${id}`);
    if (res.success) {
      container.style.opacity = 0;
      setTimeout(() => container.remove(), 200);
      updateBadge(res.unread);
    }
  }

  // DELETE SINGLE
  if (e.target.classList.contains('js-delete')) {
    const id = e.target.dataset.id;
    const container = e.target.closest('.flex.items-start');

    const res = await notifRequest(`index.php?action=deleteNotification&id=${id}`);
    if (res.success) {
      container.style.opacity = 0;
      setTimeout(() => container.remove(), 200);
      updateBadge(res.unread);
    }
  }

  // MARK ALL READ
  if (e.target.classList.contains('js-mark-all-read')) {
    const res = await notifRequest('index.php?action=markAllNotificationsRead');

    if (res.success) {
      document.querySelectorAll('.js-mark-read').forEach(el => {
        const c = el.closest('.flex.items-start');
        if (c) {
          c.style.opacity = 0;
          setTimeout(() => c.remove(), 200);
        }
      });
      updateBadge(0);
    }
  }

  // DELETE ALL
  if (e.target.classList.contains('js-clear-all')) {
    const res = await notifRequest('index.php?action=deleteAllNotifications');

    if (res.success) {
      document.querySelectorAll('#notifDropdown .flex.items-start').forEach(c => {
        c.style.opacity = 0;
        setTimeout(() => c.remove(), 200);
      });
      updateBadge(0);
    }
  }
});