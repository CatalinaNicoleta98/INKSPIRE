<?php
  require_once __DIR__ . '/../../helpers/Session.php';
  $isLoggedIn = Session::isLoggedIn();
?>
<div class="hidden lg:block fixed top-[70px] right-0 w-[280px] h-[calc(100%-70px)] bg-gradient-to-b from-purple-50 to-pink-50 border-l border-purple-100 p-6 shadow-inner overflow-y-auto z-10 box-border">
  <h3 class="text-lg font-semibold text-indigo-500 mb-4">Search</h3>
  <form method="GET" action="index.php" class="mb-6">
    <input type="hidden" name="action" value="search">
    <input 
      type="text" 
      name="q" 
      placeholder="Search posts or users..." 
      class="w-full px-3 py-2 border border-indigo-200 rounded-md text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none placeholder-gray-400"
    >
  </form>

  <hr class="my-5 border-indigo-100">

  <h3 class="text-lg font-semibold text-indigo-500 mb-3">Suggested Accounts</h3>
  <div id="suggestedAccounts" class="space-y-3 text-sm text-gray-700">
    <p class="text-gray-400 text-sm">Loading suggestions...</p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('suggestedAccounts');
  setTimeout(() => {
    <?php if ($isLoggedIn): ?>
    container.innerHTML = `
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="text-gray-700">@artlover</strong>
        <button class="bg-indigo-400 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-500 transition">Follow</button>
      </div>
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="text-gray-700">@photoqueen</strong>
        <button class="bg-indigo-400 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-500 transition">Follow</button>
      </div>
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="text-gray-700">@digitaldreams</strong>
        <button class="bg-indigo-400 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-500 transition">Follow</button>
      </div>
    `;
    <?php else: ?>
    container.innerHTML = `
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="guest-profile text-gray-700 cursor-pointer hover:text-indigo-600">@artlover</strong>
        <button class="guest-follow bg-indigo-300 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-400 transition opacity-80">Follow</button>
      </div>
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="guest-profile text-gray-700 cursor-pointer hover:text-indigo-600">@photoqueen</strong>
        <button class="guest-follow bg-indigo-300 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-400 transition opacity-80">Follow</button>
      </div>
      <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition duration-200">
        <strong class="guest-profile text-gray-700 cursor-pointer hover:text-indigo-600">@digitaldreams</strong>
        <button class="guest-follow bg-indigo-300 text-white text-xs px-3 py-1 rounded-full hover:bg-indigo-400 transition opacity-80">Follow</button>
      </div>
    `;
    <?php endif; ?>
  }, 1000);
});
</script>

<!-- Guest Login/Register Modal -->
<div id="guestModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[2000]">
  <div class="bg-white rounded-lg p-8 text-center shadow-lg max-w-sm w-full">
    <h3 class="text-lg font-semibold text-indigo-600 mb-3">Login Required</h3>
    <p class="text-gray-600 mb-6">Please log in or register to follow or view user profiles.</p>
    <div class="flex justify-center gap-4">
      <button onclick="window.location='index.php?action=login'" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white px-4 py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition-all">Login / Register</button>
      <button id="closeGuestModal" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-all">Cancel</button>
    </div>
  </div>
</div>

<script>
const guestModal = document.getElementById('guestModal');
const closeGuestModal = document.getElementById('closeGuestModal');
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('guest-follow') || e.target.classList.contains('guest-profile')) {
    guestModal.classList.remove('hidden');
  }
});
if (closeGuestModal) {
  closeGuestModal.addEventListener('click', () => guestModal.classList.add('hidden'));
}
window.addEventListener('click', (e) => {
  if (e.target === guestModal) guestModal.classList.add('hidden');
});
</script>