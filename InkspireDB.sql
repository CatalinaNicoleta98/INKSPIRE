SET default_storage_engine = 'InnoDB';

-- Remove any existing database with the same name and create a new one
DROP DATABASE IF EXISTS InkspireDB; 
CREATE DATABASE InkspireDB; 

-- Switch working DB to the new database
USE InkspireDB;

-- ==============================
-- Create Tables
-- ==============================

CREATE TABLE User
(
    user_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(255) UNIQUE NOT NULL,
    username   VARCHAR(100) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    DOB        DATE,
    is_admin   TINYINT(1) DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Profile
(
    profile_id       INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    display_name     VARCHAR(100),
    profile_picture  VARCHAR(255),
    bio              TEXT,
    followers        INT DEFAULT 0,
    posts            INT DEFAULT 0,
    is_private       TINYINT(1) DEFAULT 0 COMMENT '0 = public, 1 = private',
    user_id          INT NOT NULL,
    CONSTRAINT fk_profile_user_cascade
      FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Post
(
    post_id     INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    title       VARCHAR(200),
    description TEXT,
    text        TEXT,
    image_url   VARCHAR(255),
    is_sticky   TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id     INT NOT NULL,
    tags        VARCHAR(255) DEFAULT NULL,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_public   TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_post_user_cascade
      FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Comment
(
    comment_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    text       TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    parent_id  INT,
    user_id    INT NOT NULL,
    post_id    INT NOT NULL,
    CONSTRAINT fk_comment_parent_cascade
      FOREIGN KEY (parent_id) REFERENCES Comment (comment_id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user_cascade
      FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_post_cascade
      FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `Like`
(
    like_id    INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id    INT NOT NULL,
    post_id    INT NOT NULL,
    CONSTRAINT fk_like_user_cascade
      FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_like_post_cascade
      FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Reaction
(
    reaction_id   INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    reaction_type VARCHAR(20) NOT NULL COMMENT 'LIKE or DISLIKE',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id       INT NOT NULL,
    comment_id    INT NOT NULL,
    CONSTRAINT fk_reaction_user_cascade
      FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_comment_cascade
      FOREIGN KEY (comment_id) REFERENCES Comment (comment_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Notification
(
    notification_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    type            VARCHAR(100) NOT NULL,
    post_id         INT,
    user_id         INT NOT NULL,
    CONSTRAINT fk_notification_post_cascade
      FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE,
    CONSTRAINT notification_ibfk_2
      FOREIGN KEY (user_id) REFERENCES User (user_id)
) ENGINE=InnoDB;

-- Junction table for M:N relationship between profiles (followers/following)
CREATE TABLE Follow
(
    follower_id  INT NOT NULL,
    following_id INT NOT NULL,
    CONSTRAINT PK_Follow PRIMARY KEY (follower_id, following_id),
    CONSTRAINT follow_ibfk_1 FOREIGN KEY (follower_id)  REFERENCES Profile(profile_id),
    CONSTRAINT follow_ibfk_2 FOREIGN KEY (following_id) REFERENCES Profile(profile_id)
) ENGINE=InnoDB;

-- Table for blocking users (matches live DB: separate id PK and User references)
CREATE TABLE Block
(
    id          INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    blocker_id  INT NOT NULL,
    blocked_id  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT block_ibfk_1 FOREIGN KEY (blocker_id) REFERENCES User(user_id) ON DELETE CASCADE,
    CONSTRAINT block_ibfk_2 FOREIGN KEY (blocked_id) REFERENCES User(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Helpful indexes (MySQL will auto-create for FKs as needed, but we keep intent clear)
CREATE INDEX fk_profile_user_idx        ON Profile(user_id);
CREATE INDEX fk_post_user_idx           ON Post(user_id);
CREATE INDEX fk_comment_post_idx        ON Comment(post_id);
CREATE INDEX fk_comment_user_idx        ON Comment(user_id);
CREATE INDEX fk_comment_parent_idx      ON Comment(parent_id);
CREATE INDEX fk_like_post_idx           ON `Like`(post_id);
CREATE INDEX fk_like_user_idx           ON `Like`(user_id);
CREATE INDEX fk_reaction_user_idx       ON Reaction(user_id);
CREATE INDEX fk_reaction_comment_idx    ON Reaction(comment_id);
CREATE INDEX fk_notification_user_idx   ON Notification(user_id);
CREATE INDEX fk_notification_post_idx   ON Notification(post_id);
CREATE INDEX follow_following_idx       ON Follow(following_id);
CREATE INDEX block_blocker_idx          ON Block(blocker_id);
CREATE INDEX block_blocked_idx          ON Block(blocked_id);

-- ==============================
-- Triggers
-- ==============================

-- Keep Profile.display_name in sync with User.username
DROP TRIGGER IF EXISTS profile_bi_set_display_name;
DELIMITER $$
CREATE TRIGGER profile_bi_set_display_name
BEFORE INSERT ON Profile
FOR EACH ROW
BEGIN
  IF NEW.display_name IS NULL OR NEW.display_name = '' THEN
    DECLARE v_username VARCHAR(100);
    SELECT u.username INTO v_username
    FROM User u
    WHERE u.user_id = NEW.user_id
    LIMIT 1;
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

-- Followers counter maintenance on Follow
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

-- Posts counter maintenance on Post
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