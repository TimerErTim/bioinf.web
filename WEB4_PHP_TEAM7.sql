-- WEB4 PHP Team 7, Game of Thrones Quotes (Forum / REST)
-- Database: team_7
-- Default admin: username admin, password admin

CREATE DATABASE IF NOT EXISTS `team_7`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `team_7`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `quote_likes`;
DROP TABLE IF EXISTS `quotes`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `avatar_path` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  `speaker` VARCHAR(100) NOT NULL,
  `season` TINYINT UNSIGNED DEFAULT NULL,
  `episode` TINYINT UNSIGNED DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quote_likes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `quote_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quote_likes_user_quote` (`user_id`, `quote_id`),
  KEY `idx_quote_likes_quote_id` (`quote_id`),
  CONSTRAINT `fk_quote_likes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quote_likes_quote`
    FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_quote_id` (`quote_id`),
  KEY `idx_comments_user_id` (`user_id`),
  KEY `idx_comments_parent_id` (`parent_id`),
  CONSTRAINT `fk_comments_quote`
    FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_comments_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password: admin
INSERT INTO `users` (`id`, `username`, `password_hash`, `is_admin`, `avatar_path`, `created_at`) VALUES
(1, 'admin', '$2y$10$pMZjvrhbki/X9jkwsa.tweAK5SInkoG6RTMRT684JNMbR0m/IAne6', 1, NULL, '2026-01-15 10:00:00');

-- Password: password123
INSERT INTO `users` (`id`, `username`, `password_hash`, `is_admin`, `avatar_path`, `created_at`) VALUES
(2, 'tyrion_fan', '$2y$10$2OBUC0AU2AH1VSVrI9UyvOfrvwdHKWqMLMbjv95V.0w7xvQxsjU0a', 0, NULL, '2026-02-01 12:00:00'),
(3, 'arya_fan', '$2y$10$2OBUC0AU2AH1VSVrI9UyvOfrvwdHKWqMLMbjv95V.0w7xvQxsjU0a', 0, NULL, '2026-02-10 14:30:00');

INSERT INTO `quotes` (`id`, `text`, `speaker`, `season`, `episode`, `image_path`) VALUES
(1, 'When you play the game of thrones, you win or you die. There is no middle ground.', 'Cersei Lannister', 1, 7, NULL),
(2, 'The night is dark and full of terrors.', 'Melisandre', 2, 1, NULL),
(3, 'A Lannister always pays his debts.', 'Tyrion Lannister', 1, 5, NULL),
(4, 'Winter is coming.', 'Eddard Stark', 1, 1, NULL),
(5, 'Hold the door!', 'Hodor', 6, 5, NULL),
(6, 'Any man who must say "I am the king" is no true king.', 'Tywin Lannister', 3, 10, NULL),
(7, 'Chaos isn''t a pit. Chaos is a ladder.', 'Petyr Baelish', 3, 6, NULL),
(8, 'The North remembers.', 'Lyanna Mormont', 7, 1, NULL),
(9, 'What do we say to the God of Death? Not today.', 'Syrio Forel', 1, 8, NULL),
(10, 'I drink and I know things.', 'Tyrion Lannister', 6, 2, NULL),
(11, 'Never forget what you are. The rest of the world will not. Wear it like armor, and it can never be used to hurt you.', 'Tyrion Lannister', 1, 1, NULL),
(12, 'Power is power.', 'Cersei Lannister', 2, 3, NULL);

INSERT INTO `comments` (`id`, `quote_id`, `user_id`, `parent_id`, `content`, `created_at`) VALUES
(1, 1, 2, NULL, 'One of the most iconic lines in the entire series!', '2026-03-01 09:15:00'),
(2, 1, 3, 1, 'Cersei was absolutely ruthless here.', '2026-03-01 10:22:00'),
(3, 2, 2, NULL, 'Still gives me chills every time.', '2026-03-02 11:00:00'),
(4, 3, 3, NULL, 'House motto turned meme, love it.', '2026-03-03 08:45:00'),
(5, 4, 2, NULL, 'The Starks warned everyone and nobody listened.', '2026-03-04 16:30:00'),
(6, 5, 3, NULL, 'I am not crying, you are crying.', '2026-03-05 19:00:00'),
(7, 7, 2, NULL, 'Littlefinger''s philosophy in a nutshell.', '2026-03-06 12:10:00'),
(8, 7, 3, 7, 'This quote aged perfectly for his character arc.', '2026-03-06 13:00:00'),
(9, 9, 2, NULL, 'Arya training flashback, perfect moment.', '2026-03-07 07:30:00'),
(10, 10, 3, NULL, 'Tyrion''s best line delivery, fight me.', '2026-03-08 21:15:00'),
(11, 11, 2, NULL, 'Life advice from the Imp.', '2026-03-09 14:00:00'),
(12, 12, 3, NULL, 'Short and terrifying. Classic Cersei.', '2026-03-10 18:45:00'),
(13, 4, 3, 5, 'House Stark deserved better.', '2026-03-11 10:00:00'),
(14, 8, 2, NULL, 'Lyanna was a legend.', '2026-03-12 11:30:00'),
(15, 10, 2, 10, 'Second comment on this quote, deserved.', '2026-03-13 09:00:00'),
(16, 1, 2, 2, 'And she meant every word of it.', '2026-03-14 08:00:00'),
(17, 7, 3, 8, 'The ladder metaphor is still brilliant.', '2026-03-15 12:00:00');

INSERT INTO `quote_likes` (`user_id`, `quote_id`, `created_at`) VALUES
(2, 1, '2026-03-01 09:00:00'),
(2, 7, '2026-03-06 12:00:00'),
(2, 10, '2026-03-08 20:00:00'),
(2, 4, '2026-03-04 16:00:00'),
(3, 1, '2026-03-01 11:00:00'),
(3, 5, '2026-03-05 19:30:00'),
(3, 7, '2026-03-06 13:30:00'),
(3, 10, '2026-03-08 21:00:00'),
(3, 12, '2026-03-10 19:00:00'),
(1, 4, '2026-03-04 17:00:00'),
(1, 9, '2026-03-07 08:00:00');
