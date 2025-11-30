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
<div class="hidden md:flex flex-col fixed top-[70px] left-0 w-[220px] h-[calc(100%-70px)] bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 border-r border-purple-100 shadow-md p-4 z-20 sidebar">
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
      <button 
  id="createPostBtn" 
  data-token="<?= $_SESSION['post_token'] ?>"
  class="mt-auto bg-gradient-to-r from-indigo-400 to-purple-400 text-white font-medium py-2 px-3 rounded-md hover:from-indigo-500 hover:to-purple-500 transition-all duration-200"
>
  ➕ Create Post
</button>
    <?php endif; ?>

  <?php else: ?>

    <div class="mt-4 p-5 rounded-2xl shadow-lg bg-gradient-to-br from-purple-50 via-indigo-50 to-pink-50 border border-indigo-100 relative overflow-hidden">
      <div class="absolute -top-3 -right-3 w-16 h-16 bg-indigo-200 rounded-full opacity-30 blur-xl"></div>
      <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-purple-200 rounded-full opacity-20 blur-xl"></div>

      <div class="relative z-10">
        <h4 class="text-sm font-semibold text-indigo-700 tracking-wide drop-shadow-sm mb-3">Welcome to Inkspire</h4>
        <p class="text-xs text-gray-700 leading-relaxed mb-3">
          <?= nl2br(htmlspecialchars($aboutText)) ?>
        </p>

        <button onclick="window.location='index.php?action=register'"
          class="w-full py-2 text-xs font-semibold rounded-lg bg-gradient-to-r from-indigo-400 to-purple-400 text-white shadow-sm hover:from-indigo-500 hover:to-purple-500 transition-all">
          ✨ Join the Community
        </button>
        <button onclick="window.location='index.php?action=login'"
          class="w-full mt-3 py-2 text-xs font-semibold rounded-lg bg-white text-indigo-600 border border-indigo-300 shadow-sm hover:bg-indigo-50 transition-all">
          🔑 Already have an account? Log in
        </button>
      </div>
    </div>

  <?php endif; ?>
</div>

<!-- External JS -->
<script src="scripts/sidebar.js?v=1.0"></script>