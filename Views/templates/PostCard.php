

<?php
// Views/templates/PostCard.php
// Expects: $post, $user (if logged in), $isLoggedIn
?>

<div class="post inline-block w-full mb-6 bg-white rounded-xl shadow-md overflow-hidden break-inside-avoid relative transition transform hover:-translate-y-1 hover:shadow-lg">

    <?php if (!empty($post['image_url'])): ?>
        <img src="<?= htmlspecialchars($post['image_url']) ?>"
             alt="Post image"
             class="w-full max-h-[400px] object-cover object-center cursor-pointer transition-transform duration-300 hover:scale-[1.03] rounded-t-xl">
    <?php endif; ?>

    <div class="p-4">

        <div class="flex items-center justify-between mb-3 relative">

            <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'assets/default-avatar.png') ?>"
                     alt="profile"
                     class="w-8 h-8 rounded-full object-cover border border-indigo-200">
                <div>
                    <p class="text-sm font-semibold">

                        <?php if ($isLoggedIn): ?>
                            <a href="index.php?action=profile&user_id=<?= htmlspecialchars($post['user_id']) ?>"
                               class="text-gray-800 hover:text-indigo-600 hover:underline transition">
                                <?= htmlspecialchars($post['username']) ?>
                            </a>
                        <?php else: ?>
                            <span class="guest-profile text-gray-800 hover:text-indigo-600 hover:underline cursor-pointer transition">
                                <?= htmlspecialchars($post['username']) ?>
                            </span>
                        <?php endif; ?>

                    </p>

                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                        <span title="<?= $post['is_public'] ? 'Public' : 'Private' ?>" class="text-gray-400">
                            <?= $post['is_public'] ? '🌍' : '👥' ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (!empty($user) && isset($user['user_id']) && $user['user_id'] === $post['user_id']): ?>
            <?php endif; ?>

        </div>

        <?php if (!empty($post['title'])): ?>
            <h3 class="font-semibold text-indigo-600 text-lg mb-1 break-words">
                <?= htmlspecialchars($post['title']) ?>
            </h3>
        <?php endif; ?>

        <?php if (!empty($post['description'])): ?>
            <p class="text-gray-700 text-sm mb-2 break-words">
                <?= htmlspecialchars($post['description']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($post['tags'])): ?>
            <p class="text-xs text-indigo-400 italic">
                #<?= htmlspecialchars(str_replace(',', ' #', $post['tags'])) ?>
            </p>
        <?php endif; ?>

    </div>

    <div class="absolute bottom-3 right-3 bg-black/50 text-white rounded-full px-3 py-1 text-sm flex items-center gap-3">
        <?php if ($isLoggedIn): ?>
            <span class="like-btn cursor-pointer transition"
                  data-id="<?= $post['post_id'] ?>"
                  style="<?= !empty($post['liked']) ? 'color:#f87171;' : '' ?>">
                  ❤️ <?= $post['likes'] ?>
            </span>

            <span class="comment-btn cursor-pointer"
                  data-id="<?= $post['post_id'] ?>">
                  💬 <?= $post['comment_count'] ?? count($post['comments'] ?? []) ?>
            </span>

        <?php else: ?>
            <span class="guest-like cursor-pointer transition opacity-80 hover:opacity-100">
                ❤️ <?= $post['likes'] ?>
            </span>

            <span class="guest-comment cursor-pointer opacity-80 hover:opacity-100">
                💬 <?= $post['comment_count'] ?? count($post['comments'] ?? []) ?>
            </span>
        <?php endif; ?>
    </div>

</div>