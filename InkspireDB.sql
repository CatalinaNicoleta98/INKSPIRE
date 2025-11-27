SET default_storage_engine = 'InnoDB';

DROP DATABASE IF EXISTS InkspireDB;
CREATE DATABASE InkspireDB;
USE InkspireDB;

-- ==============================
-- Tables
-- ==============================

CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    username   VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    DOB        DATE,
    is_admin   TINYINT(1) DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE Profile (
    profile_id      INT AUTO_INCREMENT PRIMARY KEY,
    display_name    VARCHAR(100),
    profile_picture VARCHAR(255),
    bio             TEXT,
    followers       INT DEFAULT 0,
    posts           INT DEFAULT 0,
    is_private      TINYINT(1) DEFAULT 0 COMMENT '0 = public, 1 = private',
    user_id         INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Post (
    post_id     INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(50),
    description VARCHAR(1000),
    image_url   VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_public   TINYINT(1) DEFAULT 1,
    is_sticky   TINYINT(1) DEFAULT 0,
    tags        VARCHAR(255),
    user_id     INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    text       TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    parent_id  INT,
    user_id    INT NOT NULL,
    post_id    INT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES Comment(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id)   REFERENCES Post(post_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `Like` (
    like_id    INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id    INT NOT NULL,
    post_id    INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES Post(post_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Reaction (
    reaction_id   INT AUTO_INCREMENT PRIMARY KEY,
    reaction_type VARCHAR(20) NOT NULL COMMENT 'LIKE or DISLIKE',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id       INT NOT NULL,
    comment_id    INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES Comment(comment_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Notification (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    type            VARCHAR(100) NOT NULL,
    post_id         INT,
    comment_id      INT,
    user_id         INT NOT NULL,
    actor_id        INT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id)    REFERENCES Post(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES Comment(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id)   REFERENCES User(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Follow (
    follower_id  INT NOT NULL,
    following_id INT NOT NULL,
    PRIMARY KEY (follower_id, following_id),
    FOREIGN KEY (follower_id)  REFERENCES Profile(profile_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES Profile(profile_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Block (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES User(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content LONGTEXT NOT NULL,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================
-- Views
-- ==============================

DROP VIEW IF EXISTS view_new_posts_today;
CREATE VIEW view_new_posts_today AS
SELECT COUNT(*) AS new_posts_today
FROM Post
WHERE DATE(created_at) = CURDATE();

DROP VIEW IF EXISTS view_new_users_today;
CREATE VIEW view_new_users_today AS
SELECT COUNT(*) AS new_users_today
FROM User
WHERE DATE(created_at) = CURDATE();

-- ==============================
-- Triggers
-- ==============================

-- Sync Profile.display_name with User.username
DROP TRIGGER IF EXISTS profile_bi_set_display_name;
DELIMITER $$
CREATE TRIGGER profile_bi_set_display_name
BEFORE INSERT ON Profile
FOR EACH ROW
BEGIN
  IF NEW.display_name IS NULL OR NEW.display_name = '' THEN
    DECLARE v_username VARCHAR(100);
    SELECT username INTO v_username FROM User WHERE user_id = NEW.user_id LIMIT 1;
    SET NEW.display_name = v_username;
  END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS user_au_sync_display_name;
DELIMITER $$
CREATE TRIGGER user_au_sync_display_name
AFTER UPDATE ON User
FOR EACH ROW
BEGIN
  IF NEW.username <> OLD.username THEN
    UPDATE Profile
    SET display_name = NEW.username
    WHERE user_id = NEW.user_id;
  END IF;
END$$
DELIMITER ;

-- Follow triggers
DROP TRIGGER IF EXISTS follow_ai_update_followers;
DELIMITER $$
CREATE TRIGGER follow_ai_update_followers
AFTER INSERT ON Follow
FOR EACH ROW
BEGIN
  UPDATE Profile
  SET followers = followers + 1
  WHERE profile_id = NEW.following_id;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS follow_ad_update_followers;
DELIMITER $$
CREATE TRIGGER follow_ad_update_followers
AFTER DELETE ON Follow
FOR EACH ROW
BEGIN
  UPDATE Profile
  SET followers = GREATEST(followers - 1, 0)
  WHERE profile_id = OLD.following_id;
END$$
DELIMITER ;

-- Follow notification
DROP TRIGGER IF EXISTS follow_ai_create_notification;
DELIMITER $$
CREATE TRIGGER follow_ai_create_notification
AFTER INSERT ON Follow
FOR EACH ROW
BEGIN
  DECLARE followerUser INT;
  DECLARE followingUser INT;

  SELECT user_id INTO followerUser FROM Profile WHERE profile_id = NEW.follower_id LIMIT 1;
  SELECT user_id INTO followingUser FROM Profile WHERE profile_id = NEW.following_id LIMIT 1;

  IF followerUser IS NOT NULL
     AND followingUser IS NOT NULL
     AND followerUser <> followingUser THEN
      INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
      VALUES ('follow', NULL, NULL, followingUser, followerUser, 0);
  END IF;
END$$
DELIMITER ;

-- Post counters
DROP TRIGGER IF EXISTS post_ai_update_post_count;
DELIMITER $$
CREATE TRIGGER post_ai_update_post_count
AFTER INSERT ON Post
FOR EACH ROW
BEGIN
  UPDATE Profile
  SET posts = posts + 1
  WHERE user_id = NEW.user_id;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS post_ad_update_post_count;
DELIMITER $$
CREATE TRIGGER post_ad_update_post_count
AFTER DELETE ON Post
FOR EACH ROW
BEGIN
  UPDATE Profile
  SET posts = GREATEST(posts - 1, 0)
  WHERE user_id = OLD.user_id;
END$$
DELIMITER ;

-- Like notifications
DROP TRIGGER IF EXISTS like_ai_create_notification;
DELIMITER $$
CREATE TRIGGER like_ai_create_notification
AFTER INSERT ON `Like`
FOR EACH ROW
BEGIN
  DECLARE postOwner INT;

  SELECT user_id INTO postOwner
  FROM Post
  WHERE post_id = NEW.post_id
  LIMIT 1;

  IF postOwner IS NOT NULL AND postOwner <> NEW.user_id THEN
    INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
    VALUES ('like', NEW.post_id, NULL, postOwner, NEW.user_id, 0);
  END IF;
END$$
DELIMITER ;

-- Comment notifications
DROP TRIGGER IF EXISTS comment_ai_create_notification;
DELIMITER $$
CREATE TRIGGER comment_ai_create_notification
AFTER INSERT ON Comment
FOR EACH ROW
BEGIN
  DECLARE postOwner   INT;
  DECLARE parentOwner INT;
  DECLARE receiver    INT;

  SELECT user_id INTO postOwner
  FROM Post
  WHERE post_id = NEW.post_id
  LIMIT 1;

  IF NEW.parent_id IS NOT NULL THEN
    SELECT user_id INTO parentOwner
    FROM Comment
    WHERE comment_id = NEW.parent_id
    LIMIT 1;
  ELSE
    SET parentOwner = NULL;
  END IF;

  IF NEW.parent_id IS NOT NULL THEN
    SET receiver = parentOwner;
  ELSE
    SET receiver = postOwner;
  END IF;

  IF receiver IS NOT NULL AND receiver <> NEW.user_id THEN
    INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
    VALUES (
      CASE WHEN NEW.parent_id IS NOT NULL THEN 'reply' ELSE 'comment' END,
      NEW.post_id,
      NEW.comment_id,
      receiver,
      NEW.user_id,
      0
    );
  END IF;
END$$
DELIMITER ;