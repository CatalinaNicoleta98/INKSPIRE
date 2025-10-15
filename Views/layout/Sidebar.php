<style>
.sidebar {
  position: fixed;
  top: 60px; /* below header */
  left: 0;
  width: 220px;
  height: calc(100% - 60px);
  background: #ffffff;
  border-right: 1px solid #ddd;
  box-shadow: 1px 0 5px rgba(0,0,0,0.05);
  padding: 15px;
}
.sidebar input[type="text"] {
  width: 100%;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  margin-bottom: 15px;
}
.sidebar button {
  display: block;
  width: 100%;
  margin: 8px 0;
  padding: 10px;
  background: #007BFF;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
}
.sidebar button:hover {
  background: #0056b3;
}
.main-content {
  margin-left: 240px; /* push content right */
  padding-top: 70px;  /* space for header */
}
</style>

<div class="sidebar">
  <input type="text" id="search" placeholder="Search posts...">
  <button onclick="window.location='index.php?action=feed&type=new'">🆕 New Posts</button>
  <button onclick="window.location='index.php?action=feed&type=hot'">🔥 Hot Posts</button>
  <button onclick="window.location='index.php?action=feed&type=following'">👥 Following</button>
</div>

<div class="main-content">