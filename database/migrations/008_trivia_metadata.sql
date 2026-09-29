-- Mueve los metadatos de la trivia global a cada registro individual.
-- Requiere database/migrations/005_trivia_settings.sql.

USE `electromusiccrdb`;

ALTER TABLE `trivia`
    ADD COLUMN `description` TEXT NOT NULL DEFAULT '' AFTER `title`,
    ADD COLUMN `image_path` VARCHAR(500) NULL AFTER `description`;

UPDATE `trivia` AS item
INNER JOIN `trivia_settings` AS settings ON settings.id = 1
SET item.title = settings.title,
    item.description = settings.description,
    item.image_path = settings.image_path;


    UPDATE trivia AS item
INNER JOIN trivia_settings AS settings ON settings.id = 1
SET item.title = settings.title,
    item.description = settings.description,
    item.image_path = settings.image_path;