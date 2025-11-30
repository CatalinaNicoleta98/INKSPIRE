// ===============================
// Rightbar.js 
// ===============================

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------
    // SEARCH SUGGESTIONS
    // -------------------------------
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

                    html += `
                        <a href="index.php?action=search&q=${encodeURIComponent(q)}"
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
                                <a href="index.php?action=profile&user_id=${u.user_id}"
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
                                <a href="index.php?action=search&type=tags&q=${encodeURIComponent(t.tags)}"
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

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
                suggestionBox.classList.add('hidden');
            }
        });
    }

    // -------------------------------
    // SUGGESTED ACCOUNTS
    // -------------------------------
    const container = document.getElementById('suggestedAccounts');

    if (container) {
        fetch('index.php?action=getSuggestedUsers')
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';

                if (!data.users || data.users.length === 0) {
                    container.innerHTML = `<p class="text-gray-400 text-sm">No suggestions available.</p>`;
                    return;
                }

                data.users.forEach(user => {
                    if (user.is_blocked_between === true) return;

                    const profilePic = user.profile_picture || 'uploads/default.png';
                    const bio = user.bio || '';
                    const bioPreview =
                        bio.length > 40 ? bio.substring(0, 40) + '…' : bio;

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
    }

    // -------------------------------
    // GUEST MODAL
    // -------------------------------
    const guestModal = document.getElementById('guestModal');
    const closeGuestModal = document.getElementById('closeGuestModal');

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('guest-profile')) {
            guestModal.classList.remove('hidden');
        }
    });

    if (closeGuestModal) {
        closeGuestModal.addEventListener('click', () => {
            guestModal.classList.add('hidden');
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === guestModal) {
            guestModal.classList.add('hidden');
        }
    });
});