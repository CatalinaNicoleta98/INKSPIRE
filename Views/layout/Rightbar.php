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
  }, 1000);
});
</script>