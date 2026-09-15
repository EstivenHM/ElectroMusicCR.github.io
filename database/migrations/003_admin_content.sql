-- Contenido administrable para novedades, eventos y configuración extendida de trivia.
USE `electromusiccrdb`;

ALTER TABLE `users`
    ADD COLUMN `display_name` VARCHAR(120) NULL AFTER `username`;

CREATE TABLE IF NOT EXISTS `news` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(180) NOT NULL,
    `description` TEXT NOT NULL,
    `image_path` VARCHAR(500) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_status_created` (`status`, `created_at`),
    CONSTRAINT `fk_news_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `chk_news_status` CHECK (`status` IN ('draft', 'published', 'archived'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(180) NOT NULL,
    `description` TEXT NOT NULL,
    `image_path` VARCHAR(500) NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME NULL,
    `location` VARCHAR(180) NOT NULL,
    `ticket_url` VARCHAR(500) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_events_status_date` (`status`, `event_date`),
    CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `chk_events_status` CHECK (`status` IN ('draft', 'published', 'cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;