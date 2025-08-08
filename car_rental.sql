-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2025 at 02:34 PM
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
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` int(4) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `seller_id`, `brand`, `model`, `year`, `image`, `price_per_day`, `status`, `is_available`, `created_at`, `approved`) VALUES
(1, 2, 'LandCruser', 'Toyota', 2008, 'car_6825b0d2578665.71291749.jpg', 3000.00, 'available', 0, '2025-05-15 09:16:02', 1),
(5, 4, 'LandCruser', 'Toyota', 2021, 'car_682b7105aefdb1.97164688.jpg', 80.00, 'unavailable', 1, '2025-05-19 17:57:25', 1),
(6, 2, 'LandCruser', 'mericedeci', 1995, 'car_6835701c49def7.22126184.jpg', 2300.00, 'available', 1, '2025-05-27 07:56:12', 1),
(7, 2, 'pickers', 'mericedeci', 2009, 'car_68357100e29c37.42881049.jpg', 20870.00, 'available', 1, '2025-05-27 08:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_resets`
--

CREATE TABLE `otp_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_resets`
--

INSERT INTO `otp_resets` (`id`, `user_id`, `otp_hash`, `expires_at`, `created_at`) VALUES
(1, 1, '$2y$10$oqXJPFvFAEUunO0Eqbtxxuih05Z2KChGvwSh3jo0NjWzhzcwqMYE.', '2025-05-19 15:01:49', '2025-05-19 12:51:49'),
(2, 2, '$2y$10$fIF0VarsFgHg19.iPIePg.o3qGnic8Avw0aF//IUEuxMT.9239YiW', '2025-05-19 15:08:30', '2025-05-19 12:58:30'),
(3, 1, '$2y$10$CbwqFpz/uOrmrvNJJASJneFZRjNIsUODuv7sbJglSdr/0YAvPtSVG', '2025-05-19 15:18:54', '2025-05-19 13:08:54'),
(4, 2, '$2y$10$j/5MwAi1.EEYCy59gRJEa.wFwQztS2w4Po24/JXcjCLAz9p/AhKXG', '2025-05-19 15:23:08', '2025-05-19 13:13:08'),
(5, 1, '$2y$10$AM5VQuJmx7rUXt6FgZ0Lb.xB6gaYjhBvLtFsKuWe9NjmVV5o6L9UG', '2025-05-19 15:43:56', '2025-05-19 13:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `rental_days` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('active','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `car_id`, `client_id`, `start_date`, `end_date`, `total_cost`, `status`, `created_at`) VALUES
(9, 3, 1, 3, '2025-05-19', '2025-05-20', 3000.00, 'active', '2025-05-19 14:11:21');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `deleted_at`, `is_active`, `phone`) VALUES
(1, 'NTARINDWA Jean Dieudonne', 'jeandieudonnentarindwa9@gmail.com', '$2y$10$OD219JBMHdQIginffxu7.O2NwV9vMw2rFUSD5/PAm.Aszjmw2L7A6', 'admin', '2025-05-15 08:22:41', NULL, 1, ''),
(2, 'Eric Irakoze', 'irakozeeric@gmail.com', '$2y$10$8V2GuX.Gr9XKNZKxBVGCCeqja1VTTPHtu3Co3w5lz3JN76QwK2zTe', 'seller', '2025-05-15 08:41:24', NULL, 1, ''),
(3, 'Dieudonne Ntarindwa', 'jeandieudonnentarindwa@gmail.com', '$2y$10$JA5UMGdPnAyMCR7u3CjbaOa7PuxT1N2sbdIGTyb6JnSDhXVqVlV2C', 'client', '2025-05-19 13:54:34', '2025-06-04 07:17:47', 1, ''),
(4, 'Ygues', 'ygues9@gmail.com', '$2y$10$QLGyucIFWVE4IV/nPY5ot.5..8srO9Ka.WfVmCJ2NugBT1cDUzghC', 'seller', '2025-05-19 17:51:46', '2025-06-04 07:17:24', 1, ''),
(5, 'test', 'test@test.com', '$2y$10$cuHbdcGRccZgRFI78agoBehfwzWmhSofrPGDUQesHQ2WxONO6fDLa', 'client', '2025-05-28 19:34:31', NULL, 1, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otp_resets`
--
ALTER TABLE `otp_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `fk_client` (`client_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_resets`
--
ALTER TABLE `otp_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `otp_resets`
--
ALTER TABLE `otp_resets`
  ADD CONSTRAINT `otp_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `fk_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
