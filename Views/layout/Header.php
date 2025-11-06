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

<header class="fixed top-0 left-0 w-full bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 shadow-sm z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
    <div class="flex items-center space-x-3">
      <div class="text-2xl font-bold text-indigo-500 tracking-tight hover:text-indigo-600 transition duration-200 cursor-pointer">
        Inkspire
      </div>
    </div>

    <?php if ($loggedInUser): ?>
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
let dropdownOpen = false;
profileBtn.addEventListener("click", () => {
  dropdown.classList.toggle("hidden");
  dropdownOpen = !dropdownOpen;
});
window.addEventListener("click", (e) => {
  if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.add("hidden");
    dropdownOpen = false;
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
