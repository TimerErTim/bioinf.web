-- WEB4 PHP Team 4, Game of Thrones Quotes
-- Database: team_4
-- Default admin: username admin, password admin

CREATE DATABASE IF NOT EXISTS `team_4`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `team_4`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `quotes`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
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
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_quote_id` (`quote_id`),
  KEY `idx_comments_user_id` (`user_id`),
  CONSTRAINT `fk_comments_quote`
    FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password: admin
INSERT INTO `users` (`id`, `username`, `password_hash`, `is_admin`, `created_at`) VALUES
(1, 'admin', '$2y$10$pMZjvrhbki/X9jkwsa.tweAK5SInkoG6RTMRT684JNMbR0m/IAne6', 1, '2026-01-15 10:00:00');

-- Password: password123
INSERT INTO `users` (`id`, `username`, `password_hash`, `is_admin`, `created_at`) VALUES
(2, 'tyrion_fan', '$2y$10$2OBUC0AU2AH1VSVrI9UyvOfrvwdHKWqMLMbjv95V.0w7xvQxsjU0a', 0, '2026-02-01 12:00:00'),
(3, 'arya_fan', '$2y$10$2OBUC0AU2AH1VSVrI9UyvOfrvwdHKWqMLMbjv95V.0w7xvQxsjU0a', 0, '2026-02-10 14:30:00');

INSERT INTO `quotes` (`id`, `text`, `speaker`, `season`, `episode`) VALUES
(1, 'When you play the game of thrones, you win or you die. There is no middle ground.', 'Cersei Lannister', 1, 7),
(2, 'The night is dark and full of terrors.', 'Melisandre', 2, 1),
(3, 'A Lannister always pays his debts.', 'Tyrion Lannister', 1, 5),
(4, 'Winter is coming.', 'Eddard Stark', 1, 1),
(5, 'Hold the door!', 'Hodor', 6, 5),
(6, 'Any man who must say "I am the king" is no true king.', 'Tywin Lannister', 3, 10),
(7, 'Chaos isn''t a pit. Chaos is a ladder.', 'Petyr Baelish', 3, 6),
(8, 'The North remembers.', 'Lyanna Mormont', 7, 1),
(9, 'What do we say to the God of Death? Not today.', 'Syrio Forel', 1, 8),
(10, 'I drink and I know things.', 'Tyrion Lannister', 6, 2),
(11, 'Never forget what you are. The rest of the world will not. Wear it like armor, and it can never be used to hurt you.', 'Tyrion Lannister', 1, 1),
(12, 'Power is power.', 'Cersei Lannister', 2, 3);

INSERT INTO `comments` (`quote_id`, `user_id`, `content`, `created_at`) VALUES
(1, 2, 'One of the most iconic lines in the entire series!', '2026-03-01 09:15:00'),
(1, 3, 'Cersei was absolutely ruthless here.', '2026-03-01 10:22:00'),
(2, 2, 'Still gives me chills every time.', '2026-03-02 11:00:00'),
(3, 3, 'House motto turned meme, love it.', '2026-03-03 08:45:00'),
(4, 2, 'The Starks warned everyone and nobody listened.', '2026-03-04 16:30:00'),
(5, 3, 'I am not crying, you are crying.', '2026-03-05 19:00:00'),
(7, 2, 'Littlefinger''s philosophy in a nutshell.', '2026-03-06 12:10:00'),
(7, 3, 'This quote aged perfectly for his character arc.', '2026-03-06 13:00:00'),
(9, 2, 'Arya training flashback, perfect moment.', '2026-03-07 07:30:00'),
(10, 3, 'Tyrion''s best line delivery, fight me.', '2026-03-08 21:15:00'),
(11, 2, 'Life advice from the Imp.', '2026-03-09 14:00:00'),
(12, 3, 'Short and terrifying. Classic Cersei.', '2026-03-10 18:45:00'),
(4, 3, 'House Stark deserved better.', '2026-03-11 10:00:00'),
(8, 2, 'Lyanna was a legend.', '2026-03-12 11:30:00'),
(10, 2, 'Second comment on this quote, deserved.', '2026-03-13 09:00:00');
