-- =============================================================================
--  Estructura de la base de datos · Experiencia de cumpleaños
--  MySQL 5.7+ / MariaDB 10.3+ (InfinityFree) · utf8mb4
-- =============================================================================

CREATE TABLE IF NOT EXISTS `settings` (
  `k` VARCHAR(64) NOT NULL,
  `v` MEDIUMTEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `alt` VARCHAR(255) NOT NULL DEFAULT '',
  `mime` VARCHAR(32) NOT NULL DEFAULT 'image/webp',
  `width` INT UNSIGNED NOT NULL,
  `height` INT UNSIGNED NOT NULL,
  `data` MEDIUMBLOB NOT NULL,
  `thumb` MEDIUMBLOB NULL,
  `thumb_width` INT UNSIGNED NOT NULL DEFAULT 0,
  `thumb_height` INT UNSIGNED NOT NULL DEFAULT 0,
  `bytes` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_photos_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `story` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `position` INT NOT NULL DEFAULT 0,
  `date_label` VARCHAR(160) NOT NULL DEFAULT '',
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `photo_slug` VARCHAR(80) NULL,
  PRIMARY KEY (`id`),
  KEY `ix_story_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `position` INT NOT NULL DEFAULT 0,
  `photo_slug` VARCHAR(80) NOT NULL,
  `caption` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ix_gallery_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `moments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `position` INT NOT NULL DEFAULT 0,
  `layout` VARCHAR(16) NOT NULL DEFAULT 'split',
  `date_label` VARCHAR(160) NOT NULL DEFAULT '',
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `author` VARCHAR(120) NOT NULL DEFAULT '',
  `place` VARCHAR(160) NOT NULL DEFAULT '',
  `photo_slug` VARCHAR(80) NULL,
  PRIMARY KEY (`id`),
  KEY `ix_moments_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL,
  `message` TEXT NOT NULL,
  `ip_hash` CHAR(16) NOT NULL DEFAULT '',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ix_messages_ip` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `display_name` VARCHAR(120) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(255) NOT NULL,
  `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME NULL,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
