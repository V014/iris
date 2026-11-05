-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 03:48 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iris`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `milling_price` int(11) NOT NULL,
  `shelling_price` int(11) NOT NULL,
  `electricity_tariff` int(11) NOT NULL DEFAULT 295,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_branch_log`
--

CREATE TABLE `daily_branch_log` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `opening_kwh` float NOT NULL,
  `closing_kwh` float NOT NULL,
  `used_kwh` float GENERATED ALWAYS AS (`closing_kwh` - `opening_kwh`) STORED,
  `electricity_tariff_at_record` int(11) NOT NULL,
  `electricity_cost` int(11) GENERATED ALWAYS AS (round((`closing_kwh` - `opening_kwh`) * `electricity_tariff_at_record`,0)) STORED,
  `milling_revenue` int(11) NOT NULL,
  `shelling_revenue` int(11) NOT NULL,
  `total_revenue` int(11) GENERATED ALWAYS AS (`milling_revenue` + `shelling_revenue`) STORED,
  `revenue_per_kwh` decimal(10,2) GENERATED ALWAYS AS ((`milling_revenue` + `shelling_revenue`) / nullif(`closing_kwh` - `opening_kwh`,0)) STORED,
  `remarks` text DEFAULT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT 0,
  `log_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expense_type_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT 0,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_type`
--

CREATE TABLE `expense_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Daily operations','Maintenance-repairs','Fixed-regulatory') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_type`
--

