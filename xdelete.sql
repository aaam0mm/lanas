DELETE FROM posts WHERE id > 0;
SET @num := 0;
UPDATE posts SET id = @num:= @num + 1;
ALTER TABLE posts AUTO_INCREMENT = 1;
SELECT * FROM posts;

DELETE FROM post_info WHERE id > 0;
SET @num := 0;
UPDATE post_info SET id = @num:= @num + 1;
ALTER TABLE post_info AUTO_INCREMENT = 1;
SELECT * FROM post_info;

DELETE FROM users WHERE id > 0;
SET @num := 0;
UPDATE users SET id = @num:= @num + 1;
ALTER TABLE users AUTO_INCREMENT = 1;
SELECT * FROM users;

TRUNCATE posts;TRUNCATE post_category;TRUNCATE post_meta;TRUNCATE post_slices;UPDATE post_info SET number_art = 0;

DELETE FROM posts WHERE info_id IS NOT NULL;
DELETE FROM post_category
WHERE post_id NOT IN (SELECT id FROM posts);
DELETE FROM post_meta
WHERE post_id NOT IN (SELECT id FROM posts);
DELETE FROM post_slices
WHERE post_id NOT IN (SELECT id FROM posts);
UPDATE post_info SET number_art = 0;
