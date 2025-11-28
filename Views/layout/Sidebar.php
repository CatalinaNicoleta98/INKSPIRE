<?php

  $database = new Database();
  $db = $database->connect();
  $aboutModel = new AboutModel($db);
  $aboutText = $aboutModel->getAbout();

  $isLoggedIn = Session::isLoggedIn();
  $loggedInUser = $_SESSION['user'] ?? null;
  $isAdmin = $loggedInUser && !empty($loggedInUser['is_admin']);
  $adminViewOn = !empty($_SESSION['admin_view']);
  if (!isset($_SESSION['post_token'])) {
      $_SESSION['post_token'] = bin2hex(random_bytes(32));
  }
?>
<div class="hidden md:flex flex-col fixed top-[70px] left-0 w-[220px] h-[calc(100%-70px)] bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 border-r border-purple-100 shadow-md p-4 z-20">
  <h3 class="text-lg font-semibold text-indigo-500 mb-4">Navigation</h3>
  <?php if ($isLoggedIn): ?>
    <button onclick="window.location='index.php?action=home'" class="w-full text-left py-2 px-3 mb-2 rounded-md bg-indigo-200 text-indigo-800 hover:bg-indigo-300 transition">🏠 Home</button>
    <button onclick="window.location='index.php?action=explore'" class="w-full text-left py-2 px-3 mb-2 rounded-md bg-indigo-200 text-indigo-800 hover:bg-indigo-300 transition">🔥 Explore</button>
    <button onclick="window.location='index.php?action=profile'" class="w-full text-left py-2 px-3 mb-2 rounded-md bg-indigo-200 text-indigo-800 hover:bg-indigo-300 transition">👤 Profile</button>
    <button onclick="window.location='index.php?action=settings'" class="w-full text-left py-2 px-3 mb-2 rounded-md bg-indigo-200 text-indigo-800 hover:bg-indigo-300 transition">⚙️ Settings</button>
    <button onclick="window.location='index.php?action=logout'" class="w-full text-left py-2 px-3 mb-2 rounded-md bg-indigo-200 text-indigo-800 hover:bg-indigo-300 transition">🚪 Logout</button>

    <?php if ($isAdmin): ?>
      <div class="mt-4 mb-2 p-3 rounded-md bg-purple-100 text-purple-800 text-sm">
        <?php if ($adminViewOn): ?>
          <p class="font-semibold mb-2 flex items-center gap-1">
            👑 <span>Hello admin</span>
          </p>
          <button onclick="window.location='index.php?action=toggleAdminView&amp;mode=off'"
                  class="w-full mb-1 py-1.5 px-2 rounded-md border border-purple-300 text-purple-700 hover:bg-purple-200 transition text-xs">
            Disable admin view
          </button>
          <button onclick="window.location='index.php?action=adminPanel'"
                  class="w-full py-1.5 px-2 rounded-md bg-purple-500 text-white hover:bg-purple-600 transition text-xs">
            Open admin panel
          </button>
        <?php else: ?>
          <p class="font-semibold mb-2 flex items-center gap-1">
            👑 <span>Admin view is off</span>
          </p>
          <button onclick="window.location='index.php?action=toggleAdminView&amp;mode=on'"
                  class="w-full py-1.5 px-2 rounded-md border border-purple-300 text-purple-700 hover:bg-purple-200 transition text-xs">
            Enable admin view
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($loggedInUser) && isset($loggedInUser['is_active']) && (int)$loggedInUser['is_active'] === 0): ?>
      <button 
        onclick="alert('You cannot create a post because your account is blocked.'); return false;" 
        class="mt-auto bg-gray-300 text-gray-500 font-medium py-2 px-3 rounded-md cursor-not-allowed">
        ➕ Create Post
      </button>
    <?php else: ?>
      <button id="createPostBtn" class="mt-auto bg-gradient-to-r from-indigo-400 to-purple-400 text-white font-medium py-2 px-3 rounded-md hover:from-indigo-500 hover:to-purple-500 transition-all duration-200">➕ Create Post</button>
    <?php endif; ?>
  <?php else: ?>
    <div class="mt-4 p-5 rounded-2xl shadow-lg bg-gradient-to-br from-purple-50 via-indigo-50 to-pink-50 border border-indigo-100 relative overflow-hidden">
      <div class="absolute -top-3 -right-3 w-16 h-16 bg-indigo-200 rounded-full opacity-30 blur-xl"></div>
      <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-purple-200 rounded-full opacity-20 blur-xl"></div>

      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-3">
          <h4 class="text-sm font-semibold text-indigo-700 tracking-wide drop-shadow-sm">Welcome to Inkspire</h4>
        </div>

        <p class="text-xs text-gray-700 leading-relaxed mb-3">
            <?= nl2br(htmlspecialchars($aboutText)) ?>
        </p>

        <button onclick="window.location='index.php?action=login'"
          class="w-full py-2 text-xs font-semibold rounded-lg bg-gradient-to-r from-indigo-400 to-purple-400 text-white shadow-sm hover:from-indigo-500 hover:to-purple-500 transition-all">
          ✨ Join the Community
        </button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
  const currentUrl = window.location.href;
  document.querySelectorAll('.sidebar button').forEach(btn => {
    if (currentUrl.includes(btn.getAttribute('onclick').match(/'(.*?)'/)[1])) {
      btn.classList.add('bg-indigo-400', 'text-white');
    }
  });
</script>

<script>
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
              <input type="hidden" name="token" value="<?= $_SESSION['post_token'] ?>">
              <input type="text" name="title" placeholder="Title" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
              <textarea name="description" placeholder="Description" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"></textarea>
              <input type="text" name="tags" placeholder="Tags (comma-separated)" class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
              <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600">
              <p id="postError" class="hidden text-red-600 text-sm mt-2"></p>
              <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-md py-2 hover:from-indigo-500 hover:to-purple-500 transition">Post</button>
            </form>
          </div>
        `;
        document.body.appendChild(modal);
      }

      modal.style.display = 'flex';
      const closeBtn = modal.querySelector('#closeModal');
      if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
      window.onclick = function(e) {
        if (e.target === modal) modal.style.display = 'none';
      };
    });
  }
</script>

<script>
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
        errorBox.textContent = data.error || '⚠️ An error occurred while creating your post.';
        errorBox.classList.remove('hidden');
      } else {
        // Close modal and optionally refresh page or add post dynamically
        document.getElementById('postModal').style.display = 'none';
        location.reload();
      }
    } catch (err) {
      errorBox.textContent = '⚠️ Failed to connect to the server.';
      errorBox.classList.remove('hidden');
    }
  }
});
</script>