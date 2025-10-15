<?php
require_once __DIR__ . '/../../helpers/Session.php';
if (!isset($user)) {
    global $user;
}
?>
<style>
.header {
  position: fixed;
  top: 0; left: 0;
  width: 99vw;
  height: 60px;
  background: #ffffff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px 0 10px;
  z-index: 100;
}
.logo {
  font-size: 22px;
  font-weight: bold;
  color: #007BFF;
}
.profile-menu {
  position: relative;
  display: inline-block;
}
.profile-pic {
  width: 40px; height: 40px;
  border-radius: 50%;
  object-fit: cover;
  cursor: pointer;
  border: 2px solid #007BFF;
}
.dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 50px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  min-width: 150px;
}
.dropdown a {
  display: block;
  padding: 10px;
  text-decoration: none;
  color: #333;
}
.dropdown a:hover {
  background: #f4f4f4;
}
</style>

<div class="header">
  <div class="logo">Inkspire</div>
  <div class="profile-menu">
    <?php
      $profilePic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/40';
    ?>
    <img src="<?= $profilePic ?>" alt="Profile" class="profile-pic" id="profileBtn">
    <div class="dropdown" id="dropdownMenu">
      <a href="index.php?action=profile">My Profile</a>
      <a href="index.php?action=logout">Logout</a>
    </div>
  </div>
</div>

<script>
const profileBtn = document.getElementById("profileBtn");
const dropdown = document.getElementById("dropdownMenu");
let dropdownOpen = false;
profileBtn.addEventListener("click", () => {
  dropdown.style.display = dropdownOpen ? "none" : "block";
  dropdownOpen = !dropdownOpen;
});
window.addEventListener("click", (e) => {
  if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.style.display = "none";
    dropdownOpen = false;
  }
});
</script>