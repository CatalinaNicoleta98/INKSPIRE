-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql116.unoeuro.com
-- Generation Time: Dec 09, 2025 at 11:49 AM
-- Server version: 8.4.6-6
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catalinavrinceanu_com_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `About`
--

CREATE TABLE `About` (
  `id` int NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `About`
--

INSERT INTO `About` (`id`, `content`, `updated_at`) VALUES
(1, 'A warm and inspiring corner for readers, book lovers, and creative hobbyists. Share your favorite reads, cozy reading spots, beautiful quotes, impressions, and anything that sparks your imagination. Happy posting!', '2025-12-09 11:31:43');

-- --------------------------------------------------------

--
-- Table structure for table `Block`
--

CREATE TABLE `Block` (
  `id` int NOT NULL,
  `blocker_id` int NOT NULL,
  `blocked_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Comment`
--

CREATE TABLE `Comment` (
  `comment_id` int NOT NULL,
  `text` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Comment`
--

INSERT INTO `Comment` (`comment_id`, `text`, `created_at`, `updated_at`, `parent_id`, `user_id`, `post_id`) VALUES
(348, 'works?', '2025-12-09 12:23:16', NULL, NULL, 19, 43);

--
-- Triggers `Comment`
--
DELIMITER $$
CREATE TRIGGER `comment_ai_create_notification` AFTER INSERT ON `Comment` FOR EACH ROW BEGIN
  DECLARE postOwner   INT;
  DECLARE parentOwner INT;
  DECLARE receiver    INT;

  -- owner of the post
  SELECT user_id INTO postOwner
  FROM Post
  WHERE post_id = NEW.post_id
  LIMIT 1;

  -- owner of parent comment if this is a reply
  IF NEW.parent_id IS NOT NULL THEN
    SELECT user_id INTO parentOwner
    FROM Comment
    WHERE comment_id = NEW.parent_id
    LIMIT 1;
  ELSE
    SET parentOwner = NULL;
  END IF;

  -- decide who should receive the notification
  IF NEW.parent_id IS NOT NULL THEN
    SET receiver = parentOwner; -- reply to someone’s comment
  ELSE
    SET receiver = postOwner;   -- new comment on the post
  END IF;

  -- create notification if receiver exists and is not the same as actor
  IF receiver IS NOT NULL AND receiver <> NEW.user_id THEN
    INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
    VALUES (
      CASE 
        WHEN NEW.parent_id IS NOT NULL THEN 'reply'
        ELSE 'comment'
      END,
      NEW.post_id,
      NEW.comment_id,
      receiver,
      NEW.user_id,
      0
    );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Follow`
--

CREATE TABLE `Follow` (
  `follower_id` int NOT NULL,
  `following_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Follow`
--

INSERT INTO `Follow` (`follower_id`, `following_id`) VALUES
(20, 19),
(19, 20);

--
-- Triggers `Follow`
--
DELIMITER $$
CREATE TRIGGER `follow_ad_update_followers` AFTER DELETE ON `Follow` FOR EACH ROW BEGIN
  UPDATE Profile
  SET followers = GREATEST(followers - 1, 0)
  WHERE profile_id = OLD.following_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `follow_ai_create_notification` AFTER INSERT ON `Follow` FOR EACH ROW BEGIN
  DECLARE followerUserId  INT;
  DECLARE followingUserId INT;

  -- Map profiles to users
  SELECT user_id INTO followerUserId
  FROM Profile
  WHERE profile_id = NEW.follower_id
  LIMIT 1;

  SELECT user_id INTO followingUserId
  FROM Profile
  WHERE profile_id = NEW.following_id
  LIMIT 1;

  -- Avoid self-follow notifications (just in case)
  IF followerUserId IS NOT NULL
     AND followingUserId IS NOT NULL
     AND followerUserId <> followingUserId THEN

    INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
    VALUES ('follow', NULL, NULL, followingUserId, followerUserId, 0);
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `follow_ai_update_followers` AFTER INSERT ON `Follow` FOR EACH ROW BEGIN
  UPDATE Profile
  SET followers = followers + 1
  WHERE profile_id = NEW.following_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Like`
--

CREATE TABLE `Like` (
  `like_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Like`
--

INSERT INTO `Like` (`like_id`, `created_at`, `user_id`, `post_id`) VALUES
(66, '2025-12-09 12:23:12', 19, 43);

--
-- Triggers `Like`
--
DELIMITER $$
CREATE TRIGGER `like_ai_create_notification` AFTER INSERT ON `Like` FOR EACH ROW BEGIN
  DECLARE postOwner INT;

  -- Who owns the liked post?
  SELECT user_id INTO postOwner
  FROM Post
  WHERE post_id = NEW.post_id
  LIMIT 1;

  -- Don’t notify if user likes their own post
  IF postOwner IS NOT NULL AND postOwner <> NEW.user_id THEN
    INSERT INTO Notification (type, post_id, comment_id, user_id, actor_id, is_read)
    VALUES ('like', NEW.post_id, NULL, postOwner, NEW.user_id, 0);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Notification`
--

CREATE TABLE `Notification` (
  `notification_id` int NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `post_id` int DEFAULT NULL,
  `comment_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `actor_id` int NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Notification`
--

INSERT INTO `Notification` (`notification_id`, `type`, `post_id`, `comment_id`, `user_id`, `actor_id`, `is_read`, `created_at`) VALUES
(74, 'follow', NULL, NULL, 19, 20, 1, '2025-12-09 12:18:20'),
(75, 'follow', NULL, NULL, 20, 19, 1, '2025-12-09 12:18:29'),
(78, 'like', 45, NULL, 19, 20, 1, '2025-12-09 12:18:49'),
(80, 'like', 44, NULL, 20, 19, 1, '2025-12-09 12:19:17'),
(82, 'like', 43, NULL, 20, 19, 1, '2025-12-09 12:19:23'),
(83, 'like', 43, NULL, 20, 19, 1, '2025-12-09 12:23:12'),
(84, 'comment', 43, 348, 20, 19, 1, '2025-12-09 12:23:16'),
(85, 'follow', NULL, NULL, 20, 19, 1, '2025-12-09 12:24:02'),
(86, 'follow', NULL, NULL, 19, 20, 1, '2025-12-09 12:24:22');

-- --------------------------------------------------------

--
-- Table structure for table `Post`
--

CREATE TABLE `Post` (
  `post_id` int NOT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_public` tinyint(1) DEFAULT '1',
  `is_sticky` tinyint(1) DEFAULT '0',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Post`
--

INSERT INTO `Post` (`post_id`, `title`, `description`, `image_url`, `created_at`, `updated_at`, `is_public`, `is_sticky`, `tags`, `user_id`) VALUES
(43, 'test for nmotifications', 'works?', 'uploads/1765278965_1760519678_beautiful-office-space-cartoon-style.jpg', '2025-12-09 12:16:05', '2025-12-09 12:16:05', 1, 0, '', 20),
(44, 'test for private and follow!', 'fveF', 'uploads/1765278980_1761827308_wp7539662-macbook-autumn-wallpapers.jpg', '2025-12-09 12:16:21', '2025-12-09 12:17:55', 0, 0, '', 20),
(45, 'test for notifications', 'test for me', 'uploads/1765279016_1764159008_sheng-l-q2dUSl9S4Xg-unsplash.jpg', '2025-12-09 12:16:56', '2025-12-09 12:16:56', 1, 0, '', 19);

--
-- Triggers `Post`
--
DELIMITER $$
CREATE TRIGGER `post_ad_update_post_count` AFTER DELETE ON `Post` FOR EACH ROW BEGIN
  UPDATE Profile
  SET posts = GREATEST(posts - 1, 0)
  WHERE user_id = OLD.user_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `post_ai_update_post_count` AFTER INSERT ON `Post` FOR EACH ROW BEGIN
  UPDATE Profile
  SET posts = posts + 1
  WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Profile`
--

CREATE TABLE `Profile` (
  `profile_id` int NOT NULL,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `followers` int DEFAULT '0',
  `posts` int DEFAULT '0',
  `is_private` tinyint(1) DEFAULT '0' COMMENT '0 = public, 1 = private',
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Profile`
--

INSERT INTO `Profile` (`profile_id`, `display_name`, `profile_picture`, `bio`, `followers`, `posts`, `is_private`, `user_id`) VALUES
(19, '', 'uploads/1765278935_1760443376_278567536_2334708726672197_8213144299031809009_n.jpg', 'hi!', 1, 1, 0, 19),
(20, '', 'uploads/1765278949_1760519678_beautiful-office-space-cartoon-style.jpg', 'hello!', 1, 2, 1, 20);

-- --------------------------------------------------------

--
-- Table structure for table `Reaction`
--

CREATE TABLE `Reaction` (
  `reaction_id` int NOT NULL,
  `reaction_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'LIKE or DISLIKE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `comment_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` int NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`id`, `content`, `updated_at`) VALUES
(1, 'Welcome to Inkspire, a social photo-sharing platform where users can upload, explore, and interact with visual content. By creating an account or using the platform, you agree to the following terms:\r\n\r\n1. Acceptance of Terms\r\nBy registering on Inkspire, you confirm that you are at least 14 years old, have read and agreed to these Terms & Conditions, and will follow all rules outlined below. If you do not agree, you must not use the platform.\r\n\r\n2. User Responsibilities\r\nYou agree that you will not:\r\n\r\n• Upload illegal, harmful, hateful, or explicit content.\r\n• Harass, bully, impersonate, or abuse other users.\r\n• Attempt to hack, exploit, or disrupt the platform.\r\n• Create multiple accounts for malicious purposes.\r\n• Post content that violates copyright or belongs to someone else without permission.\r\nYou are responsible for all activity on your account.\r\n\r\n3. Content Ownership\r\nYou retain ownership of any photos or content you upload. However, by posting on Inkspire, you grant the platform a non-exclusive license to display your content within the app. You may delete your posts at any time.\r\n\r\n4. Moderation & Account Actions\r\nInkspire reserves the right to remove content that violates these terms, temporarily or permanently block users who break the rules, and restrict features if needed for safety or system stability.\r\n\r\n5. Privacy\r\nInkspire stores basic account information such as your username, email address, profile details, uploaded images, and your interactions (likes, comments, follows). This information is used solely for platform functionality and is not shared externally.\r\n\r\n6. Security\r\nYou agree to keep your password secure and not share access to your account. Inkspire uses reasonable security measures but cannot guarantee complete protection from all threats.\r\n\r\n7. Limitation of Liability\r\nInkspire is a school project and is provided “as is.” The platform and its developer(s) are not responsible for data loss, downtime, errors, or damages resulting from use of the platform or from user-generated content.\r\n\r\n8. Changes to These Terms\r\nInkspire may update these Terms & Conditions at any time. Continued use of the platform means you accept the updated terms.\r\n\r\n9. Contact\r\nFor questions or issues, please contact the developer directly.', '2025-12-09 11:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `user_id` int NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DOB` date DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`user_id`, `first_name`, `last_name`, `email`, `username`, `password`, `DOB`, `is_admin`, `is_active`, `created_at`, `reset_token`, `reset_expires`) VALUES
(19, 'Catalina Nicoleta', 'Vrinceanu', 'vrinceanu.catalina98@gmail.com', 'catalina98', '$2y$10$rfsKT9lPfa0o8NIYgGZV8uP2Fk.iZ7TT/qEugRIyxPUfVrNi2OaFi', '1996-07-17', 1, 1, '2025-12-09 12:14:06', NULL, NULL),
(20, 'Catalina Nicoleta', 'Vrinceanu', 'cnv98@yahoo.com', 'kate', '$2y$10$2sFxN6UBfF8vbtx.jkdHA.fX6Grb1t7PMaLz6hqerb3jL37fomw.m', '1995-06-17', 0, 1, '2025-12-09 12:14:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_new_posts_today`
-- (See below for the actual view)
--
CREATE TABLE `view_new_posts_today` (
`new_posts_today` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_new_users_today`
-- (See below for the actual view)
--
CREATE TABLE `view_new_users_today` (
`new_users_today` bigint
);

-- --------------------------------------------------------

--
-- Structure for view `view_new_posts_today`
--
DROP TABLE IF EXISTS `view_new_posts_today`;

CREATE ALGORITHM=UNDEFINED DEFINER=`catalinavrinceanu_com`@`%` SQL SECURITY DEFINER VIEW `view_new_posts_today`  AS SELECT count(0) AS `new_posts_today` FROM `Post` WHERE (cast(`Post`.`created_at` as date) = curdate()) ;

-- --------------------------------------------------------

--
-- Structure for view `view_new_users_today`
--
DROP TABLE IF EXISTS `view_new_users_today`;

CREATE ALGORITHM=UNDEFINED DEFINER=`catalinavrinceanu_com`@`%` SQL SECURITY DEFINER VIEW `view_new_users_today`  AS SELECT count(0) AS `new_users_today` FROM `User` WHERE (cast(`User`.`created_at` as date) = curdate()) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `About`
--
ALTER TABLE `About`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Block`
--
ALTER TABLE `Block`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blocker_id` (`blocker_id`),
  ADD KEY `blocked_id` (`blocked_id`);

--
-- Indexes for table `Comment`
--
ALTER TABLE `Comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comment_post_created_at` (`post_id`,`created_at`);

--
-- Indexes for table `Follow`
--
ALTER TABLE `Follow`
  ADD PRIMARY KEY (`follower_id`,`following_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indexes for table `Like`
--
ALTER TABLE `Like`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notification_user_is_read_created_at` (`user_id`,`is_read`,`created_at`),
  ADD KEY `fk_notification_post` (`post_id`),
  ADD KEY `fk_notification_comment` (`comment_id`),
  ADD KEY `fk_notification_actor` (`actor_id`);

--
-- Indexes for table `Post`
--
ALTER TABLE `Post`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_post_user_created_at` (`user_id`,`created_at`);

--
-- Indexes for table `Profile`
--
ALTER TABLE `Profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Reaction`
--
ALTER TABLE `Reaction`
  ADD PRIMARY KEY (`reaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `About`
--
ALTER TABLE `About`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Block`
--
ALTER TABLE `Block`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `Comment`
--
ALTER TABLE `Comment`
  MODIFY `comment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `Like`
--
ALTER TABLE `Like`
  MODIFY `like_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `Notification`
--
ALTER TABLE `Notification`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `Post`
--
ALTER TABLE `Post`
  MODIFY `post_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `Profile`
--
ALTER TABLE `Profile`
  MODIFY `profile_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Reaction`
--
ALTER TABLE `Reaction`
  MODIFY `reaction_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Block`
--
ALTER TABLE `Block`
  ADD CONSTRAINT `Block_ibfk_1` FOREIGN KEY (`blocker_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Block_ibfk_2` FOREIGN KEY (`blocked_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Comment`
--
ALTER TABLE `Comment`
  ADD CONSTRAINT `Comment_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `Comment` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Comment_ibfk_3` FOREIGN KEY (`post_id`) REFERENCES `Post` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `Follow`
--
ALTER TABLE `Follow`
  ADD CONSTRAINT `Follow_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `Profile` (`profile_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Follow_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `Profile` (`profile_id`) ON DELETE CASCADE;

--
-- Constraints for table `Like`
--
ALTER TABLE `Like`
  ADD CONSTRAINT `Like_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Like_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `Post` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `Notification`
--
ALTER TABLE `Notification`
  ADD CONSTRAINT `fk_notification_actor` FOREIGN KEY (`actor_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_actor_cascade` FOREIGN KEY (`actor_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_comment` FOREIGN KEY (`comment_id`) REFERENCES `Comment` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_comment_cascade` FOREIGN KEY (`comment_id`) REFERENCES `Comment` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_post` FOREIGN KEY (`post_id`) REFERENCES `Post` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Notification_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Post` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Notification_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Post`
--
ALTER TABLE `Post`
  ADD CONSTRAINT `Post_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Profile`
--
ALTER TABLE `Profile`
  ADD CONSTRAINT `Profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Reaction`
--
ALTER TABLE `Reaction`
  ADD CONSTRAINT `Reaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Reaction_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `Comment` (`comment_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
