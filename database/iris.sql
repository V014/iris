-- ═══════════════════════════════════════════════════════════════
-- IRIS — Maize Mill Management System
-- Clean DDL. Safe to run on a fresh database.
-- ═══════════════════════════════════════════════════════════════

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `iris`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `iris`;

-- ═══════════════════════════════════════════════════════════════
-- USERS
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `users` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username`        VARCHAR(255) NOT NULL UNIQUE,
  `password`        VARCHAR(255) NOT NULL,
  `two_fa_secret`   VARCHAR(255) DEFAULT NULL,
  `role`            ENUM('System-admin','Machine-operator','Branch-manager','Accountant','Business-owner') NOT NULL,
  `status`          ENUM('Active','Inactive','Terminated') NOT NULL,
  `contact`         VARCHAR(10) NOT NULL DEFAULT '',
  `password_reset`  TINYINT(1) NOT NULL DEFAULT 0,
  `last_seen`       DATETIME DEFAULT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- USER LOGS  (audit trail; target_id points at the affected row)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `user_logs` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT(11) NOT NULL,
  `action`     ENUM('Create','Read','Update','Delete','Login','Logout') NOT NULL,
  `target`     ENUM('System','System settings','User account','Branch','Daily log','Expense','Expense type') NOT NULL,
  `target_id`  INT(11) DEFAULT NULL,
  `details`    VARCHAR(255) NOT NULL,
  `status`     ENUM('Success','Failure') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- BRANCHES  (electricity_tariff = current MWK per kWh; default 295)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `branches` (
  `id`                  INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`                VARCHAR(255) NOT NULL,
  `description`         TEXT DEFAULT NULL,
  `location`            VARCHAR(255) NOT NULL,
  `status`              ENUM('Active','Inactive') NOT NULL,
  `milling_price`       INT(11) NOT NULL,
  `shelling_price`      INT(11) NOT NULL,
  `electricity_tariff`  INT(11) NOT NULL DEFAULT 295,
  `created_at`          DATETIME NOT NULL,
  `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- DAILY BRANCH LOG
-- One row per branch per day. Mirrors one page of the physical book:
--   electricity meter readings (kWh)  +  money each machine earned (MWK)
--
-- Generated columns (never entered by hand):
--   used_kwh         = closing_kwh - opening_kwh
--   electricity_cost = used_kwh × electricity_tariff_at_record
--   total_revenue    = milling_revenue + shelling_revenue
--   revenue_per_kwh  = total_revenue / used_kwh   (efficiency KPI, NOT a price)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `daily_branch_log` (
  `id`                           INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `branch_id`                    INT(11) NOT NULL,
  `user_id`                      INT(11) NOT NULL,

  -- Electricity (kWh)
  `opening_kwh`                  FLOAT NOT NULL,
  `closing_kwh`                  FLOAT NOT NULL,
  `used_kwh`                     FLOAT AS (`closing_kwh` - `opening_kwh`) STORED,
  `electricity_tariff_at_record` INT(11) NOT NULL,
  `electricity_cost`             INT(11) AS (
    ROUND((`closing_kwh` - `opening_kwh`) * `electricity_tariff_at_record`, 0)
  ) STORED,

  -- Revenue (MWK earned by each machine)
  `milling_revenue`              INT(11) NOT NULL,
  `shelling_revenue`             INT(11) NOT NULL,
  `total_revenue`                INT(11) AS (`milling_revenue` + `shelling_revenue`) STORED,

  -- Efficiency KPI: MWK earned per kWh burned (NOT the tariff)
  `revenue_per_kwh`              DECIMAL(10,2) AS (
    (`milling_revenue` + `shelling_revenue`)
      / NULLIF(`closing_kwh` - `opening_kwh`, 0)
  ) STORED,

  `remarks`                      TEXT DEFAULT NULL,
  `is_void`                      TINYINT(1) NOT NULL DEFAULT 0,
  `log_date`                     DATE NOT NULL,
  `created_at`                   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY `uq_branch_day` (`branch_id`, `log_date`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
  FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- EXPENSE TYPE  (no trailing '' in enum)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `expense_type` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `type`       ENUM('Daily operations','Maintenance-repairs','Fixed-regulatory') NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- EXPENSES  (expense_date = business date; created_at = insert time)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `expenses` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `branch_id`       INT(11) NOT NULL,
  `user_id`         INT(11) NOT NULL,
  `expense_type_id` INT(11) NOT NULL,
  `description`     TEXT DEFAULT NULL,
  `quantity`        INT(11) NOT NULL,
  `amount`          INT(11) NOT NULL,             -- MWK total
  `is_void`         TINYINT(1) NOT NULL DEFAULT 0,
  `expense_date`    DATE NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  KEY `idx_branch_date` (`branch_id`, `expense_date`),
  FOREIGN KEY (`branch_id`)       REFERENCES `branches`(`id`),
  FOREIGN KEY (`user_id`)         REFERENCES `users`(`id`),
  FOREIGN KEY (`expense_type_id`) REFERENCES `expense_type`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- ROLES / ROLE_PERMISSIONS  (kept as in your dump; empty for now)
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE `roles` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `role_permissions` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at`  DATETIME NOT NULL,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- PROFIT VIEW
-- Left side  : daily_branch_log (voids excluded)
-- Right side : expense-only days (expense recorded, no log row)
-- ═══════════════════════════════════════════════════════════════
CREATE OR REPLACE VIEW `v_daily_branch_performance` AS
SELECT
  log.`branch_id`                                   AS branch_id,
  log.`log_date`                                    AS day,
  log.`total_revenue`                               AS revenue,
  log.`electricity_cost`                            AS electricity_cost,
  COALESCE(exp.`other_expenses`, 0)                 AS other_expenses,
  log.`total_revenue`
    - log.`electricity_cost`
    - COALESCE(exp.`other_expenses`, 0)             AS net_profit,
  log.`used_kwh`                                    AS kwh_used,
  log.`revenue_per_kwh`                             AS mwk_earned_per_kwh,
  log.`electricity_tariff_at_record`                AS tariff_at_record
