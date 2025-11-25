<?php
require_once __DIR__ . '/../helpers/Session.php';
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

  <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 mx-4">
    <!-- show success message if user just registered -->
    <?php if (!empty($_SESSION['success_message'])): ?>
      <p class="text-green-600 text-center mb-4 text-sm font-medium bg-green-50 py-2 rounded-md">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
      </p>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
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
        <div class="relative">
          <input type="password" name="password" id="registerPassword" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
          <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700 password-toggle" data-target="registerPassword">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        <ul class="mt-2 text-xs space-y-1">
          <li id="req-length" class="text-gray-500">• At least 8 characters</li>
          <li id="req-upper" class="text-gray-500">• At least one uppercase letter</li>
          <li id="req-lower" class="text-gray-500">• At least one lowercase letter</li>
          <li id="req-number" class="text-gray-500">• At least one number</li>
          <li id="req-special" class="text-gray-500">• At least one special character (!@#$%^&amp;*)</li>
        </ul>
        <input type="password" name="password_confirm" id="registerPasswordConfirm" placeholder="Confirm Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none mt-3">
        <p id="passwordMatchHint" class="text-xs mt-1 text-gray-500"></p>
        <label class="block text-gray-700 text-sm mt-2">Date of Birth</label>
        <input type="date" name="dob" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md mt-2">Register</button>
      </form>
      <p class="text-center text-sm text-gray-600 mt-4">Already have an account? <a href="index.php?action=login" class="text-indigo-500 hover:underline">Login here</a></p>

    <?php elseif ($action === 'login'): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Welcome Back</h2>
      <form method="POST" action="index.php?action=login" class="space-y-4">
        <input type="text" name="username" placeholder="Username" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <div class="relative">
          <input type="password" name="password" id="loginPassword" placeholder="Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
          <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700 password-toggle" data-target="loginPassword">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Login</button>
      </form>
      <p class="text-center text-sm text-gray-600 mt-2">
        <a href="index.php?action=forgotPassword" class="text-indigo-500 hover:underline">Forgot your password?</a>
      </p>
      <p class="text-center text-sm text-gray-600 mt-4">No account? <a href="index.php?action=register" class="text-indigo-500 hover:underline">Register here</a></p>

    <?php elseif ($action === 'forgotPassword'): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Reset Your Password</h2>

      <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-4 text-sm"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <p class="text-green-600 text-center mb-4 text-sm break-all"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <form method="POST" action="index.php?action=sendResetLink" class="space-y-4">
        <input type="email" name="email" placeholder="Enter your email" required class="w-full border border-indigo-200 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Send Reset Link</button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-4">
        Remember your password? <a href="index.php?action=login" class="text-indigo-500 hover:underline">Go back</a>
      </p>

    <?php elseif ($action === 'resetPassword' && isset($_GET['token'])): ?>
      <h2 class="text-2xl font-semibold text-indigo-600 text-center mb-6">Choose a New Password</h2>

      <form method="POST" action="index.php?action=updatePassword" class="space-y-4">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">

        <div class="relative">
          <input type="password" name="password" id="resetPasswordInput" placeholder="New Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
          <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700 password-toggle" data-target="resetPasswordInput">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        <ul class="mt-2 text-xs space-y-1">
          <li id="reset-req-length" class="text-gray-500">• At least 8 characters</li>
          <li id="reset-req-upper" class="text-gray-500">• At least one uppercase letter</li>
          <li id="reset-req-lower" class="text-gray-500">• At least one lowercase letter</li>
          <li id="reset-req-number" class="text-gray-500">• At least one number</li>
          <li id="reset-req-special" class="text-gray-500">• At least one special character (!@#$%^&*)</li>
        </ul>
        <div class="relative mt-3">
          <input type="password" name="password_confirm" id="resetPasswordConfirm" placeholder="Confirm New Password" required class="w-full border border-indigo-200 rounded-md px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
          <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700 password-toggle" data-target="resetPasswordConfirm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        <p id="resetPasswordMatchHint" class="text-xs mt-1 text-gray-500"></p>

        <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-400 text-white py-2 rounded-md hover:from-indigo-500 hover:to-purple-500 transition shadow-md">Save New Password</button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-4">
        Changed your mind? <a href="index.php?action=login" class="text-indigo-500 hover:underline">Go back</a>
      </p>

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
<script>
  const eyeOpen = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  `;

  const eyeClosed = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A3 3 0 0113.412 13.41M6.883 6.883C4.943 8.173 3.5 10.063 2.458 12c1.274 4.057 5.065 7 9.542 7 1.466 0 2.853-.294 4.104-.828"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M17.117 17.117C19.057 15.827 20.5 13.937 21.542 12c-1.274-4.057-5.065-7-9.542-7-1.466 0-2.853.294-4.104.828"/>
    </svg>
  `;

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.password-toggle');
    if (!btn) return;

    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    if (!input) return;

    const hide = input.type === 'password';
    input.type = hide ? 'text' : 'password';
    btn.innerHTML = hide ? eyeClosed : eyeOpen;
  });

  // Live password strength + match validation for register form
  const registerPasswordInput = document.getElementById('registerPassword');
  const registerPasswordConfirmInput = document.getElementById('registerPasswordConfirm');
  const reqLength = document.getElementById('req-length');
  const reqUpper = document.getElementById('req-upper');
  const reqLower = document.getElementById('req-lower');
  const reqNumber = document.getElementById('req-number');
  const reqSpecial = document.getElementById('req-special');
  const passwordMatchHint = document.getElementById('passwordMatchHint');

  function setRequirement(el, satisfied) {
    if (!el) return;
    if (satisfied) {
      el.classList.remove('text-gray-500', 'text-red-500');
      el.classList.add('text-green-600');
    } else {
      el.classList.remove('text-green-600');
      el.classList.add('text-gray-500');
    }
  }

  function updatePasswordRequirements() {
    if (!registerPasswordInput) return;
    const pwd = registerPasswordInput.value || '';
    const hasMinLength = pwd.length >= 8;
    const hasUpper = /[A-Z]/.test(pwd);
    const hasLower = /[a-z]/.test(pwd);
    const hasNumber = /[0-9]/.test(pwd);
    const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

    setRequirement(reqLength, hasMinLength);
    setRequirement(reqUpper, hasUpper);
    setRequirement(reqLower, hasLower);
    setRequirement(reqNumber, hasNumber);
    setRequirement(reqSpecial, hasSpecial);
  }

  function updatePasswordMatch() {
    if (!registerPasswordInput || !registerPasswordConfirmInput || !passwordMatchHint) return;
    const pwd = registerPasswordInput.value || '';
    const confirmPwd = registerPasswordConfirmInput.value || '';

    if (!confirmPwd && !pwd) {
      passwordMatchHint.textContent = '';
      passwordMatchHint.classList.remove('text-red-500', 'text-green-600');
      passwordMatchHint.classList.add('text-gray-500');
      return;
    }

    if (confirmPwd && pwd === confirmPwd) {
      passwordMatchHint.textContent = 'Passwords match.';
      passwordMatchHint.classList.remove('text-gray-500', 'text-red-500');
      passwordMatchHint.classList.add('text-green-600');
    } else if (confirmPwd) {
      passwordMatchHint.textContent = 'Passwords do not match.';
      passwordMatchHint.classList.remove('text-gray-500', 'text-green-600');
      passwordMatchHint.classList.add('text-red-500');
    }
  }

  if (registerPasswordInput) {
    registerPasswordInput.addEventListener('input', function () {
      updatePasswordRequirements();
      updatePasswordMatch();
    });
  }
  if (registerPasswordConfirmInput) {
    registerPasswordConfirmInput.addEventListener('input', updatePasswordMatch);
  }
  updatePasswordRequirements();

  // Reset password validation
  const resetPasswordInput = document.getElementById('resetPasswordInput');
  const resetPasswordConfirmInput = document.getElementById('resetPasswordConfirm');

  const resetReqLength = document.getElementById('reset-req-length');
  const resetReqUpper = document.getElementById('reset-req-upper');
  const resetReqLower = document.getElementById('reset-req-lower');
  const resetReqNumber = document.getElementById('reset-req-number');
  const resetReqSpecial = document.getElementById('reset-req-special');
  const resetPasswordMatchHint = document.getElementById('resetPasswordMatchHint');

  function updateResetRequirements() {
    if (!resetPasswordInput) return;
    const pwd = resetPasswordInput.value || '';
    const hasMinLength = pwd.length >= 8;
    const hasUpper = /[A-Z]/.test(pwd);
    const hasLower = /[a-z]/.test(pwd);
    const hasNumber = /[0-9]/.test(pwd);
    const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

    setRequirement(resetReqLength, hasMinLength);
    setRequirement(resetReqUpper, hasUpper);
    setRequirement(resetReqLower, hasLower);
    setRequirement(resetReqNumber, hasNumber);
    setRequirement(resetReqSpecial, hasSpecial);
  }

  function updateResetMatch() {
    if (!resetPasswordInput || !resetPasswordConfirmInput || !resetPasswordMatchHint) return;

    const pwd = resetPasswordInput.value || '';
    const confirmPwd = resetPasswordConfirmInput.value || '';

    if (!confirmPwd && !pwd) {
      resetPasswordMatchHint.textContent = '';
      resetPasswordMatchHint.classList.remove('text-red-500', 'text-green-600');
      resetPasswordMatchHint.classList.add('text-gray-500');
      return;
    }

    if (confirmPwd && pwd === confirmPwd) {
      resetPasswordMatchHint.textContent = 'Passwords match.';
      resetPasswordMatchHint.classList.remove('text-gray-500', 'text-red-500');
      resetPasswordMatchHint.classList.add('text-green-600');
    } else if (confirmPwd) {
      resetPasswordMatchHint.textContent = 'Passwords do not match.';
      resetPasswordMatchHint.classList.remove('text-gray-500', 'text-green-600');
      resetPasswordMatchHint.classList.add('text-red-500');
    }
  }

  if (resetPasswordInput) {
    resetPasswordInput.addEventListener('input', function () {
      updateResetRequirements();
      updateResetMatch();
    });
  }
  if (resetPasswordConfirmInput) {
    resetPasswordConfirmInput.addEventListener('input', updateResetMatch);
  }
  updateResetRequirements();
</script>
</html>