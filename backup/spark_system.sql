-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2024 at 04:27 PM
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
-- Database: `spark_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('supervisor','housekeeping_staff') NOT NULL,
  `employee_id` varchar(100) NOT NULL,
  `email_address` varchar(50) NOT NULL,
  `verify_token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `password`, `role`, `employee_id`, `email_address`, `verify_token`) VALUES
(2, 'admin', '0000', 'supervisor', '0000', 'admin@example.com', ''),
(41, 'user1', '12345', 'housekeeping_staff', '1111', 'mav@example.com', ''),
(42, 'user2', '12345', 'housekeeping_staff', '4444', 'lanzcyrille.002@gmail.com', '1e81fb5eb0eaa8aa88344b15e2efa038');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `report_image` longblob NOT NULL,
  `description` text NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `highest_rating` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`evaluation_id`, `employee_id`, `rating`, `remark`, `report_image`, `description`, `report_id`, `created_at`, `highest_rating`) VALUES
(157, 4444, 0, '', 0x75706c6f6164732f6c612d62656c6c652d68616d696c746f6e2e6a7067, '', 140, '2024-11-03 09:19:44', 0),
(158, 4444, 0, '', 0x75706c6f6164732f6c612d62656c6c652d68616d696c746f6e2e6a7067, '', 139, '2024-11-03 09:19:52', 0),
(159, 1111, 0, '', 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', 137, '2024-11-03 09:20:00', 0),
(160, 1111, 0, '', 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', 138, '2024-11-03 09:20:09', 0),
(161, 1111, 0, '', 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', 141, '2024-11-03 09:20:59', 0);

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_history`
--

CREATE TABLE `leaderboard_history` (
  `history_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `total_rating` int(11) NOT NULL,
  `month` varchar(7) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaderboard_history`
--

INSERT INTO `leaderboard_history` (`history_id`, `employee_id`, `full_name`, `total_rating`, `month`, `created_at`) VALUES
(75, 1111, 'Maverick Gutierez', 235, '2024-11', '2024-11-30 09:22:20'),
(76, 4444, 'Cyrille Bautista', 175, '2024-11', '2024-11-30 09:22:20');

-- --------------------------------------------------------

--
-- Table structure for table `progress_reports`
--

CREATE TABLE `progress_reports` (
  `report_id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `report_image` longblob NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_evaluated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress_reports`
--

INSERT INTO `progress_reports` (`report_id`, `full_name`, `employee_id`, `report_image`, `description`, `created_at`, `is_evaluated`) VALUES
(137, 'Maverick Gutierez', 1111, 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', '2024-11-03 09:18:13', 1),
(138, 'Maverick Gutierez', 1111, 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', '2024-11-03 09:18:17', 1),
(139, 'Cyrille Bautista', 4444, 0x75706c6f6164732f6c612d62656c6c652d68616d696c746f6e2e6a7067, '', '2024-11-03 09:19:11', 1),
(140, 'Cyrille Bautista', 4444, 0x75706c6f6164732f6c612d62656c6c652d68616d696c746f6e2e6a7067, '', '2024-11-03 09:19:15', 1),
(141, 'Maverick Gutierez', 1111, 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', '2024-11-03 09:20:44', 1),
(142, 'Maverick Gutierez', 1111, 0x75706c6f6164732f3336305f465f3237333232373437335f4e30575251755833755a434a4a786c484b595a46343475614a416b6832784c472e6a7067, '', '2024-11-04 12:23:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(20) NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `contact_no` varchar(11) NOT NULL,
  `email_address` varchar(50) NOT NULL,
  `employee_id` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `contact_no`, `email_address`, `employee_id`) VALUES
(35, 'Maverick', 'Gutierez', '12345678910', 'mav@example.com', 1111),
(36, 'Cyrille', 'Bautista', '12345678910', 'lanzcyrille.002@gmail.com', 4444);

-- --------------------------------------------------------

--
-- Table structure for table `staff_schedule`
--

CREATE TABLE `staff_schedule` (
  `schedule_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `days` varchar(255) NOT NULL,
  `shift_time` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_schedule`
--

INSERT INTO `staff_schedule` (`schedule_id`, `staff_id`, `days`, `shift_time`, `location`) VALUES
(81, 35, 'Saturday', 'Morning (8 AM - 12 PM)', 'location c'),
(84, 35, 'Thursday', 'Afternoon (1 PM - 5 PM)', 'location c'),
(85, 36, 'Saturday', 'Morning (8 AM - 12 PM)', 'location b');

-- --------------------------------------------------------

--
-- Table structure for table `supplies`
--

CREATE TABLE `supplies` (
  `supplies_id` int(11) NOT NULL,
  `supplies` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `classification` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `stocks` int(11) NOT NULL,
  `expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplies`
--

INSERT INTO `supplies` (`supplies_id`, `supplies`, `brand`, `classification`, `category`, `stocks`, `expiry_date`) VALUES
(1, 'Disinfectant Spray', 'Lysol', 'Cleaning', 'Hygiene', 30, '2025-07-01'),
(2, 'Trash Bags', 'Glad', 'Waste Management', 'Disposal', 20, '2026-12-31'),
(3, 'Hand Soap', 'Safeguard', 'Cleaning', 'Hygiene', 20, '2025-01-15'),
(4, 'Paper Towels', 'Bounty', 'Cleaning', 'Disposal', 30, '2025-10-20'),
(5, 'Mop Heads', 'Vileda', 'Cleaning', 'Floor Care', 30, '2027-03-22'),
(6, 'Bleach', 'Clorox', 'Cleaning', 'Hygiene', 10, '2025-09-05'),
(7, 'Rubber Gloves', '3M', 'Protection', 'Hygiene', 30, '2026-04-10'),
(8, 'Floor Cleaner', 'Mr. Clean', 'Cleaning', 'Floor Care', 90, '2025-08-12'),
(9, 'Hand Sanitizer', 'Purell', 'Cleaning', 'Hygiene', 10, '2026-02-28'),
(10, 'Dish Soap', 'Dawn', 'Cleaning', 'Kitchen', 6, '2025-12-31'),
(11, 'Glass Cleaner', 'Windex', 'Cleaning', 'Windows', 75, '2026-05-17'),
(12, 'All-Purpose Cleaner', 'Fantastik', 'Cleaning', 'Multi-Surface', 11, '2025-11-02'),
(13, 'Toilet Paper', 'Charmin', 'Cleaning', 'Bathroom Supplies', 50, '2026-09-30'),
(14, 'Hand Towels', 'Scott', 'Cleaning', 'Disposal', 40, '2025-06-25'),
(15, 'Surface Disinfectant Wipes', 'Clorox', 'Cleaning', 'Hygiene', 200, '2025-04-12'),
(16, 'Air Freshener', 'Febreze', 'Deodorizing', 'Air Care', 150, '2027-01-01'),
(17, 'Dishwashing Liquid', 'Palmolive', 'Cleaning', 'Kitchen', 100, '2025-08-05'),
(18, 'Sponges', 'Scotch-Brite', 'Cleaning', 'Kitchen Supplies', 180, '2026-10-15'),
(19, 'Bathroom Cleaner', 'Lysol', 'Cleaning', 'Bathroom Care', 90, '2025-02-22'),
(20, 'Furniture Polish', 'Pledge', 'Polishing', 'Furniture Care', 60, '2026-12-01'),
(21, 'Laundry Detergent', 'Tide', 'Cleaning', 'Laundry', 150, '2026-06-30'),
(22, 'Fabric Softener', 'Downy', 'Cleaning', 'Laundry', 120, '2026-03-15'),
(23, 'Window Squeegee', 'Unger', 'Cleaning', 'Windows', 40, '2027-07-10'),
(24, 'Broom', 'O-Cedar', 'Cleaning', 'Floor Care', 80, '2027-12-25'),
(25, 'Dustpan', 'Rubbermaid', 'Cleaning', 'Floor Care', 100, '2027-11-30'),
(37, 'Toilet Bowl Cleaner', 'Harpic', 'Cleaning', 'Bathroom Care', 90, '2025-10-10'),
(38, 'Room Deodorizer', 'Glade', 'Deodorizing', 'Air Care', 130, '2026-04-25'),
(39, 'Mop Bucket', 'Rubbermaid', 'Cleaning', 'Floor Care', 50, '2028-01-01'),
(40, 'Vacuum Bags', 'Hoover', 'Cleaning', 'Floor Care', 70, '2027-05-12'),
(41, 'Scrubbing Pads', 'Brillo', 'Cleaning', 'Kitchen Supplies', 150, '2026-09-14');

-- --------------------------------------------------------

--
-- Table structure for table `supplies_usage_history`
--

CREATE TABLE `supplies_usage_history` (
  `history_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `employee_id` varchar(20) NOT NULL,
  `supplies` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `supplies_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplies_usage_history`
--

INSERT INTO `supplies_usage_history` (`history_id`, `full_name`, `employee_id`, `supplies`, `quantity`, `transaction_date`, `supplies_id`) VALUES
(64, 'Maverick Gutierez', '1111', 'Disinfectant Spray', 10, '2024-10-23 13:29:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fcm_token` text NOT NULL,
  `notifications_subscribed` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fcm_token`, `notifications_subscribed`) VALUES
(18, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(19, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(20, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(21, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(22, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(23, 'dnjJFXf-r1ApPFMHX5RpM6:APA91bGqSDxMvty0lwGTvDkLxdPlCX2pcLqneO-KmO0J_KfWyMXxhFMOr_BVPAM7De0f66fa92sRmTLWFi6eRykGbXpfOXQGSn6wvxSLM0lmNPYdk6V6MGM', 1),
(24, 'fk4mHFj2bQdcw59rfzo4u6:APA91bGlC9b6Gt7jo5TpJm6tB5IYOa2n3nUsh_2pFtkDiOgnLtYP2YVhZ4KaInk_sJ_pz7igLV7AM5hQWJoCcjCfq5M2NqRNfDYElLCGUIZ0uOdTsR8Up4A', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `leaderboard_history`
--
ALTER TABLE `leaderboard_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `progress_reports`
--
ALTER TABLE `progress_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `supplies`
--
ALTER TABLE `supplies`
  ADD PRIMARY KEY (`supplies_id`);

--
-- Indexes for table `supplies_usage_history`
--
ALTER TABLE `supplies_usage_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `leaderboard_history`
--
ALTER TABLE `leaderboard_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `progress_reports`
--
ALTER TABLE `progress_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `supplies`
--
ALTER TABLE `supplies`
  MODIFY `supplies_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `supplies_usage_history`
--
ALTER TABLE `supplies_usage_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `progress_reports` (`report_id`);

--
-- Constraints for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD CONSTRAINT `staff_schedule_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplies_usage_history`
--
ALTER TABLE `supplies_usage_history`
  ADD CONSTRAINT `supplies_usage_history_ibfk_1` FOREIGN KEY (`supplies_id`) REFERENCES `supplies` (`supplies_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
