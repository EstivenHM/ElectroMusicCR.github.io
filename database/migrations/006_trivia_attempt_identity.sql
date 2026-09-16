-- Trivia: la identidad del jugador queda vinculada a la submission.
-- Requiere database/migrations/002_trivia_players_schedule_ranking.sql.

USE `electromusiccrdb`;

SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'trivia_attempts'
      AND CONSTRAINT_NAME = 'chk_attempts_identity'
);

SET @drop_constraint = IF(
    @constraint_exists > 0,
    'ALTER TABLE `trivia_attempts` DROP CONSTRAINT `chk_attempts_identity`',
    'SELECT 1'
);

PREPARE migration_statement FROM @drop_constraint;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
