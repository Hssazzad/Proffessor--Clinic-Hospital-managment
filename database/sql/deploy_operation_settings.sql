-- ============================================================
-- Operation Consent + Settings (Assistant Surgeons / Anesthesiologists / Operation Names)
-- Run on the LIVE database (phpMyAdmin / mysql CLI)
-- ============================================================

-- ------------------------------------------------------------
-- 1) Assistant Surgeons
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `assistant_surgeons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2) Anesthesiologists
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `anesthesiologists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3) Operation Names
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `operation_names` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4) operation_consents: add NEW columns
--    (ONLY needed if the live table still has the ORIGINAL
--     schema. If a column already exists this will error —
--     skip the ones already present.)
-- ------------------------------------------------------------
ALTER TABLE `operation_consents`
  ADD COLUMN `patient_id` bigint unsigned NULL AFTER `admission_id`,
  ADD COLUMN `ward_no` varchar(255) NULL AFTER `district`,
  ADD COLUMN `bed_no` varchar(255) NULL AFTER `ward_no`,
  ADD COLUMN `disease_name` varchar(255) NULL AFTER `bed_no`,
  ADD COLUMN `operation_time` varchar(255) NULL AFTER `operation_date`,
  ADD COLUMN `assistant_surgeon` varchar(255) NULL AFTER `surgeon_name`,
  ADD COLUMN `anesthesiologist` varchar(255) NULL AFTER `anesthetist`,
  ADD COLUMN `operation_note` text NULL AFTER `anesthesiologist`,
  ADD COLUMN `signatory_name` varchar(255) NULL AFTER `relation`,
  ADD COLUMN `signatory_relation` varchar(255) NULL AFTER `signatory_name`,
  ADD COLUMN `doctor_signature_name` varchar(255) NULL AFTER `signatory_relation`;

-- ------------------------------------------------------------
-- 5) Settings submenu entries (run once)
-- ------------------------------------------------------------
INSERT INTO `submenu` (`menuparentcode`, `submenucode`, `submenuname`, `method`, `position`) VALUES
(30, 301012, 'Assistant Surgeons', 'assistant-surgeons', 12),
(30, 301013, 'Anesthesiologists', 'anesthesiologists', 13),
(30, 301014, 'Operation Names', 'operation-names', 14);

-- ------------------------------------------------------------
-- 6) Assign the new submenus to user pins 1,2,3,5
-- ------------------------------------------------------------
INSERT INTO `user_sub_menu` (`userpin`, `menuparentcode`, `submenucode`, `position`) VALUES
(1, '30', '301012', 0),
(1, '30', '301013', 0),
(1, '30', '301014', 0),
(2, '30', '301012', 0),
(2, '30', '301013', 0),
(2, '30', '301014', 0),
(3, '30', '301012', 0),
(3, '30', '301013', 0),
(3, '30', '301014', 0),
(5, '30', '301012', 0),
(5, '30', '301013', 0),
(5, '30', '301014', 0);

-- ------------------------------------------------------------
-- 7) Doctors: only Name is used — make the other fields nullable
--    (reg_no / RateCode are already nullable)
-- ------------------------------------------------------------
ALTER TABLE `doctors`
  MODIFY `reg_no` varchar(12) NULL DEFAULT NULL,
  MODIFY `speciality` varchar(25) NULL DEFAULT NULL,
  MODIFY `contact` varchar(12) NULL DEFAULT NULL,
  MODIFY `Posting` varchar(25) NULL DEFAULT NULL,
  MODIFY `RateCode` varchar(12) NULL DEFAULT NULL;
