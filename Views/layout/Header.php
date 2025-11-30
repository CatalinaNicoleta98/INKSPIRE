<?php
Session::start();
$database = new Database();
$db = $database->connect();
$loggedInUser = Session::get('user');

if ($loggedInUser) {
    $profileModel = new ProfileModel($db);
    $loggedProfile = $profileModel->getProfileByUserId($loggedInUser['user_id']);
    if ($loggedProfile) {
        $loggedInUser['profile_picture'] = $loggedProfile['profile_picture'];
    }
}

$profilePic = !empty($loggedInUser['profile_picture'])
    ? htmlspecialchars($loggedInUser['profile_picture'])
    : 'uploads/default_avatar.png';
?>
<script src="https://cdn.tailwindcss.com"></script>

<header class="fixed top-0 left-0 w-full bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 shadow-sm z-50">
  <!-- Mobile Header (lg:hidden) -->
  <div class="lg:hidden flex items-center justify-between px-4 py-3">
    <!-- Logo centered -->
    <a href="index.php?action=home" class="flex-1 flex justify-center">
      <img src="uploads/logo.png" alt="Inkspire Logo" class="h-10 w-auto object-contain">
    </a>

    <!-- Mobile profile dropdown -->
    <?php if (!empty($loggedInUser)): ?>
      <div class="absolute right-4 flex items-center">

        <!-- Profile Button -->
        <img 
          src="<?= $profilePic ?>" 
          alt="Profile" 
          id="mobileProfileBtn"
          class="w-9 h-9 rounded-full border-2 border-indigo-400 cursor-pointer object-cover"
        >

        <!-- Dropdown -->
        <div 
          id="mobileDropdownMenu" 
          class="hidden absolute right-2 top-14 w-48 bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden transform translate-y-2 opacity-0 transition-all duration-200 ease-out z-50"
        >
          <a href="index.php?action=profile" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">My Profile</a>
          <a href="index.php?action=logout" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Logout</a>
        </div>

      </div>
    <?php endif; ?>
  </div>
  <div class="max-w-7xl mx-auto hidden lg:flex items-center justify-between px-6 py-3">
    <div class="flex items-center space-x-3">
      <a href="index.php?action=home" class="flex items-center cursor-pointer">
        <img src="uploads/logo.png" alt="Inkspire Logo" class="h-10 w-auto object-contain hover:opacity-90 transition duration-200">
      </a>
    </div>

    <?php if ($loggedInUser): ?>
      <?php
          $notifModel = new NotificationModel($db);
          if ($loggedInUser) {
              $unread = $notifModel->getUnreadCount($loggedInUser['user_id']);
              $notifications = $notifModel->getNotificationsByUser($loggedInUser['user_id']);
          }

          $unreadNotifications = [];
          $readNotifications = [];
          if (!empty($notifications)) {
              foreach ($notifications as $n) {
                  if (empty($n['is_read'])) {
                      $unreadNotifications[] = $n;
                  } else {
                      $readNotifications[] = $n;
                  }
              }
          }
      ?>

      <div class="flex items-center gap-4">
        <div class="relative">
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
              <!-- Header with bulk actions -->
              <div class="flex items-center justify-between px-4 py-2 border-b bg-gray-50">
                <span class="text-sm font-semibold text-gray-700">Notifications</span>
                <div class="flex items-center gap-2 text-xs">
                  <button class="js-mark-all-read text-indigo-600 hover:underline">Mark all read</button>
                  <span class="text-gray-300">|</span>
                  <button class="js-clear-all text-red-500 hover:underline">Clear all</button>
                </div>
              </div>

              <div class="max-h-80 overflow-y-auto">
                <?php if (!empty($unreadNotifications)): ?>
                  <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wide">Unread</p>
                  <?php foreach ($unreadNotifications as $n): ?>
                    <div class="flex items-start gap-3 px-4 py-3 text-sm border-b last:border-b-0 bg-indigo-50 hover:bg-indigo-100 transition">
                      <?php
                          $actorPic = !empty($n['actor_profile_picture'])
                              ? htmlspecialchars($n['actor_profile_picture'])
                              : 'uploads/default_avatar.png';
                      ?>
                      <img src="<?= $actorPic ?>"
                           class="w-9 h-9 rounded-full object-cover border" alt="profile">

                      <div class="flex-1">
                        <a href="index.php?action=viewNotification&id=<?= $n['notification_id'] ?>" class="block text-gray-800">
                          <p>
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
                        </a>
                        <p class="text-xs text-gray-400 mt-1">
                          <?= date("M j, H:i", strtotime($n['created_at'])) ?>
                        </p>
                      </div>

                      <div class="flex flex-col items-end gap-1 ml-2">
                        <button class="js-mark-read text-[10px] text-indigo-600 hover:underline" data-id="<?= $n['notification_id'] ?>">Mark read</button>
                        <button class="js-delete text-[10px] text-red-500 hover:underline" data-id="<?= $n['notification_id'] ?>">Delete</button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($readNotifications)): ?>
                  <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wide">Earlier</p>
                  <?php foreach ($readNotifications as $n): ?>
                    <div class="flex items-start gap-3 px-4 py-3 text-sm border-b last:border-b-0 bg-white hover:bg-indigo-50 transition">
                      <?php
                          $actorPic = !empty($n['actor_profile_picture'])
                              ? htmlspecialchars($n['actor_profile_picture'])
                              : 'uploads/default_avatar.png';
                      ?>
                      <img src="<?= $actorPic ?>"
                           class="w-9 h-9 rounded-full object-cover border" alt="profile">

                      <div class="flex-1">
                        <a href="index.php?action=viewNotification&id=<?= $n['notification_id'] ?>" class="block text-gray-800">
                          <p>
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
                        </a>
                        <p class="text-xs text-gray-400 mt-1">
                          <?= date("M j, H:i", strtotime($n['created_at'])) ?>
                        </p>
                      </div>

                      <div class="flex flex-col items-end gap-1 ml-2">
                        <button class="js-mark-unread text-[10px] text-indigo-600 hover:underline" data-id="<?= $n['notification_id'] ?>">Mark unread</button>
                        <button class="js-delete text-[10px] text-red-500 hover:underline" data-id="<?= $n['notification_id'] ?>">Delete</button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
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
      </div>
    <?php else: ?>
      <!-- Guest: no header button -->
    <?php endif; ?>
  </div>
</header>

<!-- ADD THIS new external JS -->
<script src="scripts/header.js?v=1.0"></script>

<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>