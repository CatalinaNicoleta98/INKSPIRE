<?php
$database = new Database();
$db = $database->connect();
$termsModel = new TermsModel($db);
$terms = $termsModel->getTerms();
if (!isset($action)) {
    $action = $_GET['action'] ?? 'login';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | <?= ucfirst($action) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center">

  <!-- LOGO -->
  <div class="absolute top-10 w-full flex justify-center z-50 pointer-events-none">
    <img src="uploads/logo.png" alt="Inkspire Logo" class="w-32 h-32 object-contain opacity-90 drop-shadow-lg">
  </div>

  <?php $extraTop = ($action === 'register') ? 'mt-40' : 'mt-12'; ?>
  <div class="<?= $extraTop ?> w-full flex justify-center">
  <div class="backdrop-blur-xl bg-white/40 border border-white/30 rounded-2xl shadow-2xl w-full max-w-md p-8 mx-4">

    <!-- SUCCESS -->
    <?php if (!empty($_SESSION['success_message'])): ?>
      <p class="text-green-600 text-center mb-4 text-sm font-medium bg-green-50 py-2 rounded-md">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
      </p>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- ERROR -->
    <?php if (!empty($error)): ?>
      <p class="text-red-500 text-center mb-4 text-sm"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- REGISTER -->
    <?php if ($action === 'register'): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Create Account</h2>

      <form method="POST" action="index.php?action=register" class="space-y-4">

        <input type="text" name="first_name" placeholder="First Name" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">

        <input type="text" name="last_name" placeholder="Last Name" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">

        <input type="email" name="email" placeholder="Email" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none" value="<?= htmlspecialchars($old['email'] ?? '') ?>">

        <input type="text" name="username" placeholder="Username" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none" value="<?= htmlspecialchars($old['username'] ?? '') ?>">

        <div class="relative">
          <input type="password" name="password" id="registerPassword" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10">
          <button type="button" class="password-toggle absolute inset-y-0 right-3 text-gray-500 hover:text-gray-700" data-target="registerPassword"></button>
        </div>

        <ul class="mt-2 text-xs space-y-1">
          <li id="req-length" class="text-gray-500">• At least 8 characters</li>
          <li id="req-upper" class="text-gray-500">• At least one uppercase letter</li>
          <li id="req-lower" class="text-gray-500">• At least one lowercase letter</li>
          <li id="req-number" class="text-gray-500">• At least one number</li>
          <li id="req-special" class="text-gray-500">• At least one special character (!@#$%^&*)</li>
        </ul>

        <input type="password" name="password_confirm" id="registerPasswordConfirm" placeholder="Confirm Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 mt-3">
        <p id="passwordMatchHint" class="text-xs mt-1 text-gray-500"></p>

        <label class="block text-gray-700 text-sm mt-2">Date of Birth</label>
        <div class="flex space-x-2">
          <select name="dob_day" class="border border-indigo-200 rounded-md px-3 py-2">
            <option value="">Day</option>
            <?php for($d=1;$d<=31;$d++): ?>
              <option value="<?= $d ?>" <?= isset($old['dob_day']) && $old['dob_day']==$d?'selected':'' ?>><?= $d ?></option>
            <?php endfor; ?>
          </select>

          <select name="dob_month" class="border border-indigo-200 rounded-md px-3 py-2">
            <option value="">Month</option>
            <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= isset($old['dob_month']) && $old['dob_month']==$m?'selected':'' ?>><?= $m ?></option>
            <?php endfor; ?>
          </select>

          <select name="dob_year" class="border border-indigo-200 rounded-md px-3 py-2">
            <option value="">Year</option>
            <?php for($y=date('Y')-14;$y>=1900;$y--): ?>
              <option value="<?= $y ?>" <?= isset($old['dob_year']) && $old['dob_year']==$y?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="flex items-center mt-2">
          <input type="checkbox" id="acceptTerms" name="accept_terms" required class="mr-2">
          <label for="acceptTerms" class="text-sm text-gray-700">
            I agree to the <button type="button" id="openTermsModal" class="text-indigo-600 underline">Terms & Conditions</button>
          </label>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md">Register</button>

      </form>

      <!-- TERMS MODAL -->
      <div id="termsModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
        <div class="bg-white max-w-2xl w-full mx-4 rounded-lg shadow-lg p-6 relative">
          <h2 class="text-xl font-semibold mb-4">Terms & Conditions</h2>
          <div class="max-h-96 overflow-y-auto text-sm text-gray-700 whitespace-pre-line"><?= htmlspecialchars($terms ?? '') ?></div>
          <button type="button" id="closeTermsModal" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded">Close</button>
        </div>
      </div>

      <p class="text-center text-sm text-gray-600 mt-4">Already have an account? <a href="index.php?action=login" class="text-indigo-500 hover:underline">Login here</a></p>

    <?php elseif ($action === 'login'): ?>

      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Welcome Back</h2>

      <form method="POST" action="index.php?action=login" class="space-y-4">
        <input type="text" name="username" placeholder="Username" required class="w-full border border-indigo-200 rounded-md px-3 py-2">

        <div class="relative">
          <input type="password" name="password" id="loginPassword" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10">
          <button type="button" class="password-toggle absolute inset-y-0 right-3 text-gray-500" data-target="loginPassword"></button>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md">Login</button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-2">
        <a href="index.php?action=forgotPassword" class="text-indigo-500 underline">Forgot your password?</a>
      </p>

      <p class="text-center text-sm text-gray-600 mt-4">No account? <a href="index.php?action=register" class="text-indigo-500 underline">Register here</a></p>

    <?php elseif ($action === 'forgotPassword'): ?>

      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Reset Your Password</h2>

      <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-4 text-sm"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <p class="text-green-600 text-center mb-4 text-sm"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['info'])): ?>
        <p class="text-blue-600 text-center mb-4 text-sm"><?= htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?></p>
      <?php endif; ?>

      <form method="POST" action="index.php?action=sendResetLink" class="space-y-4">
        <input type="email" name="email" placeholder="Enter your email" required class="w-full border border-indigo-200 rounded-md px-3 py-2">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md">Send Reset Link</button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-4">
        Remember your password? <a href="index.php?action=login" class="text-indigo-500 underline">Go back</a>
      </p>

    <?php elseif ($action === 'resetPassword' && isset($_GET['token'])): ?>

      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Choose a New Password</h2>

      <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-4 text-sm bg-red-50 py-2 rounded-md">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="index.php?action=updatePassword" class="space-y-4">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">

        <div class="relative">
          <input type="password" name="password" id="resetPasswordInput" placeholder="New Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10">
          <button type="button" class="password-toggle absolute inset-y-0 right-3 text-gray-500" data-target="resetPasswordInput"></button>
        </div>

        <ul class="mt-2 text-xs space-y-1">
          <li id="reset-req-length" class="text-gray-500">• At least 8 characters</li>
          <li id="reset-req-upper" class="text-gray-500">• At least one uppercase letter</li>
          <li id="reset-req-lower" class="text-gray-500">• At least one lowercase letter</li>
          <li id="reset-req-number" class="text-gray-500">• At least one number</li>
          <li id="reset-req-special" class="text-gray-500">• At least one special character (!@#$%^&*)</li>
        </ul>

        <div class="relative mt-3">
          <input type="password" name="password_confirm" id="resetPasswordConfirm" placeholder="Confirm New Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10">
          <button type="button" class="password-toggle absolute inset-y-0 right-3 text-gray-500" data-target="resetPasswordConfirm"></button>
        </div>

        <p id="resetPasswordMatchHint" class="text-xs mt-1 text-gray-500"></p>

        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md">Save New Password</button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-4">
        Changed your mind? <a href="index.php?action=login" class="text-indigo-500 underline">Go back</a>
      </p>

    <?php elseif ($action === 'home' && isset($user)): ?>
      <div class="text-center">
        <h2 class="text-2xl font-semibold text-indigo-600 mb-3">Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
        <form method="POST" action="index.php?action=logout">
          <button type="submit" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md">Logout</button>
        </form>
      </div>

    <?php elseif ($action === 'admin' && isset($user) && !empty($user['is_admin'])): ?>
      <div class="text-center">
        <h2 class="text-2xl font-semibold text-indigo-600 mb-3">Welcome, Admin <?= htmlspecialchars($user['username']) ?>!</h2>
        <p class="text-gray-600 mb-4">Admin dashboard coming soon...</p>
        <form method="POST" action="index.php?action=logout">
          <button type="submit" class="bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 px-6 rounded-md">Logout</button>
        </form>
      </div>
    <?php endif; ?>

  </div>
  </div>

<!-- GLOBAL BASE JS -->
<script src="scripts/user.js"></script>

<?php if ($action === 'register'): ?>
  <script src="scripts/user_register.js"></script>
<?php elseif ($action === 'resetPassword'): ?>
  <script src="scripts/user_reset.js"></script>
<?php endif; ?>

</body>
</html>