<div class="max-w-lg mx-auto mt-10">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Notifications</h2>

    <?php if (empty($notifications)): ?>
        <p class="text-gray-500 text-center">No notifications yet.</p>

    <?php else: ?>

        <div class="flex flex-col gap-4">
            <?php foreach ($notifications as $n): ?>

                <a href="index.php?action=viewNotification&id=<?= $n['notification_id'] ?>"
                   class="block p-4 rounded-xl border 
                          <?= $n['is_read'] ? 'bg-white border-gray-200' : 'bg-indigo-50 border-indigo-200' ?>
                          hover:bg-indigo-100 transition">

                    <div class="flex items-center gap-3">

                        <!-- Actor avatar -->
                        <img src="uploads/<?= $n['actor_profile_picture'] ?? 'default_avatar.png' ?>"
                             class="w-12 h-12 rounded-full object-cover border" alt="profile">

                        <div class="flex-1">

                            <!-- Username + message -->
                            <p class="text-gray-800 text-sm">
                                <span class="font-semibold">@<?= htmlspecialchars($n['actor_username']) ?></span>

                                <?php if ($n['type'] === 'like'): ?>
                                    liked your post

                                <?php elseif ($n['type'] === 'comment'): ?>
                                    commented on your post

                                <?php elseif ($n['type'] === 'reply'): ?>
                                    replied to your comment

                                <?php elseif ($n['type'] === 'follow'): ?>
                                    started following you
                                <?php endif; ?>
                            </p>

                            <!-- Time -->
                            <p class="text-xs text-gray-500 mt-1">
                                <?= date("M j, H:i", strtotime($n['created_at'])) ?>
                            </p>
                        </div>

                    </div>
                </a>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
