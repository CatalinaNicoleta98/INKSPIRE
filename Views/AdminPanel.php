
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>

<?php
// Ensure variables exist
$users = $users ?? [];
$stats = $stats ?? ['new_users_today' => 0, 'new_posts_today' => 0];
?>

<div class="ml-[220px] mt-[90px] max-w-5xl p-6">
    <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>

    <!-- Daily Stats -->
    <section class="mb-10">
        <h2 class="text-xl font-semibold mb-3">Today's Statistics</h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-indigo-50 rounded-lg shadow">
                <p class="text-sm text-gray-600">New Users Today</p>
                <p class="text-3xl font-bold text-indigo-600"><?= $stats['new_users_today'] ?></p>
            </div>
            <div class="p-4 bg-indigo-50 rounded-lg shadow">
                <p class="text-sm text-gray-600">New Posts Today</p>
                <p class="text-3xl font-bold text-indigo-600"><?= $stats['new_posts_today'] ?></p>
            </div>
        </div>
    </section>

    <!-- Users Table -->
    <section>
        <h2 class="text-xl font-semibold mb-3">User Overview</h2>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Profile</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Username</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Followers</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Posts</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Admin</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="px-4 py-2">
                                <img src="<?= htmlspecialchars($u['profile_picture']) ?>" class="w-10 h-10 rounded-full object-cover" />
                            </td>

                            <td class="px-4 py-2">
                                <a href="index.php?action=profile&amp;id=<?= $u['user_id'] ?>" class="text-indigo-600 hover:underline">
                                    <?= htmlspecialchars($u['username']) ?>
                                </a>
                            </td>

                            <td class="px-4 py-2"><?= (int)$u['followers'] ?></td>
                            <td class="px-4 py-2"><?= (int)$u['posts'] ?></td>

                            <td class="px-4 py-2">
                                <?php if ($u['is_active']): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Blocked</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-2">
                                <?php if ($u['is_admin']): ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Admin</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">User</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-2 flex gap-2">

                                <!-- Block / Unblock -->
                                <form action="index.php?action=adminToggleBlock" method="POST">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="block" value="<?= $u['is_active'] ? 1 : 0 ?>">
                                    <button class="px-3 py-1 text-sm rounded 
                                        <?= $u['is_active'] ? 'bg-red-200 text-red-700' : 'bg-green-200 text-green-700' ?>">
                                        <?= $u['is_active'] ? 'Block' : 'Unblock' ?>
                                    </button>
                                </form>

                                <!-- Promote / Demote -->
                                <form action="index.php?action=adminToggleAdmin" method="POST">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="is_admin" value="<?= $u['is_admin'] ? 0 : 1 ?>">
                                    <button class="px-3 py-1 text-sm rounded 
                                        <?= $u['is_admin'] ? 'bg-gray-300 text-gray-800' : 'bg-yellow-200 text-yellow-800' ?>">
                                        <?= $u['is_admin'] ? 'Demote' : 'Promote' ?>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </section>
</div>
