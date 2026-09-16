-- Agrega una explicación opcional para mostrar después de resolver la trivia.
-- Requiere database/migrations/001_initial_schema.sql.

USE `electromusiccrdb`;

SET @explanation_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'trivia_questions'
      AND COLUMN_NAME = 'explanation'
);

SET @add_explanation = IF(
    @explanation_exists = 0,
    'ALTER TABLE `trivia_questions` ADD COLUMN `explanation` TEXT NULL AFTER `question_text`',
    'SELECT 1'
);

PREPARE migration_statement FROM @add_explanation;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;