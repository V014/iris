-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 25, 2026 at 05:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `electricity_tarrif` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `description`, `location`, `status`, `milling_price`, `shelling_price`, `electricity_tarrif`, `created_at`, `updated_at`) VALUES
(1, 'Makata', 'Headquarters', '-15.769917, 35.037389', 'Active', 1000, 750, 0, '2026-10-17 13:31:52', '2026-10-17 11:33:02');

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
  `log_date` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `daily_branch_log`
--
DELIMITER $$
CREATE TRIGGER `trg_daily_log_update` AFTER UPDATE ON `daily_branch_log` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

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
  `is_void` tinyint(1) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_type`
--

CREATE TABLE `expense_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Daily operations','Maintenance-repairs','Fixed-regulatory','') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `2FA_secret` varchar(255) DEFAULT NULL,
  `role` enum('System-admin','Machine-operator','Branch-manager','Accountant','Business-owner') NOT NULL,
  `status` enum('Active','Inactive','Terminated') NOT NULL,
  `contact` varchar(10) DEFAULT NULL,
  `password_reset` enum('True','False') NOT NULL,
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `2FA_secret`, `role`, `status`, `contact`, `password_reset`, `last_seen`, `created_at`, `updated_at`) VALUES
(1, 'void', '$2y$10$nqcQ9YLHRhXZxWdfzwRYruXene8N1f4qLrGhXnhDOEA7BYjomz89C', NULL, 'System-admin', 'Active', '0996335639', 'True', NULL, '2026-10-17 11:34:06', '2026-10-17 11:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('Create','Read','Update','Delete','Login','Logout') NOT NULL,
  `target` enum('System','System settings','User account') NOT NULL,
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
,`revenue` bigint(12)
,`electricity_cost` int(11)
,`other_expenses` decimal(32,0)
,`net_profit` decimal(33,0)
,`kwh_used` float
,`mwk_per_kwh` decimal(10,2)
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
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expense_id` (`expense_type_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_branch_performance`  AS SELECT `log`.`branch_id` AS `branch_id`, `log`.`log_date` AS `day`, `log`.`milling_revenue`+ `log`.`shelling_revenue` AS `revenue`, `log`.`electricity_cost` AS `electricity_cost`, coalesce(`exp`.`other_expenses`,0) AS `other_expenses`, `log`.`milling_revenue`+ `log`.`shelling_revenue` - `log`.`electricity_cost` - coalesce(`exp`.`other_expenses`,0) AS `net_profit`, `log`.`used_kwh` AS `kwh_used`, `log`.`revenue_per_kwh` AS `mwk_per_kwh` FROM (`daily_branch_log` `log` left join (select `expenses`.`branch_id` AS `branch_id`,`expenses`.`created_at` AS `d`,sum(`expenses`.`amount`) AS `other_expenses` from `expenses` where `expenses`.`is_void` = 0 group by `expenses`.`branch_id`,`expenses`.`created_at`) `exp` on(`exp`.`branch_id` = `log`.`branch_id` and `exp`.`d` = `log`.`log_date`)) ;

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
