-- Configuracion reutilizable de la Trivia: no se repite al cargar cada cuestionario.
USE `electromusiccrdb`;

CREATE TABLE IF NOT EXISTS `trivia_settings` (
    `id` TINYINT UNSIGNED NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    `description` TEXT NOT NULL,
    `image_path` VARCHAR(500) NULL,
    `updated_by` BIGINT UNSIGNED NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_trivia_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;