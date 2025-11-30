<?php
  $isLoggedIn = Session::isLoggedIn();
?>
<div class="hidden lg:block fixed top-[70px] right-0 w-[280px] h-[calc(100%-70px)] bg-gradient-to-b from-purple-50 to-pink-50 border-l border-purple-100 p-6 shadow-inner overflow-y-auto z-10 box-border">
  <h3 class="text-lg font-semibold text-indigo-500 mb-4">Search</h3>

  <form method="GET" action="index.php" class="mb-6">
    <input type="hidden" name="action" value="search">
    <input 
      type="text" 
      name="q" 
      autocomplete="off"
      placeholder="Search posts or users..." 
      class="w-full px-3 py-2 border border-indigo-200 rounded-md text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none placeholder-gray-400"
    >
  </form>

  <div id="searchSuggestions" class="hidden bg-white border border-indigo-100 rounded-md shadow-md text-sm p-2 space-y-2 max-h-64 overflow-y-auto mt-1"></div>

  <hr class="my-5 border-indigo-100">

  <?php if ($isLoggedIn): ?>
    <h3 class="text-lg font-semibold text-indigo-500 mb-3">Suggested Accounts</h3>
    <div id="suggestedAccounts" class="space-y-3 text-sm text-gray-700">
      <p class="text-gray-400 text-sm">Loading suggestions...</p>
    </div>
  <?php endif; ?>

  <div class="mt-12 pt-5 border-t border-indigo-100 text-[10px] text-gray-500 text-center leading-relaxed">
    <p class="text-[11px] font-medium text-indigo-500">Inkspire · 2025</p>
    <p class="mt-1">
      Crafted with care by 
      <a href="https://www.linkedin.com/in/catalinavrinceanu/"
         target="_blank"
         class="text-indigo-600 font-semibold hover:underline">
         Catalina Vrinceanu
      </a>
    </p>
  </div>
</div>

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

<!-- External JS for Rightbar -->
<script src="scripts/rightbar.js?v=1.0"></script>