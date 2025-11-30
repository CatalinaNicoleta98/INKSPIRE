// ========================================
// sidebar.js — extracted sidebar functions
// ========================================

// Highlight active navigation button
const currentUrl = window.location.href;
document.querySelectorAll('.sidebar button').forEach(btn => {
    try {
        const onclickValue = btn.getAttribute('onclick');
        if (!onclickValue) return;

        const match = onclickValue.match(/'([^']+)'/);
        if (match && currentUrl.includes(match[1])) {
            btn.classList.add('bg-indigo-400', 'text-white');
        }
    } catch (err) {
        console.error('Sidebar highlight error:', err);
    }
});

// --------------------------------------------
// "Create Post" modal open/close
// --------------------------------------------
const createPostBtn = document.getElementById('createPostBtn');

if (createPostBtn) {
    createPostBtn.addEventListener('click', () => {

        let modal = document.getElementById('postModal');

        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'postModal';
            modal.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50';

            modal.innerHTML = `
                <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg">
                    <span id="closeModal" class="float-right text-gray-500 cursor-pointer text-2xl">&times;</span>
                    <h3 class="text-xl font-semibold text-indigo-500 mb-4">Create New Post</h3>

                    <form id="createPostForm" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="token" value="${createPostBtn.dataset.token}">
                        <input type="text" name="title" placeholder="Title" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                        <textarea name="description" placeholder="Description" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"></textarea>
                        <input type="text" name="tags" placeholder="Tags (comma-separated)" class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600">
                        
                        <p id="postError" class="hidden text-red-600 text-sm mt-2"></p>

                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-md py-2 hover:from-indigo-500 hover:to-purple-500 transition">
                            Post
                        </button>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);
        }

        modal.style.display = 'flex';

        const closeBtn = modal.querySelector('#closeModal');
        if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');

        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });
    });
}

// --------------------------------------------
// Create Post Submission
// --------------------------------------------
document.addEventListener('submit', async (e) => {
    if (e.target && e.target.id === 'createPostForm') {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const errorBox = document.getElementById('postError');
        errorBox.classList.add('hidden');
        errorBox.textContent = '';

        try {
            const res = await fetch('index.php?action=createPost', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (!data.success) {
                errorBox.textContent = data.error || '⚠️ Error creating post.';
                errorBox.classList.remove('hidden');
            } else {
                document.getElementById('postModal').style.display = 'none';
                location.reload();
            }
        } catch (err) {
            errorBox.textContent = '⚠️ Failed to connect to server.';
            errorBox.classList.remove('hidden');
        }
    }
});