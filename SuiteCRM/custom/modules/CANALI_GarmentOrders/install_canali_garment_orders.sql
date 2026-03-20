-- CANALI Luxury Tailoring — Garment Orders table
-- Run once during setup / Quick Repair & Rebuild will also create this via SuiteCRM's vardefs.

CREATE TABLE IF NOT EXISTS `canali_garment_orders` (
  `id`                  VARCHAR(36)     NOT NULL,
  `name`                VARCHAR(255)    DEFAULT NULL,
  `date_entered`        DATETIME        DEFAULT NULL,
  `date_modified`       DATETIME        DEFAULT NULL,
  `modified_user_id`    VARCHAR(36)     DEFAULT NULL,
  `created_by`          VARCHAR(36)     DEFAULT NULL,
  `deleted`             TINYINT(1)      DEFAULT 0,
  `assigned_user_id`    VARCHAR(36)     DEFAULT NULL,

  -- Order identity
  `order_number`        VARCHAR(50)     DEFAULT NULL,
  `order_date`          DATE            DEFAULT NULL,

  -- Garment details
  `garment_type`        VARCHAR(32)     DEFAULT NULL,
  `order_status`        VARCHAR(32)     DEFAULT 'Consultation',
  `fabric_category`     VARCHAR(32)     DEFAULT NULL,
  `fabric_description`  VARCHAR(255)    DEFAULT NULL,
  `fit_style`           VARCHAR(32)     DEFAULT NULL,
  `lining_description`  VARCHAR(255)    DEFAULT NULL,
  `button_description`  VARCHAR(255)    DEFAULT NULL,
  `monogram`            VARCHAR(50)     DEFAULT NULL,

  -- Fitting schedule
  `consultation_date`   DATETIME        DEFAULT NULL,
  `fitting1_date`       DATETIME        DEFAULT NULL,
  `fitting2_date`       DATETIME        DEFAULT NULL,
  `delivery_date`       DATE            DEFAULT NULL,

  -- Financials (stored as DECIMAL to match SuiteCRM currency type)
  `total_price`         DECIMAL(26,6)   DEFAULT NULL,
  `total_price_usdollar` DECIMAL(26,6)  DEFAULT NULL,
  `deposit_paid`        DECIMAL(26,6)   DEFAULT NULL,
  `deposit_paid_usdollar` DECIMAL(26,6) DEFAULT NULL,

  -- Notes
  `garment_notes`       TEXT            DEFAULT NULL,
  `alteration_notes`    TEXT            DEFAULT NULL,

  -- Client relationship
  `contact_id`          VARCHAR(36)     DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `idx_canali_go_contact`  (`contact_id`),
  KEY `idx_canali_go_status`   (`order_status`),
  KEY `idx_canali_go_assigned` (`assigned_user_id`),
  KEY `idx_canali_go_deleted`  (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
