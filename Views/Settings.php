<?php 
require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/../Models/BlockModel.php';
Session::start();
$user = Session::get('user');

$profileModel = new ProfileModel();
$profile = $profileModel->getProfileByUserId($user['user_id']);

$section = $_GET['section'] ?? 'account';
$blockModel = new BlockModel();
$blockedUsers = ($section === 'blocked') ? $blockModel->getBlockedUsers($user['user_id']) : [];

$currentPic = !empty($profile['profile_picture']) ? htmlspecialchars($profile['profile_picture']) : 'uploads/default.png';
$currentBio = htmlspecialchars($profile['bio'] ?? '');
$isPrivate = isset($profile['is_private']) && $profile['is_private'] == 1 ? 'checked' : '';
?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Settings</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen">
<?php
Session::start();
$user = Session::get('user');
?>

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
      <div class="text-center">
        <label class="block text-gray-700 font-medium mb-2">Profile Picture</label>
        <img id="profilePreview" 
             src="<?= $currentPic ?>" 
             alt="Profile" 
             class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-indigo-200 shadow-sm mb-3">
        <input type="file" name="profile_picture" accept="image/*" 
               class="block mx-auto text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer"
               onchange="previewProfilePicture(event)">
      </div>
      <script>
        function previewProfilePicture(event) {
          const reader = new FileReader();
          reader.onload = function() {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;
          };
          reader.readAsDataURL(event.target.files[0]);
        }
      </script>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0 focus:outline-none">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0 focus:outline-none">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Bio</label>
        <textarea name="bio" rows="4" placeholder="Write something about yourself..." 
                  class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"><?= $currentBio ?></textarea>
      </div>

      <div class="flex items-center justify-between border border-indigo-100 rounded-md p-3 bg-indigo-50">
        <label for="is_private" class="text-gray-700 font-medium">Make Profile Private</label>
        <input type="checkbox" name="is_private" id="is_private"
               class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
               <?= $isPrivate ?>>
      </div>

      <div class="text-center">
        <button type="submit" 
                class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">
          Save Changes
        </button>
      </div>
    </form>
    <?php else: ?>
      <div class="space-y-4">
        <h3 class="text-xl font-semibold text-indigo-600 mb-4 text-center">Blocked Accounts</h3>

        <?php if (!empty($blockedUsers)): ?>
          <?php foreach ($blockedUsers as $blocked): ?>
            <div class="flex items-center justify-between border border-indigo-100 rounded-lg p-3 bg-indigo-50 hover:bg-indigo-100 transition">
              <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($blocked['profile_picture'] ?? 'uploads/default.png') ?>" 
                     class="w-10 h-10 rounded-full object-cover border border-indigo-200">
                <span class="font-medium text-gray-700"><?= htmlspecialchars($blocked['username']) ?></span>
              </div>
              <button 
                class="unblock-btn text-sm bg-red-100 text-red-600 px-3 py-1 rounded-md hover:bg-red-200 transition" 
                data-user-id="<?= htmlspecialchars($blocked['user_id']) ?>">
                Unblock
              </button>
            </div>
          <?php endforeach; ?>
          <script>
          document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.unblock-btn').forEach(button => {
              button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');

                fetch(`index.php?action=unblockUser&user_id=${userId}&ajax=1`)
                  .then(response => response.json())
                  .then(data => {
                    if (data.success) {
                      // Fade out and remove the unblocked user element
                      const container = button.closest('.flex.items-center');
                      container.style.transition = 'opacity 0.3s';
                      container.style.opacity = '0';
                      setTimeout(() => container.remove(), 300);
                    }
                  })
                  .catch(err => console.error('Unblock failed:', err));
              });
            });
          });
          </script>
        <?php else: ?>
          <p class="text-gray-500 text-center">You haven't blocked anyone yet.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>