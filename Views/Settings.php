<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
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
    <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Account Settings</h2>

    <form method="POST" action="index.php?action=updateSettings" enctype="multipart/form-data" class="space-y-5">
      <div class="text-center">
        <label class="block text-gray-700 font-medium mb-2">Profile Picture</label>
        <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'uploads/default.png') ?>" 
             alt="Profile" 
             class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-indigo-200 shadow-sm mb-3">
        <input type="file" name="profile_picture" accept="image/*" 
               class="block mx-auto text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required 
               class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Bio</label>
        <textarea name="bio" rows="4" placeholder="Write something about yourself..." 
                  class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
      </div>

      <div class="text-center">
        <button type="submit" 
                class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

</body>
</html>