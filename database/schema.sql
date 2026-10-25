-- ═══════════════════════════════════════════════════════════════
-- USERS
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    2FA_secret VARCHAR(255),
    role ENUM("System-admin","Machine-operator","Branch-manager","Accountant","Business-owner") NOT NULL,
    status ENUM("Active","Inactive","Terminated") NOT NULL,
    contact VARCHAR(10),
    password_reset ENUM("True","False"),
    last_seen DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ═══════════════════════════════════════════════════════════════
-- USER LOGS  (audit trail; target_id points at the affected row)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE user_logs(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action ENUM("Create","Read","Update","Delete","Login","Logout") NOT NULL,
    target ENUM('System','System settings','User account','Sale','Expense','Branch','Revenue') NOT NULL,
    target_id INT DEFAULT NULL,
    details VARCHAR(255) NOT NULL,
    status ENUM("Success","Failure") NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ═══════════════════════════════════════════════════════════════
-- ROLES
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE roles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at timestamp NOT NULL DEFAULT current_timestamp()
)

-- ═══════════════════════════════════════════════════════════════
-- ROLE PERMISSIONS
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE role_permissions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL,
    updated_at timestamp NOT NULL DEFAULT current_timestamp()
)

-- ═══════════════════════════════════════════════════════════════
-- BRANCHES  (electricity_tariff = current MWK per kWh, default 295)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE branches (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    location VARCHAR(255) NOT NULL,
    status ENUM('Active','Inactive') NOT NULL,
    milling_price INT NOT NULL,
    shelling_price INT NOT NULL,
    electricity_tariff INT NOT NULL DEFAULT 295,
    created_at datetime NOT NULL,
    updated_at timestamp NOT NULL DEFAULT current_timestamp()
);

-- ─────────────────────────────────────────────
-- DAILY BRANCH LOG
-- One row per branch per day, mirroring the physical book:
--   top half   = electricity meter readings
--   bottom half = money earned by each machine
-- ─────────────────────────────────────────────
CREATE TABLE daily_branch_log (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  branch_id INT NOT NULL,
  user_id INT NOT NULL,

  -- Electricity (kWh)
  opening_kwh FLOAT NOT NULL,
  closing_kwh FLOAT NOT NULL,
  used_kwh FLOAT AS (closing_kwh - opening_kwh) STORED,
  electricity_tariff_at_record INT NOT NULL,       -- snapshot of branches.electricity_tariff
  electricity_cost INT AS (
    ROUND((closing_kwh - opening_kwh) * electricity_tariff_at_record)
  ) STORED,

  -- Revenue (money earned by each machine)
  milling_revenue INT NOT NULL,
  shelling_revenue INT NOT NULL,
  total_revenue INT AS (milling_revenue + shelling_revenue) STORED,

  -- Efficiency KPI (NOT a price)
  revenue_per_kwh DECIMAL(10,2) AS (
    (milling_revenue + shelling_revenue) / NULLIF(closing_kwh - opening_kwh, 0)
  ) STORED,

  remarks TEXT,
  log_date DATE NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uq_branch_day (branch_id, log_date),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (user_id)    REFERENCES users(id)
);

-- ═══════════════════════════════════════════════════════════════
-- EXPENSES
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE expenses(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    user_id INT NOT NULL,
    expense_type_id INT NOT NULL,
    description TEXT,
    quantity INT NOT NULL,
    amount INT NOT NULL,
    is_void BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATE NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (expense_id) REFERENCES expense_type(id) 
);

-- ═══════════════════════════════════════════════════════════════
-- EXPENSE TYPE
-- ═══════════════════════════════════════════════════════════════
CREATE TABLE expense_type(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM("Daily-operations","Maintenance-repairs","Fixed-regulatory"),
    created_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- PROFIT VIEW
-- mwk_per_kwh — is your efficiency KPI
-- ─────────────────────────────────────────────
CREATE VIEW v_daily_branch_performance AS
SELECT
  log.branch_id,
  log.log_date                                AS day,
  log.milling_revenue + log.shelling_revenue  AS revenue,
  log.electricity_cost                        AS electricity_cost,
  COALESCE(exp.other_expenses, 0)             AS other_expenses,
  (log.milling_revenue + log.shelling_revenue)
    - log.electricity_cost
    - COALESCE(exp.other_expenses, 0)         AS net_profit,
  log.used_kwh                              AS kwh_used,
  log.revenue_per_kwh                         AS mwk_per_kwh
FROM daily_branch_log log
LEFT JOIN (
  SELECT branch_id, created_at AS d, SUM(amount) AS other_expenses
  FROM expenses
  WHERE NOT is_void
  GROUP BY branch_id, created_at
) exp ON exp.branch_id = log.branch_id AND exp.d = log.log_date;


-- ─────────────────────────────────────────────
-- AUDIT TRIGGERS
-- One row per branch per day, mirroring the physical book:
-- top half   = electricity meter readings
-- bottom half = money earned by each machine
-- ─────────────────────────────────────────────

DELIMITER //

CREATE TRIGGER trg_daily_log_update
AFTER UPDATE ON daily_branch_log
FOR EACH ROW
BEGIN
  IF OLD.milling_revenue <> NEW.milling_revenue OR OLD.shelling_revenue <> NEW.shelling_revenue
     OR OLD.opening_kwh <> NEW.opening_kwh
     OR OLD.closing_kwh <> NEW.closing_kwh THEN
    INSERT INTO user_logs (user_id, action, target, target_id, details, status)
    VALUES (
      NEW.user_id, 'Update', 'Sale', NEW.id,
      CONCAT('old: milling_revenue=', OLD.milling_revenue, ', shelling_revenue=', OLD.shelling_revenue,
             ', open=', OLD.opening_kwh, ', close=', OLD.closing_kwh,
             ' | new: milling_revenue=', NEW.milling_revenue, ', shelling_revenue=', NEW.shelling_revenue,
             ', open=', NEW.opening_kwh, ', close=', NEW.closing_kwh),
      'Success'
    );
  END IF;
END//

DELIMITER ;

--
-- Dumping data for table `expense_type`
--

INSERT INTO `expense_type` (`id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Electricity', 'Daily operations', '2026-10-16 17:40:21', '2026-10-16 15:41:11'),
(2, 'Lunch', 'Daily operations', '2026-10-16 17:40:21', '2026-10-16 15:41:11'),
(3, 'Overtime', 'Daily operations', '2026-10-16 17:41:11', '2026-10-16 15:41:41'),
(4, 'Water', 'Daily operations', '2026-10-16 17:41:11', '2026-10-16 15:41:41'),
(5, 'Bulb', 'Daily operations', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(6, 'Broom', 'Daily operations', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(7, 'Bolts', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(8, 'Buckets', 'Daily operations', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(9, 'Malichelo', 'Daily operations', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(10, 'Neighborhood watch', 'Daily operations', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(11, 'Welding milling machine', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(12, 'Welding shelling machine', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(13, 'Pulley', 'Maintenance-repairs', '2026-10-25 17:47:35', '2026-10-25 16:47:48'),
(14, 'Business License', 'Fixed-regulatory', '2026-10-25 17:47:51', '2026-10-25 16:48:04');

-- --------------------------------------------------------