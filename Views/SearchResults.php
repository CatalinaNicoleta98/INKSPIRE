<?php
// Variables expected from controller:
// $query, $activeType, $users, $posts
?>

<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<?php include __DIR__ . '/layout/Rightbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
</head>

<body class="bg-gradient-to-b from-indigo-50 to-white min-h-screen">

<div class="flex justify-center items-start w-full lg:pr-[250px] md:px-[200px] sm:px-4 box-border pt-[70px]">

    <!-- Main Feed -->
    <main class="feed w-full max-w-[900px] mx-auto px-2 space-y-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Search results for "<?= htmlspecialchars($query) ?>"
        </h1>

        <!-- FILTER BUTTONS -->
        <div class="flex gap-3 mb-4">

            <a href="index.php?action=search&type=profiles&q=<?= urlencode($query) ?>"
               class="px-4 py-2 rounded-md text-sm font-semibold 
               <?= $activeType === 'profiles' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                Profiles
            </a>

            <a href="index.php?action=search&type=tags&q=<?= urlencode($query) ?>"
               class="px-4 py-2 rounded-md text-sm font-semibold 
               <?= $activeType === 'tags' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                Tags
            </a>
        </div>

        <!-- RESULTS SECTION -->
        <?php if ($activeType === 'profiles'): ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Profiles</h2>

            <?php if (empty($users)): ?>
                <p class="text-gray-500">No profiles match your search.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($users as $u): ?>
                        <a href="index.php?action=profile&user_id=<?= $u['user_id'] ?>"
                           class="flex items-center bg-white p-3 rounded-lg shadow hover:shadow-md transition">
                            <img src="<?= htmlspecialchars($u['profile_picture'] ?? 'uploads/default.png') ?>"
                                 class="w-12 h-12 rounded-full object-cover mr-4">
                            <div>
                                <p class="font-semibold text-gray-800">@<?= htmlspecialchars($u['username']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($activeType === 'tags'): ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Posts</h2>

            <?php if (empty($posts)): ?>
                <p class="text-gray-500">No posts match your search.</p>
            <?php else: ?>
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6">
                    <?php foreach ($posts as $post): ?>
                        <?php include __DIR__ . '/templates/PostCard.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<!-- Comments Modal -->
<div id="commentsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg relative">
      <span id="closeComments" class="absolute top-3 right-4 text-gray-500 cursor-pointer text-2xl">&times;</span>
      <h3 class="text-xl font-semibold text-indigo-500 mb-4 text-center">Comments</h3>

      <!-- Modal Comments Layout -->
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
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
              </svg>
            </button>
          </div>
        </div>

      </div>

    </div>
</div>

<!-- External Search Results JS -->
<script src="scripts/search_results.js"></script>
<script src="scripts/like.js"></script>
<script src="scripts/explore_comments.js"></script>

</body>
</html>