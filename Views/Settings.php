

<?php require_once __DIR__ . '/../helpers/Session.php'; ?>
<?php include __DIR__ . '/layout/Header.php'; ?>
<?php include __DIR__ . '/layout/Sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inkspire | Settings</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    .settings-container {
      margin-left: 260px;
      margin-top: 100px;
      max-width: 600px;
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    h2 { text-align: center; margin-bottom: 20px; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      margin-top: 20px;
      background: #007BFF;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    .profile-pic {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
      margin: 10px auto;
    }
  </style>
</head>
<body>

<?php
Session::start();
$user = Session::get('user');
?>

<div class="settings-container">
  <h2>Account Settings</h2>

  <form method="POST" action="index.php?action=updateSettings" enctype="multipart/form-data">
    <label>Profile Picture</label>
    <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'uploads/default.png') ?>" alt="Profile" class="profile-pic">
    <input type="file" name="profile_picture" accept="image/*">

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>Bio</label>
    <textarea name="bio" rows="4" placeholder="Write something about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

    <button type="submit">Save Changes</button>
  </form>
</div>

</body>
</html>