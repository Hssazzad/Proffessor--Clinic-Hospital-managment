-- Create configpost table (Post under Village)
CREATE TABLE IF NOT EXISTS `configpost` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL,
  `village_code` VARCHAR(100) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `configpost_village_code_index` (`village_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add post column to patients table
ALTER TABLE `patients` ADD COLUMN `post` VARCHAR(50) NULL DEFAULT NULL AFTER `village`;
