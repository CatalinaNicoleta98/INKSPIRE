<?php
$error   = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>
<body>
<h2>Login</h2>

<?php if ($success): ?>
  <p style="color:green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<?php if ($error): ?>
  <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST" action="index.php?route=login_post">
  <label>Username or Email:
    <input type="text" name="username" required>
  </label><br><br>

  <label>Password:
    <input type="password" name="password" required>
  </label><br><br>

  <button type="submit">Login</button>
</form>

<p>Don't have an account?
  <a href="index.php?route=register">Register here</a>
</p>
</body>
</html>