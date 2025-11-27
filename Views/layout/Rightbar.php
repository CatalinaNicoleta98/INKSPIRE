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
      autocomplete="off"
      placeholder="Search posts or users..." 
      class="w-full px-3 py-2 border border-indigo-200 rounded-md text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none placeholder-gray-400"
    >
  </form>
  <div id="searchSuggestions" class="hidden bg-white border border-indigo-100 rounded-md shadow-md text-sm p-2 space-y-2 max-h-64 overflow-y-auto mt-1"></div>

  <hr class="my-5 border-indigo-100">

<?php if ($isLoggedIn): ?>
  <h3 class="text-lg font-semibold text-indigo-500 mb-3">Suggested Accounts</h3>
<?php endif; ?>
<?php if ($isLoggedIn): ?>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Live search suggestions
    const searchInput = document.querySelector('input[name="q"]');
    const suggestionBox = document.getElementById('searchSuggestions');

    if (searchInput && suggestionBox) {
      searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim();

        if (!q) {
          suggestionBox.classList.add('hidden');
          suggestionBox.innerHTML = '';
          return;
        }

        fetch(`index.php?action=searchSuggestions&q=${encodeURIComponent(q)}`)
          .then(res => res.json())
          .then(data => {
            let html = '';

            // "Search for ..." item
            html += `
              <a href="index.php?action=search&amp;q=${encodeURIComponent(q)}"
                 class="block px-3 py-2 text-indigo-600 font-semibold hover:bg-indigo-50 rounded-md">
                Search for "${q}"
              </a>
              <hr class="my-2 border-indigo-100">
            `;

            let hasResults = false;

            if (data.users && data.users.length > 0) {
              hasResults = true;
              html += `<div class="text-xs font-semibold text-gray-500 px-2 mb-1">Users</div>`;
              data.users.forEach(u => {
                html += `
                  <a href="index.php?action=profile&amp;user_id=${u.user_id}"
                     class="block px-3 py-1 hover:bg-indigo-50 rounded-md cursor-pointer">
                    @${u.username}
                  </a>
                `;
              });
            }


            if (data.tags && data.tags.length > 0) {
              hasResults = true;
              html += `<div class="text-xs font-semibold text-gray-500 px-2 mt-2 mb-1">Tags</div>`;
              data.tags.forEach(t => {
                html += `
                  <a href="index.php?action=search&amp;type=tags&amp;q=${encodeURIComponent(t.tags)}"
                     class="block px-3 py-1 hover:bg-indigo-50 rounded-md cursor-pointer">
                    #${t.tags}
                  </a>
                `;
              });
            }

            if (!hasResults) {
              html += `<div class="px-3 py-2 text-gray-400 text-sm">No results</div>`;
            }

            suggestionBox.innerHTML = html;
            suggestionBox.classList.remove('hidden');
          })
          .catch(() => {
            suggestionBox.innerHTML = `<div class="px-3 py-2 text-red-500 text-sm">Search failed.</div>`;
            suggestionBox.classList.remove('hidden');
          });
      });

      // Hide suggestions when clicking outside
      document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
          suggestionBox.classList.add('hidden');
        }
      });
    }

    const container = document.getElementById('suggestedAccounts');

    <?php if ($isLoggedIn): ?>
    fetch('index.php?action=getSuggestedUsers')
      .then(response => response.json())
      .then(data => {
          container.innerHTML = '';

          if (!data.users || data.users.length === 0) {
              container.innerHTML = `<p class="text-gray-400 text-sm">No suggestions available.</p>`;
              return;
          }

          data.users.forEach(user => {
              // Skip users who are blocked by current user or who blocked current user
              if (user.is_blocked_between === true) {
                  return;
              }

              const profilePic = user.profile_picture || 'uploads/default.png';
              const bio = user.bio || '';
              const bioPreview = bio.length > 0 
                  ? (bio.length > 40 ? bio.substring(0, 40) + '…' : bio) 
                  : '';

              container.innerHTML += `
                  <a href="index.php?action=profile&user_id=${user.user_id}" 
                     class="flex items-center bg-white rounded-lg p-2 shadow-sm hover:shadow-md hover:bg-indigo-50 transition duration-200 cursor-pointer group">
                      <img src="${profilePic}" class="w-10 h-10 rounded-full object-cover mr-3 border border-indigo-100 group-hover:border-indigo-300">
                      <div class="flex flex-col">
                        <span class="font-semibold text-gray-800 group-hover:text-indigo-600">@${user.username}</span>
                        ${bioPreview ? `<span class="text-xs text-gray-500 mt-0.5">${bioPreview}</span>` : ''}
                      </div>
                  </a>
              `;
          });
      })
      .catch(err => {
          console.error(err);
          container.innerHTML = `<p class="text-red-500 text-sm">Failed to load suggestions.</p>`;
      });

    <?php else: ?>
    container.innerHTML = ``;
    <?php endif; ?>
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
  if (e.target.classList.contains('guest-profile')) {
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