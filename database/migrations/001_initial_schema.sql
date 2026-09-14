-- ElectroMusicCR - esquema inicial para electromusiccrdb
-- Ejecutar con un usuario MySQL de privilegios mínimos.

CREATE DATABASE IF NOT EXISTS `electromusiccrdb`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `electromusiccrdb`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(80) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'usuario',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_username` (`username`),
    KEY `idx_users_role_active` (`role`, `is_active`),
    CONSTRAINT `chk_users_role` CHECK (`role` IN ('usuario', 'colaborador', 'admin'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trivia` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(160) NOT NULL,
    `starts_at` DATETIME NOT NULL,
    `ends_at` DATETIME NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_trivia_status_dates` (`status`, `starts_at`, `ends_at`),
    CONSTRAINT `fk_trivia_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `chk_trivia_status` CHECK (`status` IN ('draft', 'active', 'closed')),
    CONSTRAINT `chk_trivia_dates` CHECK (`ends_at` IS NULL OR `ends_at` >= `starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trivia_questions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `trivia_id` BIGINT UNSIGNED NOT NULL,
    `question_text` VARCHAR(500) NOT NULL,
    `question_date` DATE NOT NULL,
    `points` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `position` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_trivia_question_date` (`trivia_id`, `question_date`),
    KEY `idx_questions_date` (`question_date`),
    CONSTRAINT `fk_questions_trivia` FOREIGN KEY (`trivia_id`) REFERENCES `trivia` (`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_questions_points` CHECK (`points` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trivia_options` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `option_text` VARCHAR(255) NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `position` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_options_question` (`question_id`),
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `trivia_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trivia_attempts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `visitor_token_hash` CHAR(64) NULL,
    `selected_option_id` BIGINT UNSIGNED NOT NULL,
    `is_correct` TINYINT(1) NOT NULL,
    `points_awarded` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attempt_user_question` (`user_id`, `question_id`),
    UNIQUE KEY `uq_attempt_visitor_question` (`visitor_token_hash`, `question_id`),
    KEY `idx_attempts_ranking` (`user_id`, `points_awarded`),
    CONSTRAINT `fk_attempts_question` FOREIGN KEY (`question_id`) REFERENCES `trivia_questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_attempts_option` FOREIGN KEY (`selected_option_id`) REFERENCES `trivia_options` (`id`),
    CONSTRAINT `chk_attempts_identity` CHECK (`user_id` IS NOT NULL OR `visitor_token_hash` IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `publics` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(180) NOT NULL,
    `descriptions` TEXT NOT NULL,
    `fileroute` VARCHAR(500) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_publics_status_updated` (`status`, `updated_at`),
    CONSTRAINT `fk_publics_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `chk_publics_status` CHECK (`status` IN ('draft', 'pending', 'published', 'rejected'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
