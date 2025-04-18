-- 1. Check for the highest id value in the users table
SELECT MAX(id) FROM users;

-- 2. Set the AUTO_INCREMENT starting point to a value higher than the current maximum id
-- Suppose the highest id is 1000, we will set the next AUTO_INCREMENT to 1001
ALTER TABLE `users` AUTO_INCREMENT = 11;

-- 3. Modify the id column to make it AUTO_INCREMENT
ALTER TABLE `users` MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 4. Modify the user_id column in the user_meta table to ensure consistency with users(id)
ALTER TABLE `user_meta` MODIFY `user_id` INT(11) UNSIGNED NOT NULL;

-- 5. Add the foreign key constraint on user_id in user_meta
ALTER TABLE `user_meta` 
ADD CONSTRAINT `user_id_fk` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;
