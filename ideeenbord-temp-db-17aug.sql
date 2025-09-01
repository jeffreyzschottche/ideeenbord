-- -------------------------------------------------------------
-- TablePlus 6.6.5(626)
--
-- https://tableplus.com/
--
-- Database: ideeenbord-temp
-- Generation Time: 2025-08-17 14:02:01.0910
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `brand_owners`;
CREATE TABLE `brand_owners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_owner` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_owners_email_unique` (`email`),
  KEY `brand_owners_brand_id_foreign` (`brand_id`),
  CONSTRAINT `brand_owners_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `intro_short` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `socials` json DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `rating_sum` int unsigned NOT NULL DEFAULT '0',
  `rating_count` int unsigned NOT NULL DEFAULT '0',
  `rating` int NOT NULL DEFAULT '0',
  `has_paid` tinyint(1) NOT NULL DEFAULT '0',
  `subscription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_owner_id` bigint unsigned DEFAULT NULL,
  `likes` int NOT NULL DEFAULT '0',
  `dislikes` int NOT NULL DEFAULT '0',
  `quizzes` json DEFAULT NULL,
  `giveaways` json DEFAULT NULL,
  `main_question_id` bigint unsigned DEFAULT NULL,
  `ideas` json DEFAULT NULL,
  `pinned_ideas` json DEFAULT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_email_unique` (`email`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `brands_main_question_id_foreign` (`main_question_id`),
  CONSTRAINT `brands_main_question_id_foreign` FOREIGN KEY (`main_question_id`) REFERENCES `main_questions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cms_fields`;
CREATE TABLE `cms_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('text','image','html','link') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cms_fields_page_id_foreign` (`page_id`),
  CONSTRAINT `cms_fields_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `cms_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `idea_reports`;
CREATE TABLE `idea_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idea_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idea_reports_idea_id_user_id_unique` (`idea_id`,`user_id`),
  KEY `idea_reports_user_id_foreign` (`user_id`),
  CONSTRAINT `idea_reports_idea_id_foreign` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `idea_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ideas`;
CREATE TABLE `ideas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `likes` int NOT NULL DEFAULT '0',
  `dislikes` int NOT NULL DEFAULT '0',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending','in_progress','completed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `category` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ideas_brand_id_foreign` (`brand_id`),
  KEY `ideas_user_id_foreign` (`user_id`),
  CONSTRAINT `ideas_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ideas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `main_question_responses`;
CREATE TABLE `main_question_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned NOT NULL,
  `main_question_id` bigint unsigned NOT NULL,
  `answer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main_question_responses_user_id_foreign` (`user_id`),
  KEY `main_question_responses_brand_id_foreign` (`brand_id`),
  KEY `main_question_responses_main_question_id_foreign` (`main_question_id`),
  CONSTRAINT `main_question_responses_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `main_question_responses_main_question_id_foreign` FOREIGN KEY (`main_question_id`) REFERENCES `main_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `main_question_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `main_questions`;
