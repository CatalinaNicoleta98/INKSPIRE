<?php
require_once __DIR__ . '/../helpers/Session.php';
$action = $_GET['action'] ?? 'login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | <?= ucfirst($action) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 mx-4">
    <?php if (!empty($error)): ?>
      <p class="text-red-500 text-center mb-4 text-sm"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($action === 'register'): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Create Account</h2>
      <form method="POST" action="index.php?action=register" class="space-y-4">
        <input type="text" name="first_name" placeholder="First Name" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="text" name="last_name" placeholder="Last Name" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="email" name="email" placeholder="Email" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="text" name="username" placeholder="Username" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="password" name="password" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <label class="block text-gray-700 text-sm mt-2">Date of Birth</label>
        <input type="date" name="dob" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md mt-2">Register</button>
      </form>
      <p class="text-center text-sm text-gray-600 mt-4">Already have an account? <a href="index.php?action=login" class="text-indigo-500 hover:underline">Login here</a></p>

    <?php elseif ($action === 'login'): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Welcome Back</h2>
      <form method="POST" action="index.php?action=login" class="space-y-4">
        <input type="text" name="username" placeholder="Username" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <input type="password" name="password" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Login</button>
      </form>
      <p class="text-center text-sm text-gray-600 mt-4">No account? <a href="index.php?action=register" class="text-indigo-500 hover:underline">Register here</a></p>

    <?php elseif ($action === 'home' && isset($user)): ?>
      <div class="text-center">
        <h2 class="text-2xl font-semibold text-indigo-600 mb-3">Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
        <form method="POST" action="index.php?action=logout">
          <button type="submit" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Logout</button>
        </form>
      </div>

    <?php elseif ($action === 'admin' && isset($user) && !empty($user['is_admin'])): ?>
      <div class="text-center">
        <h2 class="text-2xl font-semibold text-indigo-600 mb-3">Welcome, Admin <?= htmlspecialchars($user['username']) ?>!</h2>
        <p class="text-gray-600 mb-4">Admin dashboard coming soon...</p>
        <form method="POST" action="index.php?action=logout">
          <button type="submit" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Logout</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>