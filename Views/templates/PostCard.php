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
            <?php $liked = !empty($post['liked']); ?>
            <span class="cursor-pointer transition"
                  data-action="like"
                  data-post-id="<?= htmlspecialchars($post['post_id']) ?>"
                  style="<?= $liked ? 'color:#ef4444;' : '#9ca3af;' ?>">
                <?= $liked ? '❤️' : '🤍' ?> <?= $post['likes'] ?>
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

<!-- Comments Modal for this post -->
<div id="comments-modal-<?= htmlspecialchars($post['post_id']) ?>"
     class="comments-modal hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[2000]"
     data-post-id="<?= htmlspecialchars($post['post_id']) ?>">

    <div class="bg-white rounded-lg w-[400px] max-h-[80vh] overflow-y-auto p-6 shadow-lg relative">
        <span class="close-comments absolute top-3 right-4 text-gray-500 cursor-pointer text-2xl"
              data-post-id="<?= htmlspecialchars($post['post_id']) ?>">&times;</span>

        <h3 class="text-xl font-semibold text-indigo-500 mb-4 text-center">Comments</h3>

        <div class="flex flex-col h-[70vh]" data-context="modal">

            <div class="comments-list flex-1 overflow-y-auto text-gray-600 text-sm p-1"
                 id="comments-list-<?= htmlspecialchars($post['post_id']) ?>">
                <p class="text-center text-gray-400 italic">Loading...</p>
            </div>

            <div class="bg-white border-t border-indigo-100 p-2 sticky bottom-0">
                <form class="comment-form flex items-center gap-2"
                      data-post-id="<?= htmlspecialchars($post['post_id']) ?>">

                    <input type="hidden" name="post_id"
                           value="<?= htmlspecialchars($post['post_id']) ?>">

                    <input type="text" name="comment"
                           placeholder="Add a comment..."
                           class="flex-1 border border-indigo-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none"
                           required>

                    <button type="submit"
                            class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white rounded-full p-2 hover:from-indigo-500 hover:to-purple-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10l9-6 9 6-9 6-9-6zM3 10v10l9-6 9 6V10" />
                        </svg>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>