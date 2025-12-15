# INKSPIRE  
A Social Photo‑Sharing Platform  
_First Semester PBW Final Project_

INKSPIRE is a full‑stack PHP MVC web application designed to let users share photos, explore content, follow creators, and interact through likes, comments, and notifications.  
The platform focuses on clean UX, secure authentication, and a modern social‑media‑style experience.

---

## ✨ Features

### 👤 User Accounts & Profiles
- Register, log in, log out  
- Custom profile with bio and picture  
- Follow/unfollow other users  
- Block system with global admin block

### 📸 Posts
- Create, edit, delete posts  
- Optional image upload  
- Tags, sticky posts, privacy settings  
- Real‑time like and comment counts

### 💬 Interactions
- Likes and comments  
- Nested replies  
- Smart cooldown to prevent spam  
- Live notifications (likes, follows, comments, replies)

### 🔔 Notifications
- Notification center  
- Mark read/unread  
- Auto‑remove broken references (deleted posts/comments redirect to 404)

### 🔐 Security
- Password hashing (bcrypt)  
- Session handling  
- CSRF protection for actions  
- Sanitization and validation on all user input  

### 🛠 Admin Tools
- Admin panel  
- Toggle admin status  
- Manage users (block/unblock)  
- Edit About and Terms content  

---

## 🧱 Tech Stack

- **PHP 8 (OOP + MVC architecture)**
- **MySQL** with foreign keys, triggers, and views  
- **TailwindCSS** for styling  
- **JavaScript (AJAX)** for dynamic like/comment interactions  
- **PHPMailer** for password resets  
- **Image processing** via custom resizer class  

---

## 📂 Project Structure

```
/Controllers   → Handles app logic  
/Models        → Database interaction  
/Views         → Page templates  
/uploads       → User media  
index.php      → Front controller & router  
autoloader.php → Class autoloading  
```

---


## 🚀 Getting Started

1. Import the SQL structure from `/InkspireDB.sql`  
2. Configure database connection in `/config.php`  
3. Ensure `/uploads` is writable  
4. Run the app through a local server (XAMPP/MAMP) or deploy on a PHP host  

---

## 📄 License
This project was created as part of my final PBW semester project.  
Feel free to explore or reference the structure, but please do not redistribute as your own coursework.

---

