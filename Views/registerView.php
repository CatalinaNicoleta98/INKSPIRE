<?php $error = $error ?? null; ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Register</title></head>
<body>
<h2>Register</h2>

<?php if ($error): ?>
  <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST" action="index.php?route=register_post">
  <label>First name: <input type="text" name="first_name" required></label><br><br>
  <label>Last name: <input type="text" name="last_name" required></label><br><br>
  <label>Email: <input type="email" name="email" required></label><br><br>
  <label>Username: <input type="text" name="username" required></label><br><br>
  <label>Password: <input type="password" name="password" required minlength="8"></label><br><br>
  <label>Date of birth: <input type="date" name="dob"></label><br><br>
  <button type="submit">Register</button>
</form>

<p>Already registered? <a href="index.php?route=login">Login here</a></p>
</body>
</html>