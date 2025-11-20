<?php
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../Models/ProfileModel.php';

Session::start();
$loggedInUser = Session::get('user'); // use a distinct variable to avoid overwriting $user used by ProfileController

if ($loggedInUser) {
    $profileModel = new ProfileModel();
    $loggedProfile = $profileModel->getProfileByUserId($loggedInUser['user_id']);
    if ($loggedProfile) {
        $loggedInUser['profile_picture'] = $loggedProfile['profile_picture'];
    }
}

$profilePic = !empty($loggedInUser['profile_picture'])
    ? htmlspecialchars($loggedInUser['profile_picture'])
    : 'https://via.placeholder.com/40';
?>
<script src="https://cdn.tailwindcss.com"></script>
<header class="fixed top-0 left-0 w-full bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 shadow-sm z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
    <div class="flex items-center space-x-3">
      <div class="text-2xl font-bold text-indigo-500 tracking-tight hover:text-indigo-600 transition duration-200 cursor-pointer">
        Inkspire
      </div>
    </div>

    <?php if ($loggedInUser): ?>
      <?php
          require_once __DIR__ . '/../../Models/NotificationModel.php';
          if ($loggedInUser) {
              $notifModel = new NotificationModel();
              $unread = $notifModel->getUnreadCount($loggedInUser['user_id']);
              $notifications = $notifModel->getNotificationsByUser($loggedInUser['user_id']);
          }
      ?>

      <div class="relative flex items-center mr-4">
        <button id="notifBtn" type="button" class="relative flex items-center focus:outline-none">
          <!-- Refined Bell Icon (Heroicon-style) -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke-width="1.8" stroke="currentColor"
               class="w-7 h-7 text-indigo-600 hover:text-indigo-700 transition duration-150">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.657a2.5 2.5 0 01-5.714 0M12 6.5c-2.623 0-4.5 1.877-4.5 4.5v2.25c0 .414-.336.75-.75.75h-.75a.75.75 0 000 1.5h14a.75.75 0 000-1.5h-.75a.75.75 0 01-.75-.75V11c0-2.623-1.877-4.5-4.5-4.5z" />
          </svg>

          <?php if (!empty($unread)): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full shadow">
              <?= $unread ?>
            </span>
          <?php endif; ?>
        </button>

        <!-- Notifications dropdown -->
        <div id="notifDropdown"
             class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden animate-fade-in z-50">
          <?php if (empty($notifications)): ?>
            <p class="px-4 py-3 text-sm text-gray-500 text-center">
              No notifications at the moment.
            </p>
          <?php else: ?>
            <div class="max-h-80 overflow-y-auto">
              <?php foreach ($notifications as $n): ?>
                <a href="index.php?action=viewNotification&id=<?= $n['notification_id'] ?>"
                   class="flex items-start gap-3 px-4 py-3 text-sm border-b last:border-b-0
                          <?= $n['is_read'] ? 'bg-white' : 'bg-indigo-50' ?> hover:bg-indigo-100 transition">

                  <img src="uploads/<?= $n['actor_profile_picture'] ?? 'default_avatar.png' ?>"
                       class="w-9 h-9 rounded-full object-cover border" alt="profile">

                  <div class="flex-1">
                    <p class="text-gray-800">
                      <span class="font-semibold">@<?= htmlspecialchars($n['actor_username']) ?></span>
                      <?php if ($n['type'] === 'like'): ?>
                        liked your post
                      <?php elseif ($n['type'] === 'comment'): ?>
                        commented on your post
                      <?php elseif ($n['type'] === 'reply'): ?>
                        replied to your comment
                      <?php elseif ($n['type'] === 'follow'): ?>
                        started following you
                      <?php endif; ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      <?= date("M j, H:i", strtotime($n['created_at'])) ?>
                    </p>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="relative">
        <img 
          src="<?= $profilePic ?>" 
          alt="Profile" 
          id="profileBtn"
          class="w-10 h-10 rounded-full border-2 border-indigo-400 cursor-pointer object-cover hover:scale-105 transition-transform duration-200"
        >
        <div 
          id="dropdownMenu" 
          class="hidden absolute right-0 mt-3 w-44 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden animate-fade-in"
        >
          <a href="index.php?action=profile" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-500 transition duration-150">My Profile</a>
          <a href="index.php?action=logout" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-500 transition duration-150">Logout</a>
        </div>
      </div>
    <?php else: ?>
      <button onclick="window.location='index.php?action=login'" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white font-medium py-2 px-4 rounded-md hover:from-indigo-500 hover:to-purple-500 transition-all duration-200">
        🔐 Login / Register
      </button>
    <?php endif; ?>
  </div>
</header>

<script>
const profileBtn = document.getElementById("profileBtn");
const dropdown = document.getElementById("dropdownMenu");
const notifBtn = document.getElementById("notifBtn");
const notifDropdown = document.getElementById("notifDropdown");

// Profile dropdown
if (profileBtn && dropdown) {
  profileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.toggle("hidden");
  });
}

// Notifications dropdown
if (notifBtn && notifDropdown) {
  notifBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle("hidden");
  });
}

// Close both when clicking outside
window.addEventListener("click", (e) => {
  if (profileBtn && dropdown && !profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.add("hidden");
  }
  if (notifBtn && notifDropdown && !notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
    notifDropdown.classList.add("hidden");
  }
});
</script>

<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>
