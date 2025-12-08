<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Settings</title>
</head>

<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

<div class="flex justify-center items-start w-full lg:px-[300px] md:px-[200px] sm:px-4 pt-[90px]">
  <div class="w-full max-w-[600px] bg-white rounded-xl shadow-md p-8 mx-auto">
    <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Profile Settings</h2>

    <div class="flex justify-center gap-6 mb-6">
      <a href="index.php?action=settings" 
         class="text-indigo-600 font-medium hover:underline <?= $section === 'account' ? 'underline' : '' ?>">Profile Settings</a>
      <a href="index.php?action=settings&section=blocked" 
         class="text-indigo-600 font-medium hover:underline <?= $section === 'blocked' ? 'underline' : '' ?>">Blocked Accounts</a>
    </div>

    <?php if ($section === 'account'): ?>

    <form method="POST" action="index.php?action=updateSettings" enctype="multipart/form-data" class="space-y-5">

      <!-- PROFILE PICTURE -->
      <div class="text-center">
        <label class="block text-gray-700 font-medium mb-2">Profile Picture</label>

        <img id="profilePreview" 
             src="<?= $currentPic ?>" 
             class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-indigo-200 shadow-sm mb-3">

        <input id="profileFileInput" type="file" name="profile_picture" accept="image/*"
               class="block mx-auto text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer">

        <div class="text-center mt-3">
          <button type="button"
                  id="openDeletePicModal"
                  class="text-sm bg-red-100 text-red-600 px-4 py-2 rounded-md hover:bg-red-200 transition">
            Delete Profile Picture
          </button>
        </div>

        <!-- DELETE PICTURE MODAL -->
        <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg p-6 shadow-lg text-center max-w-sm w-full">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Are you sure you want to delete your profile picture?</h3>

            <div class="flex justify-center gap-4">
              <button id="confirmDeletePic" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Yes, Delete</button>
              <button id="cancelDeletePic" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- USERNAME + EMAIL -->
      <div>
        <label class="block text-gray-700 font-medium mb-1">Username</label>
        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 bg-gray-100 cursor-not-allowed">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 bg-gray-100 cursor-not-allowed">
      </div>

      <!-- BIO -->
      <div>
        <label class="block text-gray-700 font-medium mb-1">Bio</label>
        <textarea name="bio" rows="4"
                  class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300"><?= $currentBio ?></textarea>
      </div>

      <!-- PRIVACY -->
      <div class="flex items-center justify-between border border-indigo-100 rounded-md p-3 bg-indigo-50">
        <label class="text-gray-700 font-medium">Make Profile Private</label>
        <input type="checkbox" name="is_private" id="is_private" class="h-5 w-5 text-indigo-600" <?= $isPrivate ?>>
        <input type="hidden" name="is_private_hidden" value="0">
      </div>

      <!-- SAVE -->
      <div class="text-center">
        <button type="submit" 
                class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md hover:from-indigo-500 hover:to-purple-500 transition">
          Save Changes
        </button>
      </div>

      <!-- DANGER ZONE -->
      <div class="mt-8 border-t border-red-100 pt-6">
        <h3 class="text-lg font-semibold text-red-600 mb-2 text-center">Danger Zone</h3>
        <p class="text-gray-600 text-sm mb-4 text-center">
          Deleting your account permanently removes everything. This action cannot be undone.
        </p>

        <div class="text-center">
          <button type="button" id="openDeleteAccountModal"
                  class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition">
            Delete Account
          </button>
        </div>
      </div>

    </form>

    <?php else: ?>

      <!-- BLOCKED USERS -->
      <div class="space-y-4">
        <h3 class="text-xl font-semibold text-indigo-600 mb-4 text-center">Blocked Accounts</h3>

        <?php if (!empty($blockedUsers)): ?>
          <?php foreach ($blockedUsers as $blocked): ?>
            <div class="flex items-center justify-between border border-indigo-100 rounded-lg p-3 bg-indigo-50">
              <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($blocked['profile_picture'] ?? 'uploads/default.png') ?>" 
                     class="w-10 h-10 rounded-full object-cover border border-indigo-200">
                <span class="font-medium text-gray-700"><?= htmlspecialchars($blocked['username']) ?></span>
              </div>

              <button class="unblock-btn bg-red-100 text-red-600 px-3 py-1 rounded-md hover:bg-red-200" 
                      data-user-id="<?= htmlspecialchars($blocked['user_id']) ?>">
                Unblock
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500 text-center">You haven't blocked anyone yet.</p>
        <?php endif; ?>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- DELETE ACCOUNT MODAL -->
<div id="deleteAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 shadow-lg text-center max-w-sm w-full">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete your account?</h3>
    <p class="text-sm text-gray-600 mb-4">
      This will permanently delete everything. This action cannot be undone.
    </p>

    <div class="mb-4 text-left">
      <label class="block text-sm font-medium mb-1">Confirm your password</label>
      <input type="password" id="deleteAccountPassword"
             class="w-full border border-gray-300 rounded-md px-3 py-2">
      <p id="deleteAccountError" class="text-xs text-red-500 mt-1 hidden"></p>
    </div>

    <div class="flex justify-center gap-3">
      <button id="confirmDeleteAccount" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">
        Delete permanently
      </button>
      <button id="cancelDeleteAccount" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
        Cancel
      </button>
    </div>
  </div>
</div>

<!-- EXTERNAL JS -->
<script src="scripts/settings.js"></script>

</body>
</html>