-- Admission/Discharge manual SQL for MySQL/phpMyAdmin.
-- Your `discharges` table already matches the required workflow:
-- status = pending   => waiting for manager approval
-- status = completed => manager approved, final discharge allowed

CREATE TABLE IF NOT EXISTS `discharge_medicines` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `patientcode` VARCHAR(50) NULL,
    `patient_id` BIGINT UNSIGNED NULL,
    `admission_id` BIGINT UNSIGNED NULL,
    `medicine_name` VARCHAR(255) NULL,
    `strength` VARCHAR(100) NULL,
    `dose` VARCHAR(100) NULL,
    `morning` VARCHAR(100) NULL,
    `noon` VARCHAR(100) NULL,
    `night` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_discharge_medicines_patientcode` (`patientcode`),
    INDEX `idx_discharge_medicines_admission_id` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If `discharge_medicines` already existed before, run only the ALTER lines
-- for columns that are missing in your table.
--
-- ALTER TABLE `discharge_medicines` ADD COLUMN `patientcode` VARCHAR(50) NULL AFTER `id`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `patient_id` BIGINT UNSIGNED NULL AFTER `patientcode`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `admission_id` BIGINT UNSIGNED NULL AFTER `patient_id`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `medicine_name` VARCHAR(255) NULL AFTER `admission_id`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `strength` VARCHAR(100) NULL AFTER `medicine_name`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `dose` VARCHAR(100) NULL AFTER `strength`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `morning` VARCHAR(100) NULL AFTER `dose`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `noon` VARCHAR(100) NULL AFTER `morning`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `night` VARCHAR(100) NULL AFTER `noon`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `night`;
-- ALTER TABLE `discharge_medicines` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
-- ALTER TABLE `discharge_medicines` ADD INDEX `idx_discharge_medicines_patientcode` (`patientcode`);
-- ALTER TABLE `discharge_medicines` ADD INDEX `idx_discharge_medicines_admission_id` (`admission_id`);

-- Optional safety checks for `discharges`.
-- Run these only if any listed column is missing from your existing `discharges` table.
--
-- ALTER TABLE `discharges` ADD COLUMN `patient_id` BIGINT UNSIGNED NOT NULL AFTER `id`;
-- ALTER TABLE `discharges` ADD COLUMN `admission_id` BIGINT UNSIGNED NOT NULL AFTER `patient_id`;
-- ALTER TABLE `discharges` ADD COLUMN `discharge_date` DATE NULL AFTER `admission_id`;
-- ALTER TABLE `discharges` ADD COLUMN `diagnosis` VARCHAR(300) NULL AFTER `discharge_date`;
-- ALTER TABLE `discharges` ADD COLUMN `doctor_name` VARCHAR(150) NULL AFTER `diagnosis`;
-- ALTER TABLE `discharges` ADD COLUMN `ward` VARCHAR(100) NULL AFTER `doctor_name`;
-- ALTER TABLE `discharges` ADD COLUMN `reg_no` VARCHAR(100) NULL AFTER `ward`;
-- ALTER TABLE `discharges` ADD COLUMN `operation_name` VARCHAR(300) NULL AFTER `reg_no`;
-- ALTER TABLE `discharges` ADD COLUMN `operation_method` TEXT NULL AFTER `operation_name`;
-- ALTER TABLE `discharges` ADD COLUMN `surgeon` VARCHAR(200) NULL AFTER `operation_method`;
-- ALTER TABLE `discharges` ADD COLUMN `anesthetist` VARCHAR(200) NULL AFTER `surgeon`;
-- ALTER TABLE `discharges` ADD COLUMN `assistant_1` VARCHAR(200) NULL AFTER `anesthetist`;
-- ALTER TABLE `discharges` ADD COLUMN `assistant_2` VARCHAR(200) NULL AFTER `assistant_1`;
-- ALTER TABLE `discharges` ADD COLUMN `investigations` TEXT NULL AFTER `assistant_2`;
-- ALTER TABLE `discharges` ADD COLUMN `blood_group` VARCHAR(10) NULL AFTER `investigations`;
-- ALTER TABLE `discharges` ADD COLUMN `drug_allergy` VARCHAR(200) NULL AFTER `blood_group`;
-- ALTER TABLE `discharges` ADD COLUMN `notes` TEXT NULL AFTER `drug_allergy`;
-- ALTER TABLE `discharges` ADD COLUMN `follow_up_date` DATE NULL AFTER `notes`;
-- ALTER TABLE `discharges` ADD COLUMN `status` ENUM('pending','completed') NOT NULL DEFAULT 'completed' AFTER `follow_up_date`;
-- ALTER TABLE `discharges` ADD COLUMN `discharged_by` BIGINT UNSIGNED NULL AFTER `status`;

