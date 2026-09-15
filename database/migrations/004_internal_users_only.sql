-- Reduce users to internal accounts: 1 = administrator, 2 = collaborator.
USE `electromusiccrdb`;

UPDATE `users` SET `role` = '1' WHERE `role` = 'admin';
UPDATE `users` SET `role` = '2' WHERE `role` = 'colaborador';
UPDATE `users` SET `role` = '2' WHERE `role` NOT IN ('1', '2');

ALTER TABLE `users`
    DROP INDEX `idx_users_role_active`;

ALTER TABLE `users`
    DROP CONSTRAINT `chk_users_role`;

ALTER TABLE `users`
    DROP COLUMN `is_active`,
    DROP COLUMN `created_at`;

ALTER TABLE `users`
    CHANGE COLUMN `updated_at` `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `users`
    MODIFY COLUMN `role` TINYINT UNSIGNED NOT NULL;

ALTER TABLE `users`
    ADD KEY `idx_users_role` (`role`);

ALTER TABLE `users`
    ADD CONSTRAINT `chk_users_role` CHECK (`role` IN (1, 2));