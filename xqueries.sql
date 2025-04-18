ALTER TABLE `users` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_meta` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_meta` MODIFY `user_id` int(11) UNSIGNED NOT NULL;

ALTER TABLE `user_meta` ADD CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


CREATE TABLE `post_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL,
  `post_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `post_date_gmt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_fetch` int,
  `number_art` int DEFAULT 0,
  `post_category` bigint(20) unsigned NOT NULL,
  `post_lang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_show_pic` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'on',
  `book_without_pdf` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'off',
  `post_source_1` text COLLATE utf8mb4_unicode_ci,
  `post_source_2` text COLLATE utf8mb4_unicode_ci,
  `post_fetch_url` text COLLATE utf8mb4_unicode_ci,
  `post_in` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trusted',
  `post_keywords` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `type_status_lang_in_info` (`id`,`post_type`,`post_status`,`post_lang`,`post_in`) USING BTREE,
  KEY `post_author` (`post_author`),
  KEY `post_category` (`post_category`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `post_info` ADD UNIQUE (`post_fetch_url`(555));

ALTER TABLE `posts` ADD `info_id` bigint(20) unsigned DEFAULT NULL;

CREATE TABLE `post_info_program` (
  `id` bigint(20) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `post_info_id` bigint(20) unsigned NOT NULL,
  `program` TEXT NOT NULL,
  FOREIGN KEY(`post_info_id`) REFERENCES `post_info`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `short_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_url` text NOT NULL,
  `short_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(155) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `book_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `is_author` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(`author_id`) REFERENCES `authors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `translators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(155) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `book_translator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `is_translator` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(`translator_id`) REFERENCES `translators`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE posts ADD `reviewed` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'off' AFTER post_share;

-- new

CREATE TABLE `boot_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_name` varchar(155) NOT NULL,
  `comment_lang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `boot_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(255) NOT NULL,
  `permission_value_ar` TEXT NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `boot_permissions` SET `permission_key` = "follow_accounts", `permission_value_ar` = "متابعة لحسابات الاعضاء";
INSERT INTO `boot_permissions` SET `permission_key` = "add_comments", `permission_value_ar` = "اضافة تعليقات لكل موضوع";
INSERT INTO `boot_permissions` SET `permission_key` = "add_reviews", `permission_value_ar` = "اضافة تقييمات للمواضيع";
INSERT INTO `boot_permissions` SET `permission_key` = "add_previews", `permission_value_ar` = "اضافة مشاهدات للمواضيع";
INSERT INTO `boot_permissions` SET `permission_key` = "books_and_subject_tools", `permission_value_ar` = "مشاهدة و تحميل و معاينة واستعماع للكتاب والموضوع";
INSERT INTO `boot_permissions` SET `permission_key` = "comment_execept", `permission_value_ar` = "التعليق بالحكم الموجودة في الموقع";
INSERT INTO `boot_permissions` SET `permission_key` = "comment_events", `permission_value_ar` = "التعليق بالاحداث الموجودة في الموقع";

CREATE TABLE `boots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(155) NOT NULL,
  `lang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cats` TEXT NOT NULL,
  `users_family` TEXT NOT NULL,
  `permissions` TEXT NOT NULL,
  `stat` tinyint DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `boot_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_key` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `boot_id` int(11) NOT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `boot_id` (`boot_id`),
  KEY `meta_key` (`meta_key`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