INSERT INTO `expense_type` (`id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Electricity', 'Daily operations', '2026-10-16 17:40:21', '2026-10-27 10:33:24'),
(2, 'Lunch', 'Daily operations', '2026-10-16 17:40:21', '2026-10-27 10:33:24'),
(3, 'Overtime', 'Daily operations', '2026-10-16 17:41:11', '2026-10-27 10:33:24'),
(4, 'Water', 'Daily operations', '2026-10-16 17:41:11', '2026-10-27 10:33:24'),
(5, 'Bulb', 'Daily operations', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(6, 'Broom', 'Daily operations', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(7, 'Bolts', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(8, 'Buckets', 'Daily operations', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(9, 'Malichelo', 'Daily operations', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(10, 'Neighborhood watch', 'Daily operations', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(11, 'Welding milling machine', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(12, 'Welding shelling machine', 'Maintenance-repairs', '2026-10-16 17:41:55', '2026-10-27 10:33:24'),
(13, 'Pulley', 'Maintenance-repairs', '2026-10-25 17:47:35', '2026-10-27 10:33:24'),
(14, 'Business License', 'Fixed-regulatory', '2026-10-25 17:47:51', '2026-10-27 10:33:24');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System administrator', 'Controls user and branch creation while monitoring activity logs.', 'Active', '2026-11-05 15:37:37', '2026-11-05 14:42:28'),
(2, 'Machine operator', 'Populates sales and expense data, directly answering to their branch manager.', 'Active', '2026-11-05 15:37:37', '2026-11-05 14:42:28'),
(3, 'Branch manager', 'Reviews sales and expenses data, approves or voids expenses and reports directly to the accountant.', 'Active', '2026-11-05 15:42:35', '2026-11-05 14:45:21'),
(4, 'Accountant', 'Reviews finances of all branches, approves larger business payments and reports directly to the business owner.', '', '2026-11-05 15:42:35', '2026-11-05 14:45:21'),
(5, 'Business owner', 'Reviews final report of business performance and approves strategic business decisions and price adjustments.', '', '2026-11-05 15:45:21', '2026-11-05 14:46:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'View dashboard', '2026-11-05 15:33:26', '2026-11-05 14:34:33'),
(2, 'Manage users', '2026-11-05 15:33:26', '2026-11-05 14:34:33'),
(3, 'View audit log', '2026-11-05 15:34:45', '2026-11-05 14:35:38'),
(4, 'Manage system settings', '2026-11-05 15:34:45', '2026-11-05 14:35:38'),
(5, 'Process sales', '2026-11-05 15:35:38', '2026-11-05 14:36:43'),
(6, 'Process expenses', '2026-11-05 15:35:38', '2026-11-05 14:36:43'),
(7, 'View reports', '2026-11-05 15:36:43', '2026-11-05 14:37:02'),
(8, 'Manage roles', '2026-11-05 15:36:43', '2026-11-05 14:37:02'),
(9, 'Manage finances', '2026-11-05 15:37:02', '2026-11-05 14:37:16'),
(10, 'Export data', '2026-11-05 15:37:02', '2026-11-05 14:37:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `two_fa_secret` varchar(255) DEFAULT NULL,
  `role` enum('System-admin','Machine-operator','Branch-manager','Accountant','Business-owner') NOT NULL,
  `status` enum('Active','Inactive','Terminated') NOT NULL,
  `contact` varchar(10) NOT NULL DEFAULT '',
  `password_reset` tinyint(1) NOT NULL DEFAULT 0,
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('Create','Read','Update','Delete','Login','Logout') NOT NULL,
  `target` enum('System','System settings','User account','Branch','Daily log','Expense','Expense type') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` varchar(255) NOT NULL,
  `status` enum('Success','Failure') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_daily_branch_performance`
-- (See below for the actual view)
--
CREATE TABLE `v_daily_branch_performance` (
`branch_id` int(11)
,`day` date
,`revenue` int(11)
,`electricity_cost` int(11)
,`other_expenses` decimal(32,0)
,`net_profit` decimal(33,0)
,`kwh_used` double
,`mwk_earned_per_kwh` decimal(10,2)
,`tariff_at_record` int(11)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_branch_log`
--
ALTER TABLE `daily_branch_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_branch_day` (`branch_id`,`log_date`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_branch_date` (`branch_id`,`expense_date`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expense_type_id` (`expense_type_id`);

--
-- Indexes for table `expense_type`
--
ALTER TABLE `expense_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_branch_log`
--
ALTER TABLE `daily_branch_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_type`
--
ALTER TABLE `expense_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `v_daily_branch_performance`
--
DROP TABLE IF EXISTS `v_daily_branch_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_branch_performance`  AS SELECT `log`.`branch_id` AS `branch_id`, `log`.`log_date` AS `day`, `log`.`total_revenue` AS `revenue`, `log`.`electricity_cost` AS `electricity_cost`, coalesce(`exp`.`other_expenses`,0) AS `other_expenses`, `log`.`total_revenue`- `log`.`electricity_cost` - coalesce(`exp`.`other_expenses`,0) AS `net_profit`, `log`.`used_kwh` AS `kwh_used`, `log`.`revenue_per_kwh` AS `mwk_earned_per_kwh`, `log`.`electricity_tariff_at_record` AS `tariff_at_record` FROM (`daily_branch_log` `log` left join (select `expenses`.`branch_id` AS `branch_id`,`expenses`.`expense_date` AS `d`,sum(`expenses`.`amount`) AS `other_expenses` from `expenses` where `expenses`.`is_void` = 0 group by `expenses`.`branch_id`,`expenses`.`expense_date`) `exp` on(`exp`.`branch_id` = `log`.`branch_id` and `exp`.`d` = `log`.`log_date`)) WHERE `log`.`is_void` = 0union allselect `e`.`branch_id` AS `branch_id`,`e`.`expense_date` AS `day`,0 AS `revenue`,0 AS `electricity_cost`,sum(`e`.`amount`) AS `other_expenses`,-sum(`e`.`amount`) AS `net_profit`,0 AS `kwh_used`,NULL AS `mwk_earned_per_kwh`,NULL AS `tariff_at_record` from `expenses` `e` where `e`.`is_void` = 0 and !exists(select 1 from `daily_branch_log` `l` where `l`.`branch_id` = `e`.`branch_id` and `l`.`log_date` = `e`.`expense_date` and `l`.`is_void` = 0 limit 1) group by `e`.`branch_id`,`e`.`expense_date`  ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_branch_log`
--
ALTER TABLE `daily_branch_log`
  ADD CONSTRAINT `daily_branch_log_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `daily_branch_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_type` (`id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