FROM `daily_branch_log` log
LEFT JOIN (
  SELECT `branch_id`, `expense_date` AS d, SUM(`amount`) AS other_expenses
  FROM `expenses`
  WHERE `is_void` = 0
  GROUP BY `branch_id`, `expense_date`
) exp ON exp.`branch_id` = log.`branch_id` AND exp.`d` = log.`log_date`
WHERE log.`is_void` = 0

UNION ALL

-- Expense-only days: expense recorded, no daily log row for that branch/date
SELECT
  e.`branch_id`                                     AS branch_id,
  e.`expense_date`                                  AS day,
  0                                                 AS revenue,
  0                                                 AS electricity_cost,
  SUM(e.`amount`)                                   AS other_expenses,
  -SUM(e.`amount`)                                  AS net_profit,
  0                                                 AS kwh_used,
  NULL                                              AS mwk_earned_per_kwh,
  NULL                                              AS tariff_at_record
FROM `expenses` e
WHERE e.`is_void` = 0
  AND NOT EXISTS (
    SELECT 1 FROM `daily_branch_log` l
    WHERE l.`branch_id` = e.`branch_id`
      AND l.`log_date`  = e.`expense_date`
      AND l.`is_void`   = 0
  )
GROUP BY e.`branch_id`, e.`expense_date`;

-- ═══════════════════════════════════════════════════════════════
-- SEED DATA
-- ═══════════════════════════════════════════════════════════════

INSERT INTO `branches`
  (`id`, `name`, `description`, `location`, `status`,
   `milling_price`, `shelling_price`, `electricity_tariff`, `created_at`)
VALUES
  (1, 'Makata', 'Headquarters', '-15.769917, 35.037389', 'Active',
   1000, 750, 295, '2026-10-17 13:31:52');

INSERT INTO `expense_type` (`id`, `name`, `type`, `created_at`) VALUES
  (1,  'Electricity',                 'Daily operations',      '2026-10-16 17:40:21'),
  (2,  'Lunch',                       'Daily operations',      '2026-10-16 17:40:21'),
  (3,  'Overtime',                    'Daily operations',      '2026-10-16 17:41:11'),
  (4,  'Water',                       'Daily operations',      '2026-10-16 17:41:11'),
  (5,  'Bulb',                        'Daily operations',      '2026-10-16 17:41:55'),
  (6,  'Broom',                       'Daily operations',      '2026-10-16 17:41:55'),
  (7,  'Bolts',                       'Maintenance-repairs',   '2026-10-16 17:41:55'),
  (8,  'Buckets',                     'Daily operations',      '2026-10-16 17:41:55'),
  (9,  'Malichelo',                   'Daily operations',      '2026-10-16 17:41:55'),
  (10, 'Neighborhood watch',          'Daily operations',      '2026-10-16 17:41:55'),
  (11, 'Welding milling machine',     'Maintenance-repairs',   '2026-10-16 17:41:55'),
  (12, 'Welding shelling machine',    'Maintenance-repairs',   '2026-10-16 17:41:55'),
  (13, 'Pulley',                      'Maintenance-repairs',   '2026-10-25 17:47:35'),
  (14, 'Business License',            'Fixed-regulatory',      '2026-10-25 17:47:51');