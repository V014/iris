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

CREATE TABLE roles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at timestamp NOT NULL DEFAULT current_timestamp()
)

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

-- CREATE TABLE assets (
--     id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     branch_id int NOT NULL,
--     name varchar(255) NOT NULL,
--     description varchar(255) DEFAULT NULL,
--     category enum('Fixed','Leased') DEFAULT NULL,
--     purchase_value int(11) DEFAULT NULL,
--     purchase_date datetime DEFAULT NULL,
--     estimated_value int(11) DEFAULT NULL,
--     created_at datetime NOT NULL,
--     updated_at timestamp NOT NULL DEFAULT current_timestamp(),
--     FOREIGN KEY (branch_id) REFERENCES branches(id)
-- );

-- CREATE TABLE employees(
-- 	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     branch_id INT NOT NULL,
--     first_name VARCHAR(255) NOT NULL,
--     last_name VARCHAR(255) NOT NULL,
--     date_of_birth DATE NOT NULL,
--     gender ENUM("Male","Female") NOT NULL,
--     marital_status ENUM("Single","Married"),
--     religion ENUM("Christian","Islamic","N/a"),
--     bank Enum("Standard Bank","National Bank","New Building Society","First Discount House","First Capital Bank","Eco Bank","Centenary Bank"),
--     bank_account_number VARCHAR(255),
--     tenure ENUM("Full-time","Part-time") NOT NULL,
--     hired_at DATETIME NOT NULL,
--     terminated_at DATETIME,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (branch_id) REFERENCES branches(id)
-- );

-- CREATE TABLE attendance(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     employee_id INT NOT NULL,
--     status ENUM("Present","Absent","Late","Leave"),
--     clock_int DATETIME NOT NULL,
--     clock_out DATETIME NOT NULL,
--     FOREIGN KEY (employee_id) REFERENCES employees(id)
-- );


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

  UNIQUE KEY uq_branch_day (branch_id, created_at),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (user_id)    REFERENCES users(id)
);

-- CREATE TABLE sales(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     branch_id INT NOT NULL,
--     user_id INT NOT NULL,
--     opening_kwh FLOAT NOT NULL,
--     closing_kwh FLOAT NOT NULL,
--     used_kwh FLOAT NOT NULL,
--     milling_revenue INT,
--     shelling_revenue INT,
--     total_daily INT,
--     unit_price INT,
--     remarks TEXT,
--     created_at DATETIME NOT NULL,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (branch_id) REFERENCES branches(id),
--     FOREIGN KEY (user_id) REFERENCES users(id)
-- );

CREATE TABLE expense_type(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
--   top half   = electricity meter readings
--   bottom half = money earned by each machine
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

-- expense_type ENUM("Electricity","Lunch","Water","Overtime","Bulb","Broom","Welding","Spares","Maintenance","Malichelo","Buckets","Neighborhood","Other") NOT NULL,
-- CREATE TABLE payroll(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     branch_id INT,
--     period DATE NOT NULL,
--     payment_date DATE NOT NULL,
--     description ENUM("Salary","Bonus","Pension","Termination"),
--     created_at DATETIME NOT NULL,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (branch_id) REFERENCES branches(id)
-- );

-- CREATE TABLE earnings(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     payroll_id INT NOT NULL,
--     employee_id INT NOT NULL,
--     remarks TEXT,
--     amount INT NOT NULL,
--     FOREIGN KEY (payroll_id) REFERENCES payroll(id)
-- );

-- CREATE TABLE deductions(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     payroll_id INT NOT NULL,
--     employee_id INT NOT NULL,
--     remarks TEXT,
--     amount INT NOT NULL,
--     FOREIGN KEY (payroll_id) REFERENCES payroll(id)
-- );

-- CREATE TABLE loans(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     employee_id INT NOT NULL,
--     payback_period ENUM("Monthly","Quarterly","Annually") NOT NULL,
--     amount INT NOT NULL,
--     interest_rate INT NOT NULL,
--     balance INT NOT NULL,
--     status ENUM("Active","Paid","Defaulted"),
--     created_at DATETIME NOT NULL,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (employee_id) REFERENCES employees(id)
-- );

-- CREATE TABLE loan_repayments(
--     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     loan_id INT NOT NULL,
--     amount INT NOT NULL,
--     created_at DATETIME NOT NULL,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (loan_id) REFERENCES loans(id)
-- );