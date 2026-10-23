-- ============================================================
-- LIVE DB SQL  — O/E (Print) + Pre-Con-Conceptual features
-- MariaDB / phpMyAdmin style. Run each block.
-- Use "IF NOT EXISTS" so it's safe to run even if some already exist.
-- ============================================================

-- 1) oe_master (master list of O/E fields)
CREATE TABLE IF NOT EXISTS oe_master (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100)     NOT NULL,
  short_name  VARCHAR(30)      NULL,
  unit        VARCHAR(20)      NULL,
  input_type  VARCHAR(20)      NOT NULL,
  sort_order  INT              NOT NULL DEFAULT 0,
  active      TINYINT(1)       NOT NULL DEFAULT 1,
  created_at  TIMESTAMP        NULL,
  updated_at  TIMESTAMP        NULL,
  -- field_code (optional; no longer required by the app)
  field_code  INT              NULL,
  PRIMARY KEY (id),
  INDEX idx_oe_master_sort (sort_order),
  INDEX idx_oe_master_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1b) Seed core O/E fields (adjust names/units to your language/case)
INSERT IGNORE INTO `oe_master` (`id`, `name`, `short_name`, `unit`, `input_type`, `sort_order`, `active`, `field_code`) VALUES
(1,  'Pulse',           'Pulse',           'beats', 'text', 1, 1, 1),
(2,  'Blood Pressure',  'BP',              'mmHg',  'text', 2, 1, 2),
(3,  'Temperature',     'Temp',            '°F',    'text', 3, 1, 3),
(4,  'Weight',          'Wt',              'kg',    'text', 4, 1, 4),
(5,  'Height',          'Ht',              'cm',    'text', 5, 1, 5),
(6,  'O2 (SpO2)',       'SpO2',            '%',     'text', 6, 1, 6),
(7,  'Resp Rate',       'RR',              '/min',  'text', 7, 1, 7);


-- 2) prescription_oe — per-prescription O/E values (the print source)
CREATE TABLE IF NOT EXISTS `prescription_oe` (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    prescription_id  BIGINT UNSIGNED NOT NULL,
    oe_master_id     BIGINT UNSIGNED NOT NULL,
    value            VARCHAR(255) NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_prescription_oe (prescription_id, oe_master_id),
    INDEX idx_po_prescription (prescription_id),
    INDEX idx_po_oe (oe_master_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3) preconassessment_values — per-assessment field values
CREATE TABLE IF NOT EXISTS `preconassessment_values` (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    assessment_id  BIGINT UNSIGNED NOT NULL,
    oe_master_id   BIGINT UNSIGNED NOT NULL,
    value          VARCHAR(255) NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    PRIMARY KEY (id),
    INDEX idx_pcv_assessment (assessment_id),
    INDEX idx_pcv_oe (oe_master_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4) Add O/E snapshot columns to `prescriptions` table (if missing)
ALTER TABLE `prescriptions`
    ADD COLUMN IF NOT EXISTS `oe_nursing_id` BIGINT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS `oe_pulse` VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `oe_bp`    VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `oe_temp`  VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `oe_weight` VARCHAR(50) NULL;

CREATE INDEX IF NOT EXISTS idx_prescriptions_oe_nursing ON `prescriptions` (`oe_nursing_id`);


-- 5) Add `patient_id` + `status` to `preconconceptual` (if not exists)
ALTER TABLE `preconconceptual`
    ADD COLUMN IF NOT EXISTS `patient_id` BIGINT UNSIGNED NULL AFTER `patientcode`,
    ADD COLUMN IF NOT EXISTS `status` TINYINT(1) NOT NULL DEFAULT 0 AFTER `notes`;