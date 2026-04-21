-- Adminer 4.8.1 MySQL 8.0.11 dump
SET
  NAMES utf8;

SET
  time_zone = '+00:00';

SET
  foreign_key_checks = 0;

SET
  NAMES utf8mb4;

CREATE TABLE
  `pages` (
    `id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(120) NOT NULL,
    `word` int (6) DEFAULT NULL,
    `translate_type` varchar(20) DEFAULT NULL,
    `cat` varchar(120) DEFAULT NULL,
    `lang` varchar(30) DEFAULT NULL,
    `date` date DEFAULT NULL,
    `user` varchar(120) DEFAULT NULL,
    `pupdate` varchar(120) DEFAULT NULL,
    `target` varchar(120) DEFAULT NULL,
    `add_date` date DEFAULT NULL,
    `deleted` int (11) DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `pages_users` (
    `id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(120) NOT NULL,
    `lang` varchar(30) DEFAULT NULL,
    `user` varchar(120) DEFAULT NULL,
    `pupdate` varchar(120) DEFAULT NULL,
    `target` varchar(120) DEFAULT NULL,
    `add_date` date DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `projects` (
    `g_id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `g_title` varchar(120) NOT NULL,
    PRIMARY KEY (`g_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `qids` (
    `id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(120) NOT NULL,
    `qid` varchar(120) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `qids_others` (
    `id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(120) NOT NULL,
    `qid` varchar(120) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `settings` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `title` text NOT NULL,
    `displayed` text NOT NULL,
    `Type` text NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `translate_type` (
    `tt_id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `tt_title` varchar(120) NOT NULL,
    `tt_lead` int (11) NOT NULL DEFAULT '1',
    `tt_full` int (11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`tt_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `users` (
    `user_id` int (11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `wiki` varchar(255) NOT NULL,
    `user_group` varchar(120) NOT NULL,
    `reg_date` date NOT NULL,
    PRIMARY KEY (`user_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `views` (
    `id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `target` varchar(120) NOT NULL,
    `lang` varchar(30) DEFAULT NULL,
    `countall` int (6) DEFAULT NULL,
    `count2021` int (6) DEFAULT NULL,
    `count2022` int (6) DEFAULT NULL,
    `count2023` int (6) DEFAULT NULL,
    `count2024` int (6) DEFAULT '0',
    `count2025` int (6) DEFAULT '0',
    `count2026` int (6) DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
  `words` (
    `w_id` int (6) unsigned NOT NULL AUTO_INCREMENT,
    `w_title` varchar(120) NOT NULL,
    `w_lead_words` int (6) DEFAULT NULL,
    `w_all_words` int (6) DEFAULT NULL,
    PRIMARY KEY (`w_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE VIEW
  `users_list` AS
SELECT
  `user_id`,
  `username`,
  `wiki`,
  `user_group`,
  `reg_date`
FROM
  `users`;

-- 2024-11-29 22:26:59
