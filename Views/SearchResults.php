<?php
// Variables expected from controller:
// $query, $activeType, $users, $posts, $tags
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
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="index.php?action=search&type=posts&q=<?= urlencode($query) ?>"
               class="px-4 py-2 rounded-md text-sm font-semibold 
               <?= $activeType === 'posts' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                Posts
            </a>

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
                            <img src="uploads/default.png" class="w-12 h-12 rounded-full object-cover mr-4">
                            <div>
                                <p class="font-semibold text-gray-800">@<?= htmlspecialchars($u['username']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($activeType === 'tags'): ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Tags</h2>

            <?php if (empty($tags)): ?>
                <p class="text-gray-500">No tags match your search.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tags as $t): ?>
                        <a href="index.php?action=explore&tag=<?= urlencode($t['tags']) ?>"
                           class="block bg-white p-3 rounded-lg shadow hover:shadow-md transition">
                            #<?= htmlspecialchars($t['tags']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <h2 class="text-xl font-semibold text-gray-700">Matching Posts</h2>

            <?php if (empty($posts)): ?>
                <p class="text-gray-500">No posts match your search.</p>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($posts as $p): ?>
                        <a href="index.php?action=explore&post_id=<?= $p['post_id'] ?>"
                           class="block bg-white p-4 rounded-lg shadow hover:shadow-md transition">
                            <p class="font-semibold text-gray-900 text-lg">
                                <?= htmlspecialchars($p['title']) ?>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

</body>
</html>