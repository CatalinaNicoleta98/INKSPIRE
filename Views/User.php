<?php
require_once __DIR__ . '/../helpers/Session.php';
$action = $_GET['action'] ?? 'login';
?>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($action === 'register'): ?>
    <h2>Register</h2>
    <form method="POST" action="index.php?action=register">
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <label>Date of Birth:</label><br>
        <input type="date" name="dob" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="index.php?action=login">Login here</a></p>

<?php elseif ($action === 'login'): ?>
    <h2>Login</h2>
    <form method="POST" action="index.php?action=login">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>No account? <a href="index.php?action=register">Register here</a></p>

<?php elseif ($action === 'home' && isset($user)): ?>
    <h2>Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
    <form method="POST" action="index.php?action=logout">
        <button type="submit">Logout</button>
    </form>

<?php elseif ($action === 'admin' && isset($user) && !empty($user['is_admin'])): ?>
    <h2>Welcome, Admin <?= htmlspecialchars($user['username']) ?>!</h2>
    <p>Admin dashboard coming soon...</p>
    <form method="POST" action="index.php?action=logout">
        <button type="submit">Logout</button>
    </form>
<?php endif; ?>