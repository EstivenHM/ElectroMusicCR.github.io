-- ElectroMusicCR - anonymous identity, schedule, attempts, and trivia ranking
-- Requiere database/migrations/001_initial_schema.sql.

USE `electromusiccrdb`;

ALTER TABLE `trivia`
    ADD COLUMN `scheduled_date` DATE NULL AFTER `title`;

UPDATE `trivia`
SET `scheduled_date` = DATE(`starts_at`)
WHERE `scheduled_date` IS NULL;

ALTER TABLE `trivia`
    MODIFY COLUMN `scheduled_date` DATE NOT NULL,
    ADD UNIQUE KEY `uq_trivia_scheduled_date` (`scheduled_date`),
    ADD KEY `idx_trivia_publication` (`status`, `scheduled_date`);

ALTER TABLE `trivia_questions`
    DROP INDEX `uq_trivia_question_date`,
    ADD UNIQUE KEY `uq_trivia_question_position` (`trivia_id`, `position`);

CREATE TABLE IF NOT EXISTS `trivia_players` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nickname` VARCHAR(40) NOT NULL,
    `nickname_normalized` VARCHAR(40) NOT NULL,
    `recovery_code_hash` CHAR(64) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_seen_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_trivia_players_nickname` (`nickname_normalized`),
    KEY `idx_trivia_players_active` (`is_active`, `nickname_normalized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trivia_submissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `trivia_id` BIGINT UNSIGNED NOT NULL,
    `player_id` BIGINT UNSIGNED NOT NULL,
    `attempt_number` TINYINT UNSIGNED NOT NULL,
    `points_total` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status` VARCHAR(20) NOT NULL DEFAULT 'submitted',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_submission_player_trivia_attempt` (`player_id`, `trivia_id`, `attempt_number`),
    KEY `idx_submissions_ranking` (`player_id`, `status`, `points_total`),
    CONSTRAINT `fk_submissions_trivia` FOREIGN KEY (`trivia_id`) REFERENCES `trivia` (`id`),
    CONSTRAINT `fk_submissions_player` FOREIGN KEY (`player_id`) REFERENCES `trivia_players` (`id`),
    CONSTRAINT `chk_submission_attempt_number` CHECK (`attempt_number` IN (1, 2)),
    CONSTRAINT `chk_submission_status` CHECK (`status` IN ('submitted', 'invalidated'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `trivia_attempts`
    DROP INDEX `uq_attempt_user_question`,
    DROP INDEX `uq_attempt_visitor_question`,
    ADD COLUMN `submission_id` BIGINT UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_attempts_submission` (`submission_id`),
    ADD CONSTRAINT `fk_attempts_submission` FOREIGN KEY (`submission_id`) REFERENCES `trivia_submissions` (`id`) ON DELETE CASCADE;

CREATE OR REPLACE VIEW `trivia_ranking` AS
SELECT
    ROW_NUMBER() OVER (ORDER BY `totals`.`points` DESC, `totals`.`nickname` ASC) AS `position`,
    `totals`.`nickname` AS `nickname`,
    `totals`.`points` AS `points`
FROM (
    SELECT
        `player`.`nickname` AS `nickname`,
        COALESCE(SUM(CASE WHEN `submission`.`status` = 'submitted' THEN `submission`.`points_total` ELSE 0 END), 0) AS `points`
    FROM `trivia_players` AS `player`
    LEFT JOIN `trivia_submissions` AS `submission`
        ON `submission`.`player_id` = `player`.`id`
    WHERE `player`.`is_active` = 1
    GROUP BY `player`.`id`, `player`.`nickname`
) AS `totals`;
