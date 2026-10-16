-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 05:44 PM
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
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_type`
--

CREATE TABLE `expense_type` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_type`
--

INSERT INTO `expense_type` (`id`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Electricity', '2026-10-16 17:40:21', '2026-10-16 15:41:11'),
(2, 'Lunch', '2026-10-16 17:40:21', '2026-10-16 15:41:11'),
(3, 'Overtime', '2026-10-16 17:41:11', '2026-10-16 15:41:41'),
(4, 'Water', '2026-10-16 17:41:11', '2026-10-16 15:41:41'),
(5, 'Bulb', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(6, 'Broom', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(7, 'Welding', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(8, 'Buckets', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(9, 'Malichelo', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(10, 'Neighborhood watch', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(11, 'Spares', '2026-10-16 17:41:55', '2026-10-16 15:43:56'),
(12, 'Maintenance', '2026-10-16 17:41:55', '2026-10-16 15:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `opening_units` float NOT NULL,
  `closing_units` float NOT NULL,
  `used_units` float NOT NULL,
  `miller` int(11) DEFAULT NULL,
  `sheller` int(11) DEFAULT NULL,
  `total_daily` int(11) DEFAULT NULL,
  `unit_price` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('Create','Read','Update','Delete','Login','Logout') NOT NULL,
  `target` enum('System','System settings','User account') NOT NULL,
  `details` varchar(255) NOT NULL,
  `status` enum('Success','Failure') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expense_id` (`expense_id`);

--
-- Indexes for table `expense_type`
--
ALTER TABLE `expense_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_type`
--
ALTER TABLE `expense_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`expense_id`) REFERENCES `expense_type` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
