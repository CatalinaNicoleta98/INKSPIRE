<?php $user = Session::get('user'); ?>
<?php $isLoggedIn = !empty($user) && isset($user['user_id']); ?>
<?php include __DIR__ . '/layout/Header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Explore</title>
</head>

<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen pt-[70px]">

  <div class="flex justify-center items-center w-full lg:pr-[250px] md:px-[200px] sm:px-4 box-border">

    <main class="feed w-full max-w-[900px] mx-auto px-2 space-y-6">
      <?php if (!empty($posts)): ?>
        <div class="columns-3 md:columns-2 sm:columns-1 gap-6 [column-fill:_balance]">
          <?php foreach ($posts as $post): ?>
              <?php include __DIR__ . '/templates/PostCard.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-500 italic mt-10">No posts yet. Be the first to create one!</p>
      <?php endif; ?>
    </main>

    <?php include __DIR__ . '/layout/Sidebar.php'; ?>
    <?php include __DIR__ . '/layout/Rightbar.php'; ?>

  </div>


  <!-- Lightbox -->
  <div id="lightbox" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-[1000]">
    <img id="lightboxImg" src="" alt="Full image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-lg">
  </div>


  <!-- Comments Modal -->
  <div id="commentsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg relative">
      <span id="closeComments" class="absolute top-3 right-4 text-gray-500 cursor-pointer text-2xl">&times;</span>
      <h3 class="text-xl font-semibold text-indigo-500 mb-4 text-center">Comments</h3>

      <div class="flex flex-col h-[70vh]" data-context="modal">
        <div id="commentsList" class="comments-list flex-1 overflow-y-auto text-gray-600 text-sm p-1">
          <p class="text-center text-gray-400 italic">Loading...</p>
        </div>

        <div class="bg-white border-t border-indigo-100 p-2 sticky bottom-0">
          <div class="flex items-center gap-2">
            <input id="newCommentInput" type="text" placeholder="Add a comment..."
                  class="comment-input flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none">

            <button id="submitComment"
                    class="comment-submit bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                   class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
              </svg>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- Guest Login/Register Modal -->
  <div id="guestModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[2000]">
    <div class="bg-white rounded-lg p-8 text-center shadow-lg max-w-sm w-full">
      <h3 class="text-lg font-semibold text-indigo-600 mb-3">Login Required</h3>
      <p class="text-gray-600 mb-6">Please log in or register to like posts, comment, or view user profiles.</p>
      <div class="flex justify-center gap-4">
        <button onclick="window.location='index.php?action=login'"
                class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white px-4 py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition-all">
          Login / Register
        </button>
        <button id="closeGuestModal"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-all">
          Cancel
        </button>
      </div>
    </div>
  </div>


  <!-- Required global JS value -->
  <script>
    window.IS_ADMIN_VIEW = <?= (!empty($_SESSION['admin_view']) && !empty($user['is_admin'])) ? 'true' : 'false' ?>;
  </script>

  <!-- External JS Files -->
  <script src="scripts/explore.js?v=1.0"></script>
  <script src="scripts/explore_comments.js?v=1.0"></script>
   <script src="scripts/like.js"></script>     
</body>
</html>