-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 09:58 AM
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
-- Database: `iot_plant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `system_records`
--

CREATE TABLE `system_records` (
  `id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `soil` int(11) DEFAULT NULL,
  `temp` float DEFAULT NULL,
  `hum` float DEFAULT NULL,
  `n_val` int(11) DEFAULT NULL,
  `p_val` int(11) DEFAULT NULL,
  `k_val` int(11) DEFAULT NULL,
  `record_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_records`
--

INSERT INTO `system_records` (`id`, `action_type`, `soil`, `temp`, `hum`, `n_val`, `p_val`, `k_val`, `record_time`) VALUES
(26, 'PUMP ON', 0, 28.3, 90, 0, 0, 0, '2026-04-01 10:13:34'),
(27, 'PUMP OFF', 0, 28.3, 90, 0, 0, 0, '2026-04-01 10:13:39'),
(28, 'PUMP OFF', 0, 28.1, 84, 0, 0, 256, '2026-04-01 10:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$12$u.AXId8ZFYZX0vKwLI54oOHY6a4VDpzo740MQmSy.brtfm7e.Dx4u', '2026-04-01 08:28:46'),
(2, 'Caylyn', '$2y$12$ZroKEu8jHqg5Bs1Sugj0leC7ToIdLB6Rg0YgI0kDMEJOiUoQfrIlS', '2026-04-01 08:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `system_records`
--
ALTER TABLE `system_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `system_records`
--
ALTER TABLE `system_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