CREATE TABLE `main_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answers` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `prize` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `quiz_questions` json NOT NULL,
  `quiz_answers` json NOT NULL,
  `participants` json DEFAULT NULL,
  `winner_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quizzes_slug_unique` (`slug`),
  KEY `quizzes_brand_id_foreign` (`brand_id`),
  CONSTRAINT `quizzes_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `education_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relationship_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `liked_posts` json DEFAULT NULL,
  `disliked_posts` json DEFAULT NULL,
  `created_posts` json DEFAULT NULL,
  `quiz_submissions` json DEFAULT NULL,
  `ratings_given` json DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `notifications` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brand_owners` (`id`, `brand_id`, `name`, `email`, `phone`, `url`, `subscription_plan`, `password`, `verified_owner`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'peter jan', 'contact@febo.nl', '06214532423', 'https://www.febo.nl', 'Brons', '$2y$12$i9WP.y8us2yhLqpjvv9FJOVF2jJxLPUsRkl6oHm7BPjMuXdi7wX.S', 1, '2025-08-16 14:25:07', '2025-08-16 14:24:53', '2025-08-16 14:25:07'),
(2, 2, 'Jan willem', 'info@videoland.com', '0612345678', 'https://www.videoland.com', 'Zilver', '$2y$12$kNL/ug5xcycL/1RnWCTeQ.BcKJgiHD4J3chndGWVai/kseU2kbj.u', 1, '2025-08-16 20:37:37', '2025-08-16 20:37:18', '2025-08-16 20:37:37'),
(3, 3, 'Kees De Koning', 'contact@topnotch.nl', '0612345678', 'https://www.topnotch.nl', 'Brons', '$2y$12$0I3h9TGbRniWHJeqD7Dq1OgaJTK9l9rA73QV7EBKwAiJh2eqaE2UG', 1, '2025-08-17 10:48:23', '2025-08-17 10:48:06', '2025-08-17 10:48:23'),
(4, 4, 'Charlotte de vries', 'contact@hema.nl', '0612345678', 'https://www.hema.nl', 'Goud', '$2y$12$7f.hltVWSv9BUIJ6ndcPX.Owe59.ANS8QDS5lXaZBcJSdz4bTwzcG', 1, '2025-08-17 10:57:54', '2025-08-17 10:57:40', '2025-08-17 10:57:54'),
(5, 5, 'Charlotte de vries', 'contact@awakenings.nl', '0612345678', 'https://www.awakenings.nl', 'Brons', '$2y$12$a5JyRvoYQpBBbcc3WFvZge2OCtSb05CI5a.lWl2rZ/kvJVwR6G5iO', 1, '2025-08-17 11:01:48', '2025-08-17 11:01:34', '2025-08-17 11:01:48');

INSERT INTO `brands` (`id`, `title`, `slug`, `category`, `website_url`, `intro`, `intro_short`, `email`, `logo_path`, `socials`, `verified`, `rating_sum`, `rating_count`, `rating`, `has_paid`, `subscription`, `brand_owner_id`, `likes`, `dislikes`, `quizzes`, `giveaways`, `main_question_id`, `ideas`, `pinned_ideas`, `accepted`, `created_at`, `updated_at`) VALUES
(1, 'FEBO', 'febo', 'Snackbar', 'https://www.febo.nl', 'de beste snackbar van de wereld', 'snackbar febo', 'contact@febo.nl', 'brands/mMQ4Be8yTd1HDxsy1rF5jLNs6jAAEIOmB6G8114D.png', '\"[]\"', 1, 7, 1, 0, 0, NULL, 1, 0, 0, '[]', '[]', 6, '[]', '[]', 1, '2025-08-16 14:23:56', '2025-08-16 20:40:22'),
(2, 'Videoland', 'videoland', 'Streamingplatform', 'https://www.videoland.nl', 'Videoland is een streaming platform uit Nederland', 'Videoland streamingplatform', 'info@videoland.nl', 'brands/0Cg8nXY8y6311uaMvKSAeXc2wWNIwp0IR6mDepd7.png', '\"[]\"', 1, 0, 0, 0, 0, NULL, 2, 0, 0, '[]', '[]', NULL, '[]', '[]', 1, '2025-08-16 20:28:23', '2025-08-16 20:37:31'),
(3, 'Topnotch', 'topnotch', 'Muziek label', 'https://www.topnotch.nl', 'Top-notch is het grootste urban muziek label van Nederland', 'Top-Notch muzieklabel.', 'contact@topnotch.nl', 'brands/R5VA1DYcgNhkKJbSN62mD56zLkyIN1ATPfM8vOKv.png', '\"[]\"', 1, 0, 0, 0, 0, NULL, 3, 0, 0, '[]', '[]', NULL, '[]', '[]', 1, '2025-08-17 10:45:40', '2025-08-17 10:48:17'),
(4, 'Hema', 'hema', 'Winkel', 'https://www.hema.nl', 'HEMA is een Nederlandse winkelketen die bekend staat om zijn brede assortiment aan alledaagse producten en huismerken.', 'Hema, Hollandsche Eenheidsprijzen Maatschappij Amsterdam', 'contact@hema.nl', 'brands/8CNRTcWdsseaImNfzsx2WEj8QjgToV3Fn8CGK2db.png', '\"[]\"', 1, 0, 0, 0, 0, NULL, 4, 0, 0, '[]', '[]', NULL, '[]', '[]', 1, '2025-08-17 10:56:51', '2025-08-17 10:57:48'),
(5, 'Awakenings', 'awakenings', 'Festival', 'https://www.awakenings.nl', 'Awakenings is een technofestival sinds 1997', 'Technofestival', 'contact@awakenings.nl', 'brands/GPKpBjiQxr7rEB2rIofZ261yId3Arkx6VdHv8P6c.png', '\"[]\"', 1, 0, 0, 0, 0, NULL, 5, 0, 0, '[]', '[]', NULL, '[]', '[]', 1, '2025-08-17 11:01:04', '2025-08-17 11:01:44');

INSERT INTO `cms_fields` (`id`, `page_id`, `label`, `key`, `type`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 'Home Page Title', 'home-title', 'text', 'De plek waar ideeën landen en groeien', '2025-08-16 14:05:43', '2025-08-16 14:05:43'),
(2, 1, 'Home Page Description', 'home-description', 'text', 'Ideeënbord verbindt merken en idee-makers. Dien jouw idee in, stem op favorieten en zie welke ideeën echt gebouwd worden.', '2025-08-16 14:05:43', '2025-08-16 14:05:43'),
(3, 1, 'Home CTA', 'home-cta', 'text', 'Ontdek hoe het werkt', '2025-08-16 14:05:43', '2025-08-16 14:05:43'),
(5, 1, 'Home About image', 'home-about-image', 'image', '/storage/images-cms/aQHiqzhyJ1GVhTgh9fm38fEtWh3txMJMK3fTRvZu.png', '2025-08-16 14:06:24', '2025-08-16 14:06:24'),
(6, 1, 'Stat: Companies', 'stat-companies', 'text', '42', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(7, 1, 'Stat: Companies Label', 'stat-companies-label', 'text', 'actieve merken', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(8, 1, 'Stat: Idea Makers', 'stat-idea-makers', 'text', '3.1k', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(9, 1, 'Stat: Idea Makers Label', 'stat-idea-makers-label', 'text', 'idee-makers', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(10, 1, 'Stat: Ideas Submitted', 'stat-ideas-submitted', 'text', '8.7k', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(11, 1, 'Stat: Ideas Submitted Label', 'stat-ideas-submitted-label', 'text', 'ingediende ideeën', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(12, 1, 'Stat: Ideas Implemented', 'stat-ideas-implemented', 'text', '520', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(13, 1, 'Stat: Ideas Implemented Label', 'stat-ideas-implemented-label', 'text', 'gerealiseerde ideeën', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(14, 1, 'Motivation Title', 'motivation-title', 'text', 'Maak van feedback echte features', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(15, 1, 'Motivation Description', 'motivation-description', 'text', 'Bij Ideeënbord wordt elke stem meegewogen. Merken krijgen focus, community’s zien voortgang en jij ziet wat je bijdrage oplevert.', '2025-08-16 14:07:00', '2025-08-16 14:07:00'),
(17, 1, 'HowTo Step 1 Title', 'howto-step1-title', 'text', 'Dien je idee in', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(18, 1, 'HowTo Step 1 Description', 'howto-step1-description', 'text', 'Kies een merk, beschrijf je idee en voeg optioneel visuals toe.', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(20, 1, 'HowTo Step 2 Title', 'howto-step2-title', 'text', 'Verzamel stemmen', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(21, 1, 'HowTo Step 2 Description', 'howto-step2-description', 'text', 'De community stemt en geeft feedback — het beste werk stijgt omhoog.', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(23, 1, 'HowTo Step 3 Title', 'howto-step3-title', 'text', 'Zie het gebouwd worden', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(24, 1, 'HowTo Step 3 Description', 'howto-step3-description', 'text', 'Merken pikken winnende ideeën op en tonen voortgang tot release.', '2025-08-16 14:07:22', '2025-08-16 14:07:22'),
(25, 1, 'Current Actions Left Title', 'currentactions-left-title', 'text', 'Nieuwe deelnemende merken', '2025-08-16 14:07:37', '2025-08-16 14:07:37'),
(26, 1, 'Current Actions Right Title', 'currentactions-right-title', 'text', 'Laatste winacties & quizzes', '2025-08-16 14:07:37', '2025-08-16 14:07:37'),
(27, 1, 'Brand Slider Title', 'brandslider-title', 'text', 'Merken die luisteren naar ideeën', '2025-08-16 14:07:52', '2025-08-16 14:07:52'),
(28, 1, 'Video Title', 'video-title', 'text', 'Wat is Ideeënbord?', '2025-08-16 14:08:06', '2025-08-16 14:08:06'),
(29, 1, 'Video Description', 'video-description', 'text', 'In 90 seconden: hoe makers, klanten en merken samen betere producten bouwen met transparante feedback en duidelijke voortgang.', '2025-08-16 14:08:06', '2025-08-16 14:08:06'),
(30, 1, 'Video URL', 'video-url', 'link', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2025-08-16 14:08:06', '2025-08-16 14:08:06'),
(31, 1, 'Options Title', 'options-title', 'text', 'Kies je volgende stap', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(32, 1, 'Options Intro', 'options-intro', 'text', 'Start met ideeën posten, ontdek merken of doe mee aan een quiz. Jij bepaalt hoe je bijdraagt.', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(33, 1, 'Option 1 Title', 'option1-title', 'text', 'Dien een idee in', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(34, 1, 'Option 1 Text', 'option1-text', 'text', 'Heb je een feature, verbeterpunt of campagne in gedachten? Zet ‘m live.', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(35, 1, 'Option 1 Button', 'option1-button', 'text', 'Start hier', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(36, 1, 'Option 1 Link', 'option1-link', 'link', '/ideas', '2025-08-16 14:08:53', '2025-08-16 14:10:22'),
(37, 1, 'Option 2 Title', 'option2-title', 'text', 'Ontdek merken', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(38, 1, 'Option 2 Text', 'option2-text', 'text', 'Bekijk welke merken meedoen en waar jouw idee het meeste impact heeft.', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(39, 1, 'Option 2 Button', 'option2-button', 'text', 'Bekijk merken', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(40, 1, 'Option 2 Link', 'option2-link', 'link', '/participants', '2025-08-16 14:08:53', '2025-08-16 14:10:16'),
(41, 1, 'Option 3 Title', 'option3-title', 'text', 'Stem & volg voortgang', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(42, 1, 'Option 3 Text', 'option3-text', 'text', 'Help prioriteren met jouw stem en zie welke ideeën worden opgepakt.', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(43, 1, 'Option 3 Button', 'option3-button', 'text', 'Naar ideeën', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(44, 1, 'Option 3 Link', 'option3-link', 'link', '/ideas', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(45, 1, 'Option 4 Title', 'option4-title', 'text', 'Speel mee & win', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(46, 1, 'Option 4 Text', 'option4-text', 'text', 'Doe mee aan quizzes en winacties van je favoriete merken.', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(47, 1, 'Option 4 Button', 'option4-button', 'text', 'Bekijk acties', '2025-08-16 14:08:53', '2025-08-16 14:08:53'),
(48, 1, 'Option 4 Link', 'option4-link', 'link', '/win', '2025-08-16 14:08:53', '2025-08-16 14:10:10'),
(49, 1, 'How to step 1 image', 'howto-step1-image', 'image', '/storage/images-cms/Vyju5TakRx5KWYIrVDdeJbPzDvJ5QRrRU8w48DM9.png', '2025-08-16 14:09:41', '2025-08-16 14:09:41'),
(50, 1, 'How to step 2 image', 'howto-step2-image', 'image', '/storage/images-cms/3zuZU0givgqnRljRrS40x6ljt6fpXS3RVcHCLgvl.png', '2025-08-16 14:09:51', '2025-08-16 14:09:51'),
(51, 1, 'How to step 3 image', 'howto-step3-image', 'image', '/storage/images-cms/AmctgrZlfnEgueBDvm7TzvmoindtU5t4PwU8lE9L.png', '2025-08-16 14:10:01', '2025-08-16 14:10:01'),
(53, 2, 'Hero Title', 'hero-title', 'text', 'Over Ideeënbord', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(54, 2, 'Hero Subtitle', 'hero-subtitle', 'text', 'Wij verbinden merken met de slimste ideeën van fans en klanten. Zo wordt goede feedback zichtbaar, meetbaar en echt gebouwd.', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(55, 2, 'Brand Title', 'brand-title', 'text', 'Voor merken: bouw wat ertoe doet', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(56, 2, 'Brand Description', 'brand-description', 'text', 'Ideeënbord helpt je om ideeën te verzamelen, te prioriteren met community-stemmen en transparant te leveren. Minder ruis, meer impact.', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(57, 2, 'Brand Step 1 Title', 'brand-step1-title', 'text', 'Verzamel & prioriteer', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(58, 2, 'Brand Step 1 Description', 'brand-step1-description', 'text', 'Ontvang ideeën op één plek, laat de community stemmen en kies wat je eerst bouwt.', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(59, 2, 'Brand Step 2 Title', 'brand-step2-title', 'text', 'Lever transparant', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(60, 2, 'Brand Step 2 Description', 'brand-step2-description', 'text', 'Toon status, updates en releases zodat fans betrokken blijven en vertrouwen groeit.', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(61, 2, 'Brand CTA Label', 'brand-cta-label', 'text', 'Word deelnemend merk', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(62, 2, 'Brand CTA Link', 'brand-cta-link', 'link', '/become-a-brandowner', '2025-08-16 14:10:55', '2025-08-16 14:13:18'),
(64, 2, 'Fan Title', 'fan-title', 'text', 'Voor fans: laat je idee landen', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(65, 2, 'Fan Description', 'fan-description', 'text', 'Dien je idee in, stem mee en volg de voortgang. Jouw input helpt features sneller werkelijkheid te worden.', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(67, 2, 'Fan CTA: Login Label', 'fan-cta-login-label', 'text', 'Inloggen', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(68, 2, 'Fan CTA: Login Link', 'fan-cta-login-link', 'link', '/login', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(69, 2, 'Fan CTA: Register Label', 'fan-cta-register-label', 'text', 'Account aanmaken', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(70, 2, 'Fan CTA: Register Link', 'fan-cta-register-link', 'link', '/register', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(71, 2, 'Fan CTA: Participants Label', 'fan-cta-participants-label', 'text', 'Deelnemende merken', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(72, 2, 'Fan CTA: Participants Link', 'fan-cta-participants-link', 'link', '/participants', '2025-08-16 14:10:55', '2025-08-16 14:11:09'),
(73, 2, 'Fan CTA: Ideas Label', 'fan-cta-ideas-label', 'text', 'Bekijk ideeën', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(74, 2, 'Fan CTA: Ideas Link', 'fan-cta-ideas-link', 'link', '/ideas', '2025-08-16 14:10:55', '2025-08-16 14:10:55'),
(75, 2, 'Fan image', 'fan-image', 'image', '/storage/images-cms/3C98WFdkvPljZHqBvuPFgkljA8tqbBHKNDO3s744.png', '2025-08-16 14:11:36', '2025-08-16 14:11:36'),
(76, 2, 'brand image', 'brand-image', 'image', '/storage/images-cms/zcAqqZv43Q8zJWwiNYISvt0VZ20B2YIFAA0lCPS4.png', '2025-08-16 14:11:49', '2025-08-16 14:11:49'),
(77, 2, 'Hero image', 'hero-image', 'image', '/storage/images-cms/lwJk9Wq618MV6xisklwG0n2ZnpFR7WsbAGbyuicO.jpg', '2025-08-16 14:12:16', '2025-08-16 14:12:16'),
(79, 3, 'Hero Title', 'hero-title', 'text', 'Nieuws & updates', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(80, 3, 'Hero Paragraph', 'hero-paragraph', 'text', 'Blijf op de hoogte van releases, milestones en events rondom Ideeënbord.', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(81, 3, 'Article 1 Title', 'article1-title', 'text', 'Nieuwe merch drop: limited ‘Idea Maker’ collectie', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(82, 3, 'Article 1 Slug', 'article1-slug', 'text', 'nieuwe-merch-idea-maker-collectie', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(83, 3, 'Article 1 Excerpt', 'article1-excerpt', 'html', '<p>We lanceren onze eerste <strong>‘Idea Maker’</strong> merch: hoodies, tees en stickers. Limited oplage, duurzaam materiaal.</p>', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(85, 3, 'Article 1 Body', 'article1-body', 'html', '<p>Ideeënbord draait om makers. Daarom lanceren we de <strong>‘Idea Maker’</strong> collectie – ontworpen samen met de community. De eerste drop bevat:</p><ul><li>Unisex hoodie (zwart en zand)</li><li>Heavy tee (wit en zwart)</li><li>Stickerpack (5 stuks)</li></ul><p>Alles is geproduceerd in kleine oplage met oog voor kwaliteit en comfort. Op = op. Je steunt er bovendien de ontwikkeling van nieuwe features mee.</p><p><em>Beschikbaarheid:</em> de shop gaat deze maand live voor geregistreerde gebruikers. Community-leden krijgen 48 uur early access.</p>', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(86, 3, 'Article 2 Title', 'article2-title', 'text', '1.000 gebruikers! Dank je wel, community', '2025-08-16 14:12:50', '2025-08-16 14:12:50'),
(87, 3, 'Article 2 Slug', 'article2-slug', 'text', '1000-gebruikers-mijlpaal', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(88, 3, 'Article 2 Excerpt', 'article2-excerpt', 'html', '<p>We hebben de <strong>1.000 gebruikers</strong> aangetikt. Tijd voor een kleine terugblik en wat we hierna bouwen.</p>', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(90, 3, 'Article 2 Body', 'article2-body', 'html', '<p>Wat begon als een idee om feedback zichtbaar te maken, groeit uit tot een plek waar fans en merken elkaar vinden. Dankzij jullie is de 1.000-gebruikersmijlpaal bereikt.</p><p><strong>Wat we tot nu toe hebben gerealiseerd:</strong></p><ul><li>Ideeën indienen en stemmen</li><li>Publieke voortgang per merk</li><li>Quiz/winacties met transparante selectie</li></ul><p><strong>Wat eraan komt:</strong></p><ul><li>Verbeterde merkprofielen met roadmaps</li><li>Notificaties & persoonlijke inbox</li><li>Open API voor data-exports</li></ul><p>Dank voor alle feedback & features die jullie blijven aandragen. Op naar de volgende 10.000! 🚀</p>', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(91, 3, 'Article 3 Title', 'article3-title', 'text', 'Ideeënbord spreekt op de Product Community Summit', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(92, 3, 'Article 3 Slug', 'article3-slug', 'text', 'ideeënbord-op-product-community-summit', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(93, 3, 'Article 3 Excerpt', 'article3-excerpt', 'html', '<p>We zijn geselecteerd voor de <strong>Product Community Summit</strong>. We delen onze learnings over community-driven roadmaps.</p>', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(95, 3, 'Article 3 Body', 'article3-body', 'html', '<p>Op de Product Community Summit presenteren we hoe merken <em>community-signalen</em> vertalen naar duidelijke prioriteiten en releases. Verwacht praktische frameworks, metrics en voorbeelden uit onze pilot-fase.</p><p><strong>Topics in onze talk:</strong></p><ol><li>Van ruis naar richting: prioriteren met stemmen & signalen</li><li>Transparantie als feature: statusupdates die werken</li><li>Van idee naar impact: meten wat een release oplevert</li></ol><p>Ben je erbij? Kom hallo zeggen – we horen graag wat jij mist in tooling voor community-gedreven productontwikkeling.</p>', '2025-08-16 14:12:51', '2025-08-16 14:12:51'),
(96, 3, 'Hero image', 'hero-image', 'image', '/storage/images-cms/8m0negRbPCn2ZOxCnfuPnCOCNVBQqhdBlY8BmRvG.jpg', '2025-08-16 14:13:48', '2025-08-16 14:13:48'),
(97, 3, 'Article 1 image', 'article1-image', 'image', '/storage/images-cms/cOs35S3AsoHG6QtKG6RTCTlihIr7YQrDmEfq9byS.png', '2025-08-16 14:14:13', '2025-08-16 14:14:13'),
(98, 3, 'Article 2 image', 'article2-image', 'image', '/storage/images-cms/tyeE7Pb5PUr5WwPybzymBmQ0ILwOIFHffJW0wcIV.jpg', '2025-08-16 14:14:37', '2025-08-16 14:14:37'),
(99, 3, 'Article 3 image', 'article3-image', 'image', '/storage/images-cms/XZfu4kCpPyJbVzQzL6hH7j5NPfJJkrXSGPId9ZHi.jpg', '2025-08-16 14:15:06', '2025-08-16 14:15:06'),
(101, 4, 'Hero Title', 'hero-title', 'text', 'Speel mee & win met jouw ideeën', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(102, 4, 'Hero Paragraph', 'hero-paragraph', 'text', 'Doe mee aan quizzes en winacties van deelnemende merken. Test je kennis, deel je visie en maak kans op exclusieve prijzen.', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(103, 4, 'CTA Label', 'cta-label', 'text', 'Bekijk deelnemende merken', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(104, 4, 'Steps Title', 'steps-title', 'text', 'Zo werkt meedoen', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(105, 4, 'Step 1 Title', 'step1-title', 'text', 'Kies een merk & actie', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(106, 4, 'Step 1 Description', 'step1-desc', 'text', 'Ga naar deelnemers, kies je favoriete merk en open de lopende quiz of winactie.', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(107, 4, 'Step 2 Title', 'step2-title', 'text', 'Beantwoord & verzamel punten', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(108, 4, 'Step 2 Description', 'step2-desc', 'text', 'Beantwoord de vragen of uitdagingen. Extra punten voor scherpe ideeën en onderbouwing.', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(109, 4, 'Step 3 Title', 'step3-title', 'text', 'Volg uitslag & claim je prijs', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(110, 4, 'Step 3 Description', 'step3-desc', 'text', 'Na sluiting kiest het merk een winnaar. Je krijgt een melding in je inbox en op de merkpagina.', '2025-08-16 14:15:31', '2025-08-16 14:15:31'),
(111, 4, 'Hero image', 'hero-image', 'image', '/storage/images-cms/zQULketF6LKSSSRaBjcKhdURMpvnzGNRyehDd1kd.png', '2025-08-16 14:16:01', '2025-08-16 14:16:01'),
(113, 5, 'Hero Title', 'hero-title', 'text', 'De plek waar ideeën landen', '2025-08-16 14:16:40', '2025-08-16 14:16:40'),
(114, 5, 'Hero Paragraph', 'hero-paragraph', 'text', 'Ontdek, filter en stem op ideeën van deelnemende merken. Geef feedback, like of discussieer mee en help bepalen wat er als eerste gebouwd wordt.', '2025-08-16 14:16:40', '2025-08-16 14:16:40'),
(115, 5, 'CTA Label', 'cta-label', 'text', 'Zoek deelnemende brands', '2025-08-16 14:16:40', '2025-08-16 14:16:40'),
(116, 5, 'Hero Image', 'hero-image', 'image', '/storage/images-cms/0VjVRq2Xx57FHyp46bglKh1sQjgEwciWPOdmd5U3.jpg', '2025-08-16 14:17:20', '2025-08-16 14:17:20'),
(118, 6, 'Banner Title', 'banner-title', 'text', 'Ontdek alle deelnemende merken', '2025-08-16 14:17:42', '2025-08-16 14:17:42'),
(119, 6, 'Banner Paragraph', 'banner-paragraph', 'text', 'Hier vind je alle bedrijven die actief luisteren naar ideeën van de community. Filter op categorie of zoek direct naar je favoriete merk.', '2025-08-16 14:17:42', '2025-08-16 14:17:42'),
(120, 6, 'Search Placeholder', 'search-placeholder', 'text', 'Zoek op merk of categorie…', '2025-08-16 14:17:42', '2025-08-16 14:17:42'),
(121, 6, 'Filter Title', 'filter-title', 'text', 'Categorieën', '2025-08-16 14:17:42', '2025-08-16 14:17:42'),
(122, 6, 'Banner image', 'banner-image', 'image', '/storage/images-cms/WNj9rCaK8xL7AnzA8tuljTO4JNPb73xi90xccAfJ.png', '2025-08-16 14:18:04', '2025-08-16 14:18:04'),
(123, 7, 'Become Title', 'become-title', 'text', 'Word deelnemend merk', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(124, 7, 'Step 1 Title', 'step1-title', 'text', 'Vraag je merk aan', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(125, 7, 'Step 1 Description', 'step1-description', 'text', 'Vul het aanvraagformulier in met je bedrijfsgegevens, categorie en logo. Ons team controleert de aanvraag.', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(126, 7, 'Step 1 Link', 'step1-link', 'link', '/brands/request', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(127, 7, 'Step 1 Link Label', 'step1-link-label', 'text', 'Vraag een merk aan', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(128, 7, 'Step 2 Title', 'step2-title', 'text', 'Word geverifieerd', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(129, 7, 'Step 2 Description', 'step2-description', 'text', 'Na goedkeuring wordt je merk zichtbaar in het overzicht. Je krijgt toegang tot je eigen dashboard.', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(130, 7, 'Step 3 Title', 'step3-title', 'text', 'Activeer je dashboard', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(131, 7, 'Step 3 Description', 'step3-description', 'text', 'Pas je profiel aan, stel vragen aan de community en open je eerste quiz of winactie.', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(132, 7, 'Step 3 Link', 'step3-link', 'link', '/dashboard', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(133, 7, 'Step 3 Link Label', 'step3-link-label', 'text', 'Ga naar je dashboard', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(134, 7, 'Step 4 Title', 'step4-title', 'text', 'Verzamel ideeën', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(135, 7, 'Step 4 Description', 'step4-description', 'text', 'Communityleden kunnen ideeën indienen en stemmen. Jij volgt alles in je dashboard.', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(136, 7, 'Step 5 Title', 'step5-title', 'text', 'Implementeer en deel updates', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(137, 7, 'Step 5 Description', 'step5-description', 'text', 'Laat zien welke ideeën je oppakt, welke in ontwikkeling zijn en wat al live staat.', '2025-08-16 14:18:24', '2025-08-16 14:18:24'),
(139, 8, 'Hero Title', 'hero-title', 'text', 'Privacyverklaring', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(140, 8, 'Hero Subtitle', 'hero-subtitle', 'text', 'We gaan zorgvuldig om met jouw gegevens. Hieronder lees je welke data we verzamelen en waarom.', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(141, 8, 'Updated Date', 'updated-date', 'text', '2025-08-16', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(142, 8, 'Section 1 Title', 'section1-title', 'text', 'Wie we zijn', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(143, 8, 'Section 1 Body', 'section1-body', 'html', '<p>Ideeënbord (\"wij\") is een platform waar merken en fans samen bouwen aan betere producten. Deze privacyverklaring is van toepassing op alle diensten en websites onder Ideeënbord.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(144, 8, 'Section 2 Title', 'section2-title', 'text', 'Welke gegevens we verzamelen', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(145, 8, 'Section 2 Body', 'section2-body', 'html', '<ul><li><strong>Accountgegevens:</strong> naam, e-mail, wachtwoord (gehasht).</li><li><strong>Profiel & interacties:</strong> ingestuurde ideeën, stemmen/likes, reacties en quizdeelnames.</li><li><strong>Technische data:</strong> IP-adres, apparaat/ browserinformatie, logbestanden.</li><li><strong>Optioneel:</strong> merklogo\'s en mediabestanden die je uploadt.</li></ul>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(146, 8, 'Section 3 Title', 'section3-title', 'text', 'Waarom we deze gegevens gebruiken', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(147, 8, 'Section 3 Body', 'section3-body', 'html', '<ul><li>Om je account te beheren en toegang te geven tot functionaliteit.</li><li>Om ideeën, stemmen en voortgang goed te kunnen tonen.</li><li>Voor beveiliging, misbruikpreventie en het verbeteren van onze dienstverlening.</li><li>Voor communicatie over belangrijke wijzigingen, support en (optioneel) productupdates.</li></ul>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(148, 8, 'Section 4 Title', 'section4-title', 'text', 'Rechtsgrond', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(149, 8, 'Section 4 Body', 'section4-body', 'html', '<p>We verwerken persoonsgegevens op basis van <strong>uitvoering van overeenkomst</strong> (je account), <strong>gerechtvaardigd belang</strong> (beveiliging, platformverbetering) en, waar nodig, <strong>toestemming</strong> (nieuwsbrieven).</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(150, 8, 'Section 5 Title', 'section5-title', 'text', 'Bewaartermijn', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(151, 8, 'Section 5 Body', 'section5-body', 'html', '<p>We bewaren gegevens niet langer dan nodig. Je kunt je account verwijderen; we anonimiseren of verwijderen je content waar mogelijk, behoudens wettelijke verplichtingen en anti-misbruikmaatregelen.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(152, 8, 'Section 6 Title', 'section6-title', 'text', 'Delen met derden', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(153, 8, 'Section 6 Body', 'section6-body', 'html', '<p>We delen geen persoonsgegevens met derden, behalve met <em>verwerkers</em> die ons helpen (hosting, e-mail, analytics) en alleen volgens onze instructies en verwerkersovereenkomsten. Ideeën en openbare profielen kunnen zichtbaar zijn voor andere gebruikers.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(154, 8, 'Section 7 Title', 'section7-title', 'text', 'Cookies & tracking', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(155, 8, 'Section 7 Body', 'section7-body', 'html', '<p>We gebruiken functionele cookies voor inloggen en sessies, en (optioneel) analytische cookies om het platform te verbeteren. Je kunt je voorkeuren beheren in je browser of via onze cookie-instellingen.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(156, 8, 'Section 8 Title', 'section8-title', 'text', 'Jouw rechten', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(157, 8, 'Section 8 Body', 'section8-body', 'html', '<ul><li>Recht op inzage, correctie, verwijdering en dataportabiliteit.</li><li>Recht op beperking of bezwaar tegen verwerking.</li><li>Recht om toestemming in te trekken (waar van toepassing).</li></ul><p>Neem contact met ons op om deze rechten uit te oefenen.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(158, 8, 'Section 9 Title', 'section9-title', 'text', 'Beveiliging', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(159, 8, 'Section 9 Body', 'section9-body', 'html', '<p>We nemen passende technische en organisatorische maatregelen, zoals versleuteling, toegangscontrole en periodieke audits.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(160, 8, 'Section 10 Title', 'section10-title', 'text', 'Contact', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(161, 8, 'Section 10 Body', 'section10-body', 'html', '<p>Vragen of verzoeken? Mail ons via <a href=\"mailto:privacy@ideeenbord.app\">privacy@ideeenbord.app</a>. Postadres en aanvullende gegevens delen we op verzoek.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(162, 8, 'Contact Title (footer)', 'contact-title', 'text', 'Vragen over deze privacyverklaring', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(163, 8, 'Contact Body (footer)', 'contact-body', 'html', '<p>Neem contact op met onze privacycontactpersoon via <a href=\"mailto:privacy@ideeenbord.app\">privacy@ideeenbord.app</a>. Je hebt ook het recht om een klacht in te dienen bij de Autoriteit Persoonsgegevens.</p>', '2025-08-16 14:19:16', '2025-08-16 14:19:16'),
(164, 1, 'Home tagline', 'home-tagline', 'text', 'Bordevol ideeën!', '2025-08-16 16:51:11', '2025-08-16 16:51:11'),
(166, 9, 'Hero Title', 'hero-title', 'text', 'Kies je abonnement', '2025-08-16 17:05:12', '2025-08-16 17:05:12'),
(167, 9, 'Hero Paragraph', 'hero-paragraph', 'text', 'Ontgrendel data en tools om meer uit Ideeënbord te halen. Start klein of ga voor alles—jij kiest.', '2025-08-16 17:05:12', '2025-08-16 17:05:12'),
(168, 9, 'Bronze Title', 'plan-bronze-title', 'text', 'Brons', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(169, 9, 'Bronze Subtitle', 'plan-bronze-subtitle', 'text', 'Instappen & ontdekken', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(170, 9, 'Bronze Price', 'plan-bronze-price', 'text', '99', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(171, 9, 'Bronze Description', 'plan-bronze-description', 'text', 'Basisinzichten voor je merk.', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(172, 9, 'Bronze Feature 1', 'plan-bronze-feature1', 'text', 'Toegang tot exclusieve data voor jouw merk', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(173, 9, 'Bronze Feature 2', 'plan-bronze-feature2', 'text', 'Export: CSV, JSON, Excel (geen API)', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(174, 9, 'Bronze CTA Label', 'plan-bronze-cta-label', 'text', 'Kies Brons', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(175, 9, 'Bronze CTA Link', 'plan-bronze-cta-link', 'link', '/checkout?plan=bronze', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(176, 9, 'Silver Title', 'plan-silver-title', 'text', 'Zilver', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(177, 9, 'Silver Subtitle', 'plan-silver-subtitle', 'text', 'Groei & optimalisatie', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(178, 9, 'Silver Price', 'plan-silver-price', 'text', '149', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(179, 9, 'Silver Description', 'plan-silver-description', 'text', 'Uitgebreidere data + opschonen.', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(180, 9, 'Silver Feature 1', 'plan-silver-feature1', 'text', 'Toegang tot exclusieve data voor jouw merk', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(181, 9, 'Silver Feature 2', 'plan-silver-feature2', 'text', 'Export: CSV, JSON + API toegang', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(182, 9, 'Silver Feature 3', 'plan-silver-feature3', 'text', 'Jaarlijks rapport', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(183, 9, 'Silver Feature 4', 'plan-silver-feature4', 'text', 'Verwijderen van ideeën', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(184, 9, 'Silver CTA Label', 'plan-silver-cta-label', 'text', 'Kies Zilver', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(185, 9, 'Silver CTA Link', 'plan-silver-cta-link', 'link', '/checkout?plan=silver', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(186, 9, 'Gold Title', 'plan-gold-title', 'text', 'Goud', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(187, 9, 'Gold Subtitle', 'plan-gold-subtitle', 'text', 'Alles-in-één', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(188, 9, 'Gold Price', 'plan-gold-price', 'text', '200', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(189, 9, 'Gold Description', 'plan-gold-description', 'text', 'Maximale inzichten en controle.', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(190, 9, 'Gold Feature 1', 'plan-gold-feature1', 'text', 'Toegang tot exclusieve data voor jouw merk', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(191, 9, 'Gold Feature 2', 'plan-gold-feature2', 'text', 'Export: CSV, JSON + API toegang', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(192, 9, 'Gold Feature 3', 'plan-gold-feature3', 'text', 'Jaarlijks rapport', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(193, 9, 'Gold Feature 4', 'plan-gold-feature4', 'text', 'Maandelijks rapport', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(194, 9, 'Gold Feature 5', 'plan-gold-feature5', 'text', 'Verwijderen van ideeën', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(195, 9, 'Gold Feature 6', 'plan-gold-feature6', 'text', 'Meer mogelijkheden voor het aanpassen van je profiel', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(196, 9, 'Gold CTA Label', 'plan-gold-cta-label', 'text', 'Kies Goud', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(197, 9, 'Gold CTA Link', 'plan-gold-cta-link', 'link', '/checkout?plan=gold', '2025-08-16 17:05:13', '2025-08-16 17:05:13'),
(198, 8, 'Hero Image', 'hero-image', 'image', '/storage/images-cms/XaHgDL9xpSKEhXslUbDYJUjohhZEr4pddFyGsNPR.png', '2025-08-16 17:09:25', '2025-08-16 17:09:25'),
(199, 9, 'Hero Image', 'hero-image', 'image', '/storage/images-cms/IODygl94jk3vidKt1XSWSQQNeiClpjE0KFPAVkdU.webp', '2025-08-16 17:11:41', '2025-08-16 17:11:41');

INSERT INTO `cms_pages` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'Home', '2025-08-16 14:05:23', '2025-08-16 14:05:23'),
(2, 'Uitleg', '2025-08-16 14:10:50', '2025-08-16 14:10:50'),
(3, 'News', '2025-08-16 14:12:31', '2025-08-16 14:12:31'),
(4, 'Win', '2025-08-16 14:15:27', '2025-08-16 14:15:27'),
(5, 'ideeen', '2025-08-16 14:16:36', '2025-08-16 14:16:36'),
(6, 'deelnemers', '2025-08-16 14:17:38', '2025-08-16 14:17:38'),
(7, 'become-a-brandowner', '2025-08-16 14:18:20', '2025-08-16 14:18:20'),
(8, 'privacy', '2025-08-16 14:19:13', '2025-08-16 14:19:13'),
(9, 'subscriptions', '2025-08-16 17:05:00', '2025-08-16 17:05:00');

INSERT INTO `ideas` (`id`, `brand_id`, `user_id`, `title`, `description`, `likes`, `dislikes`, `is_pinned`, `status`, `category`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 'Nieuw burger idee', 'Een hamburger maar in plaats van vlees gebruik dan boekoeloekoe mix', 0, 1, 0, 'pending', 'Product aanbod', '2025-08-16 14:26:28', '2025-08-16 19:10:44'),
(3, 1, 1, 'Schonere winkels', 'de winkels zijn soms erg oud en vies maak deze moderner', 1, 0, 0, 'pending', 'Winkel verbetering', '2025-08-16 14:51:13', '2025-08-16 19:10:41');

INSERT INTO `main_question_responses` (`id`, `user_id`, `brand_id`, `main_question_id`, `answer`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 6, 'Betrouwbaarheid', '2025-08-16 20:40:35', '2025-08-16 20:40:35');

INSERT INTO `main_questions` (`id`, `text`, `answers`, `created_at`, `updated_at`) VALUES
(1, 'Als je aan [merknaam] denkt, welk gevoel komt dan het eerst in je op?', '[\"Ontspannen\", \"Energiek\", \"Vertrouwd\", \"Nieuwsgierig\", \"Geïnspireerd\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(2, 'Hoe vaak komt [merknaam] de laatste tijd in je gesprekken met anderen ter sprake?', '[\"Nooit\", \"Zelden\", \"Soms\", \"Regelmatig\", \"Vaak\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(3, 'Stel dat [merknaam] een persoon zou zijn, welke drie woorden zouden die persoon het beste omschrijven?', '[\"Antwoord in drie losse woorden\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(4, 'Op een schaal van 1 tot 5, waarbij 1 staat voor \'helemaal niet\' en 5 voor \'heel veel\', in hoeverre past [merknaam] bij jouw levensstijl of interesses?', '[\"1\", \"2\", \"3\", \"4\", \"5\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(5, 'Welke andere [categorie, bijv. tv-series, voetbalclubs, supermarkten] ken je die vergelijkbaar is met [merknaam]?', '[\"Geen\", \"Eén of twee\", \"Meer dan twee\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(6, 'Als je [merknaam] zou aanraden aan iemand, wat zou dan je belangrijkste reden zijn?', '[\"Kwaliteit\", \"Entertainment\", \"Gemeenschap\", \"Betrouwbaarheid\", \"Anders\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(7, 'Hoe zou je de \'sfeer\' of \'uitstraling\' van [merknaam] omschrijven in één woord?', '[\"Modern\", \"Traditioneel\", \"Spannend\", \"Gemakkelijk\", \"Gedreven\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(8, 'In vergelijking met andere [categorie] die je kent, waar zou je [merknaam] plaatsen op een populariteitsschaal?', '[\"Laag\", \"Gemiddeld\", \"Hoog\", \"Zeer hoog\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(9, 'Heb je recentelijk nog iets gezien of gehoord over [merknaam] dat je is bijgebleven?', '[\"Ja\", \"Nee\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03'),
(10, 'Als [merknaam] een kleur zou zijn, welke kleur zou dat dan zijn volgens jou?', '[\"Antwoord in één kleur\"]', '2025-08-16 20:40:03', '2025-08-16 20:40:03');

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_01_000000_create_main_questions_table', 1),
(5, '2025_04_11_175627_create_personal_access_tokens_table', 1),
(6, '2025_04_25_110304_create_brands_table', 1),
(7, '2025_04_25_112729_create_brand_owners_table', 1),
(8, '2025_04_26_140624_create_ideas_table', 1),
(9, '2025_04_30_000000_create_main_question_responses_table', 1),
(10, '2025_05_03_171002_create_quizzes_table', 1),
(11, '2025_06_11_211114_create_cms_pages_table', 1),
(12, '2025_06_11_211149_create_cms_fields_table', 1),
(13, '2025_07_13_172325_create_idea_reports_table', 1);

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', 'cca25994c7f248afeb603479bf5cc23b15bde8afada97b62c0bb80e893f0f9b0', '[\"*\"]', NULL, NULL, '2025-08-16 14:04:40', '2025-08-16 14:04:40'),
(2, 'App\\Models\\User', 1, 'auth_token', '02740ff095973432a9de454181012c393f479d0e1af3ce2429113f60ce9cedef', '[\"*\"]', NULL, NULL, '2025-08-16 14:04:56', '2025-08-16 14:04:56'),
(3, 'App\\Models\\User', 1, 'auth_token', 'a86a87c85b0faacbca52f4f94700bbba68adb02fedf05ab3d12bdf9661306773', '[\"*\"]', '2025-08-17 11:01:45', NULL, '2025-08-16 14:05:17', '2025-08-17 11:01:45'),
(4, 'App\\Models\\User', 2, 'auth_token', '64047610c17e50c7dad5a194ab7697e7f068ecbdf067ddb1eba90ab1ff501f09', '[\"*\"]', NULL, NULL, '2025-08-16 14:23:06', '2025-08-16 14:23:06'),
(5, 'App\\Models\\User', 2, 'auth_token', 'd2f2ba11f8a3a3a1184c7476edb355d3b81c47276c468413343814aec3726789', '[\"*\"]', '2025-08-16 14:24:52', NULL, '2025-08-16 14:23:28', '2025-08-16 14:24:52'),
(6, 'App\\Models\\BrandOwner', 1, 'brand-owner-token', 'f1bd0195733c0b3c1d87ac9bdc2d2856434b3532f8e794e25909463d257fddd9', '[\"*\"]', NULL, NULL, '2025-08-16 14:25:30', '2025-08-16 14:25:30'),
(7, 'App\\Models\\BrandOwner', 1, 'brand-owner-token', 'e9cf3faf23e9b6ef1512b9de47ba8bb5ec44999445b7d15313f9143523515f00', '[\"*\"]', '2025-08-17 11:45:13', NULL, '2025-08-16 17:17:24', '2025-08-17 11:45:13'),
(8, 'App\\Models\\User', 3, 'auth_token', '2af93ed9221f2c86c111b8b3704b3c512b4eac9913afca50bcd05794ebf22f55', '[\"*\"]', NULL, NULL, '2025-08-16 20:26:21', '2025-08-16 20:26:21'),
(9, 'App\\Models\\User', 3, 'auth_token', '81b2f4fe33f40a828aee1d706cf461ab16e80b77db29ab585cdccc6732692881', '[\"*\"]', '2025-08-16 20:28:23', NULL, '2025-08-16 20:27:10', '2025-08-16 20:28:23'),
(10, 'App\\Models\\User', 3, 'auth_token', '4e9d6a510947b84a3c1b07a47c33bfd68552602180617824fc13e32425f7d3a3', '[\"*\"]', '2025-08-16 20:37:18', NULL, '2025-08-16 20:36:53', '2025-08-16 20:37:18'),
(11, 'App\\Models\\BrandOwner', 2, 'brand-owner-token', '9fdc47d5afbc200f021392f500e5ff418c8a5caabe040895adccfabb3c5bf255', '[\"*\"]', NULL, NULL, '2025-08-16 20:37:54', '2025-08-16 20:37:54'),
(12, 'App\\Models\\User', 4, 'auth_token', 'bcc416b92f8bcbf114090c5ec235cc9c8f4c0e5e68813cc40406be6003ddfa03', '[\"*\"]', NULL, NULL, '2025-08-17 10:41:40', '2025-08-17 10:41:40'),
(13, 'App\\Models\\User', 4, 'auth_token', 'b5d4041ed7b032f6df77f7f6dadebbaf352dbdae69b1b3725fd5862a69368bcc', '[\"*\"]', '2025-08-17 10:48:06', NULL, '2025-08-17 10:42:28', '2025-08-17 10:48:06'),
(14, 'App\\Models\\User', 5, 'auth_token', '6de9133a01d0921b57ca3b25a1e0c3699a3a16fb9025929a11e7b7c9795c2971', '[\"*\"]', NULL, NULL, '2025-08-17 10:54:41', '2025-08-17 10:54:41'),
(15, 'App\\Models\\User', 5, 'auth_token', '3c2746227c0ee21dea81d9a73c348651f082877af8c199ed5c04b76854bad869', '[\"*\"]', '2025-08-17 10:57:40', NULL, '2025-08-17 10:55:32', '2025-08-17 10:57:40'),
(16, 'App\\Models\\BrandOwner', 4, 'brand-owner-token', 'a84fd37cb8dd8eb28510adc3664201083de11d6cf7ef9872cadf7c7eb985d4fd', '[\"*\"]', NULL, NULL, '2025-08-17 10:58:09', '2025-08-17 10:58:09'),
(17, 'App\\Models\\User', 5, 'auth_token', '963faeb9422028227003a425252af5df0ac38efc9a5f6934be48ea9c5ac786c4', '[\"*\"]', '2025-08-17 11:01:33', NULL, '2025-08-17 10:58:46', '2025-08-17 11:01:33'),
(18, 'App\\Models\\BrandOwner', 5, 'brand-owner-token', '460429a760fcd4d9496b7d693443c2dfbbe6a81f47b1320afd377d2fbc76f92f', '[\"*\"]', '2025-08-17 11:02:58', NULL, '2025-08-17 11:02:01', '2025-08-17 11:02:58');

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `gender`, `birthdate`, `education_level`, `education`, `job`, `sector`, `city`, `birth_city`, `relationship_status`, `postal_code`, `liked_posts`, `disliked_posts`, `created_posts`, `quiz_submissions`, `ratings_given`, `role`, `notifications`, `created_at`, `updated_at`) VALUES
(1, 'jeffrey', 'jeffrey', 'jeffreyzschot@gmail.com', '2025-08-16 14:04:50', '$2y$12$YLeMa4TACauPB4iwT/162ORSwOOxM5iLFgDUVt9krcJiSWFdcV55S', NULL, 'Man', '1999-08-15', 'HBO', 'ICT', 'Developer', 'IT', 'Zwanenburg', 'Amsterdam', 'Single', '1111 XX', '[3]', '[2]', '[1, 2, 3]', '[]', '[1]', 'admin', '[{\"type\": \"idea_like\", \"idea_id\": 3, \"message\": \"👍 Je idee \'Schonere winkels\' heeft een nieuwe like gekregen!\", \"timestamp\": \"2025-08-16T19:10:41.092417Z\"}, {\"type\": \"idea_like\", \"idea_id\": 2, \"message\": \"👍 Je idee \'Nieuw burger idee\' heeft een nieuwe like gekregen!\", \"timestamp\": \"2025-08-16T19:10:43.284827Z\"}]', '2025-08-16 14:04:40', '2025-08-16 19:10:44'),
(2, 'peterjan', 'peterjan', 'peterjan@gmail.com', '2025-08-16 14:23:16', '$2y$12$ilG/a2eJ8sNDJs3MJNBkKuVl9jYYtsnoeUYDt7ImGLj/DvCqpUv8y', NULL, 'man', '9999-08-15', 'MBO', 'Installateur', 'Elektricien', 'Techniek', 'Zwanenburg', 'Haarlem', 'Single', '1116 XX', '[]', '[]', '[]', '[]', NULL, 'user', NULL, '2025-08-16 14:23:06', '2025-08-16 14:23:16'),
(3, 'jan willem', 'janwillem', 'janwillem@gmail.com', '2025-08-16 20:26:57', '$2y$12$QCmAj5jpZMTBmuSEGiCCxencRgYBCh8jF5tY5luLYTpifyz9B5WxO', NULL, 'man', '0009-08-15', 'HBO', 'Communicatie', 'Copywriter', 'Media', 'Hoofddorp', 'Amsterdam', 'Single', '1111 XX', '[]', '[]', '[]', '[]', NULL, 'user', NULL, '2025-08-16 20:26:21', '2025-08-16 20:26:57'),
(4, 'Diederik', 'diederik', 'diederik@gmail.com', '2025-08-17 10:42:17', '$2y$12$UaZX8VBjTG9KjRQI00w2EeEky2UEc/9/M/NQu.sPU6MYDRhsMkUUS', NULL, 'man', '1999-08-15', 'HBO', 'Civil engineering', 'Vakkenvuller', 'Voedselindustrie', 'Blijdorp', 'Zaandam', 'Getrouwd', '1111 XX', '[]', '[]', '[]', '[]', NULL, 'user', NULL, '2025-08-17 10:41:40', '2025-08-17 10:42:17'),
(5, 'Charlotte De Vries', 'charlottevries', 'charlottedevries@gmail.com', '2025-08-17 10:54:58', '$2y$12$zonmzkS4LabhlOMuBji.p.o0QJaX1k269vAuOh3Ln8h/XX8lqs4De', NULL, 'Vrouw', '1994-09-11', 'Universitair', 'Psychologie', 'Tandartsassistent', 'Zorg', 'Leiden', 'Leiden', 'In een relatie', '1111 PX', '[]', '[]', '[]', '[]', NULL, 'user', NULL, '2025-08-17 10:54:41', '2025-08-17 10:54:58');



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;