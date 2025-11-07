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
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    DOB DATE,
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE
);


CREATE TABLE Profile
(
    profile_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    display_name VARCHAR(100),
    profile_picture VARCHAR(255),
    bio TEXT,
    followers INT DEFAULT 0,
    posts INT DEFAULT 0,
    is_private TINYINT(1) DEFAULT 0 COMMENT '0 = public, 1 = private',
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE
);


CREATE TABLE Post
(
    post_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    text TEXT,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- auto-update timestamp
    is_public TINYINT(1) DEFAULT 1, -- 1 = public, 0 = private
    is_sticky BOOLEAN DEFAULT FALSE,
    tags VARCHAR(255) DEFAULT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE
);


CREATE TABLE Comment
(
    comment_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    parent_id INT,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES Comment (comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE
);


CREATE TABLE `Like`
(
    like_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE
);


CREATE TABLE Reaction
(
    reaction_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    reaction_type VARCHAR(20) NOT NULL COMMENT 'LIKE or DISLIKE',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    comment_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES Comment (comment_id) ON DELETE CASCADE
);


CREATE TABLE Notification
(
    notification_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    post_id INT,
    user_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES Post (post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User (user_id) ON DELETE CASCADE
);


-- Junction table for M:N relationship between profiles (followers/following)
CREATE TABLE Follow
(
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    CONSTRAINT PK_Follow PRIMARY KEY (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES Profile(profile_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES Profile(profile_id) ON DELETE CASCADE
);


-- Table for blocking users
CREATE TABLE Block
(
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Block PRIMARY KEY (blocker_id, blocked_id),
    FOREIGN KEY (blocker_id) REFERENCES Profile(profile_id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES Profile(profile_id) ON DELETE CASCADE
);