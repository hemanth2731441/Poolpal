-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2025 at 09:16 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `poolpal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$yrs0YUUwBuK3P2p0wGTkZO7cAf4yK20eDNr67uT.83gtqwf0G941e');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trip_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `driver_email` varchar(255) DEFAULT NULL,
  `seats_booked` int DEFAULT NULL,
  `total_amount` int NOT NULL,
  `special_requests` text,
  `booking_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `payment_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_id` (`trip_id`),
  KEY `user_id` (`user_id`),
  KEY `payment_id` (`payment_id`),
  KEY `razorpay_order_id` (`razorpay_order_id`),
  KEY `razorpay_payment_id` (`razorpay_payment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `trip_id`, `user_id`, `user_name`, `user_email`, `driver_email`, `seats_booked`, `total_amount`, `special_requests`, `booking_time`, `payment_status`, `payment_id`, `payment_method`, `payment_date`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`) VALUES
(1, 41, 53, 'Eslavath Kumar', 'eslavathkumar50@gmail.com', 'shubhyysingh@gmail.com', 2, 996, '', '2025-05-31 12:50:02', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 41, 53, 'Eslavath Kumar', 'eslavathkumar50@gmail.com', 'shubhyysingh@gmail.com', 1, 498, '', '2025-05-31 13:10:29', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 41, 53, 'Eslavath Kumar', 'eslavathkumar50@gmail.com', 'shubhyysingh@gmail.com', 1, 498, '', '2025-05-31 13:11:18', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 51, 61, 'Poolpal', 'poolpal.in@gmail.com', 'shubhyykrsingh@gmail.com', 7, 70007, '', '2025-06-02 23:02:17', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 42, 74, 'shubham', 'shubhyykrsingh@gmail.com', 'eslavathkumar50@gmail.com', 2, 1000, '', '2025-06-04 10:10:39', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(32, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-07 16:38:31', 'completed', NULL, 'upi', '2025-06-07 11:09:01', 'order_QeHg4jeKjLKIQY', 'pay_QeHgJnOJSUZl34', '46639e58bb992adf283c99929f513d62f7b14c2c2140808fde7cbd98afe8c136'),
(31, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-07 16:36:25', 'completed', NULL, 'upi', '2025-06-07 11:07:34', 'order_QeHdsqWRdPgYIH', 'pay_QeHen4pRltJ8OH', 'e08a09a24dca370b1e80d33a802d60f1d669576c385cdf1a2aeaf62609c71b4f'),
(15, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-06 14:43:45', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-06 14:45:35', 'pending', NULL, NULL, NULL, 'order_QdrDfNlDQY9ZMd', NULL, NULL),
(17, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 2222, '', '2025-06-06 14:46:44', 'pending', NULL, NULL, NULL, 'order_QdrEsrNWcBZ2E5', NULL, NULL),
(18, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 2222, '', '2025-06-06 15:07:56', 'pending', NULL, NULL, NULL, 'order_QdrbHrJZSDsJCk', NULL, NULL),
(19, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-06 15:16:02', 'pending', NULL, NULL, NULL, 'order_Qdrjp2auWVAgwU', NULL, NULL),
(20, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 3, 3333, '', '2025-06-06 15:23:54', 'pending', NULL, NULL, NULL, 'order_Qdrs8D2GAUEx2H', NULL, NULL),
(21, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-06 15:29:15', 'pending', NULL, NULL, NULL, 'order_Qdrxp5oKvz8AU3', NULL, NULL),
(22, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 8, 8888, '', '2025-06-06 15:33:47', 'pending', NULL, NULL, NULL, 'order_Qds2aBUIUV9Rb3', NULL, NULL),
(23, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1111, '', '2025-06-06 15:43:37', 'pending', NULL, NULL, NULL, 'order_QdsCxQpbJhFm9n', NULL, NULL),
(24, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 5, 5555, '', '2025-06-06 15:53:54', 'pending', NULL, NULL, NULL, 'order_QdsNqJuMfA9vxM', NULL, NULL),
(25, 52, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 11, 12221, '', '2025-06-06 16:09:19', 'pending', NULL, NULL, NULL, 'order_Qdse7MKt8fwwQg', NULL, NULL),
(36, 54, 77, 'Poolpal', 'poolpal.in@gmail.com', 'shubhyykrsingh@gmail.com', 1, 498, '', '2025-06-07 19:05:33', 'completed', NULL, 'upi', '2025-06-07 13:36:17', 'order_QeKBQ8wvR9BsFf', 'pay_QeKBsWlfYFgXHZ', '6c94016cab9f867f26327fbd44ebadb4bf71e5fd7fbbbc4fabba8a77cb17dd4a'),
(33, 53, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 1499, '', '2025-06-07 16:43:01', 'completed', NULL, 'upi', '2025-06-07 11:13:35', 'order_QeHkpt1KnH6CaW', 'pay_QeHl8mRoG3PzAJ', '638d027dbfd500d52ed34813c2d9d23f0583080deba98e5077749c01a9202fee'),
(43, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 12:31:46', 'completed', NULL, 'upi', '2025-06-09 07:02:18', 'order_Qf0XfuQ7N4XPi5', 'pay_Qf0XvZsOAlJuB0', 'e5f080344e3b60fbad705cc6d43fe3ffa1f148d5656a96a6a8d49cb37df0f679'),
(44, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 12:36:47', 'completed', NULL, 'upi', '2025-06-09 07:07:17', 'order_Qf0cy4jAVrbwCT', 'pay_Qf0dBWQIzm3tHO', '2d205cb7f1b4b13547f207560965be51d9561b9f1ae560299ffd7fb607d004e8'),
(45, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 12:38:19', 'completed', NULL, 'upi', '2025-06-09 07:08:52', 'order_Qf0ebOjzSpVQjk', 'pay_Qf0eqXXwd6PEnR', '9510c0d25cdd787b192818b99101da29e409fec4fc27dbba08491bf3313e3e3d'),
(46, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 16:01:53', 'completed', NULL, 'upi', '2025-06-09 10:32:27', 'order_Qf47dqAdn1NDJk', 'pay_Qf47vNNNwq8p7l', '7e49e670e5e34c7e3a64703bb61e0cd74935cd6f42fe8d04a04d705803b08251'),
(47, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 16:14:01', 'completed', NULL, 'upi', '2025-06-09 10:44:35', 'order_Qf4KTRgSo45jc9', 'pay_Qf4KixmXEy9eM8', 'e31c05d40a771d007d2df4074acbac3583eac6b7174e92b8d21d4ba242f24a08'),
(48, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 16:19:54', 'completed', NULL, 'upi', '2025-06-09 10:50:39', 'order_Qf4Qr1g2PO8Vdo', 'pay_Qf4R8aK2VHmt1A', 'e30d91d5c6afce814b9eb95c9c8b0a154f77911edb6b8a66ec9a37d97d5e6b5b'),
(49, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 16:25:25', 'completed', NULL, 'upi', '2025-06-09 10:55:54', 'order_Qf4WUrn1uE9a82', 'pay_Qf4Wh734gRNveS', '54efda82dcea8f4be8291b9541fc6eb07f5fd3a4755bad01e1de1bdd83963008'),
(50, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 2, 1000, '', '2025-06-09 16:53:04', 'completed', NULL, 'upi', '2025-06-09 11:23:34', 'order_Qf4zi88sYI5hhV', 'pay_Qf4zvEb0AVScAF', 'c1829fcbad8ca29ec31711eb4065739d2aa99ba0dcefaab6eb9338f941e4c402'),
(51, 55, 76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', 'shubhyykrsingh@gmail.com', 1, 500, '', '2025-06-10 11:06:18', 'pending', NULL, NULL, NULL, 'order_QfNcWl3mG15kZs', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_bookings`
--

DROP TABLE IF EXISTS `cancelled_bookings`;
CREATE TABLE IF NOT EXISTS `cancelled_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) NOT NULL,
  `trip_id` int NOT NULL,
  `seats_booked` int NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cancellation_reason` text,
  `cancelled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cancelled_bookings`
--

INSERT INTO `cancelled_bookings` (`id`, `user_email`, `trip_id`, `seats_booked`, `departure_city`, `destination_city`, `departure_date`, `departure_time`, `arrival_time`, `price`, `cancellation_reason`, `cancelled_at`) VALUES
(1, 'eslavathkumar50@gmail.com', 43, 1, 'Mysuru, Karnataka, India', 'Belagavi, Karnataka, India', '2025-06-04', '08:30:00', '19:00:00', 1000.00, NULL, '2025-05-31 13:14:58'),
(2, 'rajakotha11@gmail.com', 43, 1, 'Mysuru, Karnataka, India', 'Belagavi, Karnataka, India', '2025-06-04', '08:30:00', '19:00:00', 1000.00, NULL, '2025-05-31 13:26:56'),
(3, 'rajakotha11@gmail.com', 42, 1, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-06', '08:00:00', '20:30:00', 500.00, NULL, '2025-05-31 13:27:41'),
(4, 'shubhyykrsingh@gmail.com', 42, 1, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-06', '08:00:00', '20:30:00', 500.00, NULL, '2025-06-04 05:09:57'),
(5, 'shubhyykrsingh@gmail.com', 42, 1, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-06', '08:00:00', '20:30:00', 500.00, NULL, '2025-06-04 05:11:28'),
(6, 'singhshubhyy@gmail.com', 52, 1, 'Delhi, India', 'Chandigarh, India', '2025-06-15', '02:14:00', '02:14:00', 1111.00, 'Schedule conflict', '2025-06-07 11:02:13'),
(7, 'singhshubhyy@gmail.com', 52, 1, 'Delhi, India', 'Chandigarh, India', '2025-06-15', '02:14:00', '02:14:00', 1111.00, 'Schedule conflict', '2025-06-07 11:02:37'),
(8, 'singhshubhyy@gmail.com', 52, 2, 'Delhi, India', 'Chandigarh, India', '2025-06-15', '02:14:00', '02:14:00', 1111.00, 'Emergency situation', '2025-06-07 11:03:22'),
(9, 'singhshubhyy@gmail.com', 52, 2, 'Delhi, India', 'Chandigarh, India', '2025-06-15', '02:14:00', '02:14:00', 1111.00, 'asdfbdsfghjkl;;kjhgfd', '2025-06-07 11:03:52'),
(10, 'singhshubhyy@gmail.com', 54, 1, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Found alternative transport', '2025-06-10 12:44:49'),
(11, 'singhshubhyy@gmail.com', 54, 4, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Found alternative transport', '2025-06-10 12:56:34'),
(12, 'singhshubhyy@gmail.com', 54, 2, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Change in travel plans', '2025-06-10 12:59:26'),
(13, 'singhshubhyy@gmail.com', 54, 2, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Change in travel plans', '2025-06-10 13:02:48'),
(14, 'singhshubhyy@gmail.com', 54, 1, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Change in travel plans', '2025-06-10 13:06:34'),
(15, 'singhshubhyy@gmail.com', 54, 3, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Schedule conflict', '2025-06-10 13:08:53'),
(16, 'singhshubhyy@gmail.com', 54, 2, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Change in travel plans', '2025-06-10 13:13:11'),
(17, 'singhshubhyy@gmail.com', 54, 2, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '17:00:00', 498.00, 'Schedule conflict', '2025-06-10 13:13:53'),
(18, 'singhshubhyy@gmail.com', 56, 1, 'Guwahati, Assam, India', 'Bengaluru, Karnataka, India', '2025-06-13', '10:00:00', '12:00:00', 1.00, 'Emergency situation', '2025-06-10 13:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_trips`
--

DROP TABLE IF EXISTS `cancelled_trips`;
CREATE TABLE IF NOT EXISTS `cancelled_trips` (
  `id` int NOT NULL,
  `driver_email` varchar(100) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `seats` int DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`driver_email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cancelled_trips`
--

INSERT INTO `cancelled_trips` (`id`, `driver_email`, `departure_city`, `destination_city`, `departure_date`, `departure_time`, `arrival_time`, `price`, `seats`, `cancellation_reason`, `cancelled_at`) VALUES
(48, 'shubhyykrsingh@gmail.com', 'Njeezhoor, Kerala 686612, India', 'Gyanpur, Uttar Pradesh 221304, India', '2025-06-03', '10:00:00', '12:00:00', 5000.00, 10, NULL, '2025-06-02 15:24:02'),
(50, 'shubhyykrsingh@gmail.com', 'Delhi Cantt, Delhi, India', 'Chandigarh, India', '2025-06-07', '10:01:00', '10:01:00', 540.00, 15, 'Weather conditions', '2025-06-03 19:52:14'),
(51, 'shubhyykrsingh@gmail.com', 'Bhagalpur, Bihar, India', 'Chandigarh, India', '2025-06-07', '22:14:00', '10:14:00', 10001.00, 93, 'mera man mai nhi jayega', '2025-06-03 20:02:46');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE IF NOT EXISTS `drivers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Full_Name` char(50) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Contact` varchar(10) NOT NULL,
  `alt_phone` varchar(10) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Address` varchar(200) NOT NULL,
  `Driving_License` varchar(500) NOT NULL,
  `Profile_Pic` varchar(255) DEFAULT NULL,
  `Aadhar` varchar(255) NOT NULL,
  `vehicle_name` varchar(255) NOT NULL,
  `vehicle_color` varchar(50) NOT NULL,
  `vehicle_type` enum('Car-Pooling','Car-Taxi','Bike','Auto Rickshaw','Goods-7ft','Goods-8ft','Goods-3Wheeler','Goods-Tata407') NOT NULL,
  `Vehicle_Number` varchar(50) NOT NULL,
  `RC` varchar(255) NOT NULL,
  `Languages` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_created_at` datetime DEFAULT NULL,
  `member_since` date DEFAULT NULL,
  `verification_status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`ID`, `Full_Name`, `Gender`, `Contact`, `alt_phone`, `Email`, `Address`, `Driving_License`, `Profile_Pic`, `Aadhar`, `vehicle_name`, `vehicle_color`, `vehicle_type`, `Vehicle_Number`, `RC`, `Languages`, `Password`, `reset_otp`, `otp_created_at`, `member_since`, `verification_status`, `status`) VALUES
(79, 'YO YO HONEY SINGH', 'Male', '9504145235', '8960753758', 'shubhyykrsingh@gmail.com', '13-5-154, rama rao nagar, Sanathnagar', 'uploads/1749197343_license_6842a21f64cb0.jpg', 'uploads/1749197343_profile_6842a21f656f7.jpg', 'uploads/1749197343_aadhar_6842a21f65c60.jpg', 'SCORPIO S12', 'BLACK', 'Car-Taxi', 'BR10AC5448', 'uploads/1749197343_rc_6842a21f651c2.jpg', 'HINDI, ENGLISH', '$2y$10$uUB3fYUIvkP58KdBwYAXxuK9xMBCig3lb32pPyKxtOPDiCuwchAjq', NULL, NULL, '2025-06-06', 'accepted', 1);

-- --------------------------------------------------------

--
-- Table structure for table `driver_password_resets`
--

DROP TABLE IF EXISTS `driver_password_resets`;
CREATE TABLE IF NOT EXISTS `driver_password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `driver_password_resets`
--

INSERT INTO `driver_password_resets` (`id`, `email`, `otp`, `token`, `expires_at`, `created_at`) VALUES
(36, 'shubhyysingh@gmail.com', '747497', '06c2eeec4c3758f8204776e4e34c048a0e5a8c22cc949b6385b494960ca0d792', '2025-05-27 07:10:38', '2025-05-27 00:40:38'),
(27, 'shubhyykrsingh@gmail.com', '163114', 'c3dd41bcbe4390e308ec6a7df92a4d9f8b1d32cceadf36450ce6977f7f91e075', '2025-05-27 04:42:27', '2025-05-26 22:12:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

DROP TABLE IF EXISTS `notification_settings`;
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `trip_updates` tinyint(1) DEFAULT '0',
  `promotions` tinyint(1) DEFAULT '0',
  `account_activity` tinyint(1) DEFAULT '0',
  `ride_reminders` tinyint(1) DEFAULT '0',
  `driver_messages` tinyint(1) DEFAULT '0',
  `system_announcements` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`(250)),
  KEY `token` (`token`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp`, `token`, `verified`, `created_at`, `expires_at`) VALUES
(58, 'shubhyykrsingh@gmail.com', '508874', '4c85141c270d20aa31427e7ce133ee1186af07b729598888d2a9bee44e106820', 1, '2025-06-09 11:21:24', '2025-06-09 17:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_details` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `payment_id` (`payment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_id`, `order_id`, `amount`, `currency`, `status`, `payment_method`, `payment_details`, `created_at`, `updated_at`) VALUES
(1, 16, '', 'order_QdrDfNlDQY9ZMd', 1111.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:15:36', '2025-06-06 09:15:36'),
(2, 17, '', 'order_QdrEsrNWcBZ2E5', 2222.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:16:45', '2025-06-06 09:16:45'),
(3, 18, '', 'order_QdrbHrJZSDsJCk', 2222.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:37:57', '2025-06-06 09:37:57'),
(4, 19, '', 'order_Qdrjp2auWVAgwU', 1111.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:46:02', '2025-06-06 09:46:02'),
(5, 20, '', 'order_Qdrs8D2GAUEx2H', 3333.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:53:54', '2025-06-06 09:53:54'),
(6, 21, '', 'order_Qdrxp5oKvz8AU3', 1111.00, 'INR', 'pending', NULL, NULL, '2025-06-06 09:59:17', '2025-06-06 09:59:17'),
(7, 22, '', 'order_Qds2aBUIUV9Rb3', 8888.00, 'INR', 'pending', NULL, NULL, '2025-06-06 10:03:48', '2025-06-06 10:03:48'),
(8, 23, '', 'order_QdsCxQpbJhFm9n', 1111.00, 'INR', 'pending', NULL, NULL, '2025-06-06 10:13:37', '2025-06-06 10:13:37'),
(9, 24, '', 'order_QdsNqJuMfA9vxM', 5555.00, 'INR', 'pending', NULL, NULL, '2025-06-06 10:23:55', '2025-06-06 10:23:55'),
(10, 25, '', 'order_Qdse7MKt8fwwQg', 12221.00, 'INR', 'pending', NULL, NULL, '2025-06-06 10:39:20', '2025-06-06 10:39:20'),
(11, 26, 'pay_QeHGQzjQJfILFc', 'order_QeHFoKXs72scHj', 1111.00, 'INR', 'success', 'upi', NULL, '2025-06-07 10:43:39', '2025-06-07 10:46:13'),
(12, 27, 'pay_QeHLcuif1E0oNV', 'order_QeHIrD5q4rw7uG', 1111.00, 'INR', 'success', 'card', NULL, '2025-06-07 10:46:33', '2025-06-07 10:49:43'),
(13, 28, 'pay_QeHQLPy6T5EFi0', 'order_QeHQ4W4vYYwRL1', 1111.00, 'INR', 'success', 'upi', NULL, '2025-06-07 10:53:22', '2025-06-07 10:53:54'),
(14, 29, 'pay_QeHT8xRGjGbodq', 'order_QeHSsSkWSxp7oS', 1111.00, 'INR', 'success', 'upi', NULL, '2025-06-07 10:56:02', '2025-06-07 10:56:33'),
(15, 30, '', 'order_QeHWo6K6GN9TNw', 1111.00, 'INR', 'pending', NULL, NULL, '2025-06-07 10:59:45', '2025-06-07 10:59:45'),
(16, 31, 'pay_QeHen4pRltJ8OH', 'order_QeHdsqWRdPgYIH', 1111.00, 'INR', 'success', 'upi', NULL, '2025-06-07 11:06:27', '2025-06-07 11:07:34'),
(17, 32, 'pay_QeHgJnOJSUZl34', 'order_QeHg4jeKjLKIQY', 1111.00, 'INR', 'success', 'upi', NULL, '2025-06-07 11:08:31', '2025-06-07 11:09:01'),
(18, 33, 'pay_QeHl8mRoG3PzAJ', 'order_QeHkpt1KnH6CaW', 1499.00, 'INR', 'success', 'upi', NULL, '2025-06-07 11:13:02', '2025-06-07 11:13:35'),
(19, 34, 'pay_QeJVB1KziyV7la', 'order_QeJUwMPxGKmFch', 498.00, 'INR', 'success', 'upi', NULL, '2025-06-07 12:55:22', '2025-06-07 12:55:52'),
(20, 35, 'pay_QeJWpr2tzmudVS', 'order_QeJWTHEfzjvGEs', 498.00, 'INR', 'success', 'upi', NULL, '2025-06-07 12:56:49', '2025-06-07 12:57:26'),
(21, 36, 'pay_QeKBsWlfYFgXHZ', 'order_QeKBQ8wvR9BsFf', 498.00, 'INR', 'success', 'upi', NULL, '2025-06-07 13:35:35', '2025-06-07 13:36:17'),
(22, 37, 'pay_Qf05ucGsw1IeJe', 'order_Qf05XIBfKhs3bE', 1992.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:35:09', '2025-06-09 06:35:47'),
(23, 38, 'pay_Qf07XEeg1eJ8EK', 'order_Qf07AyRye7kwCW', 1494.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:36:42', '2025-06-09 06:37:20'),
(24, 39, 'pay_Qf09O7jySBFcWT', 'order_Qf095XAFVP9PTn', 996.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:38:30', '2025-06-09 06:39:04'),
(25, 40, 'pay_Qf0MSmP0XtiyD5', 'order_Qf0MDJcXCVKBUx', 996.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:50:56', '2025-06-09 06:51:27'),
(26, 41, 'pay_Qf0QaAQI07VCE3', 'order_Qf0QJfRl13p6wL', 996.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:54:49', '2025-06-09 06:55:21'),
(27, 42, 'pay_Qf0UWYEUEsmGnY', 'order_Qf0UHqLjgxOfBJ', 996.00, 'INR', 'success', 'upi', NULL, '2025-06-09 06:58:34', '2025-06-09 06:59:05'),
(28, 43, 'pay_Qf0XvZsOAlJuB0', 'order_Qf0XfuQ7N4XPi5', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 07:01:47', '2025-06-09 07:02:18'),
(29, 44, 'pay_Qf0dBWQIzm3tHO', 'order_Qf0cy4jAVrbwCT', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 07:06:48', '2025-06-09 07:07:17'),
(30, 45, 'pay_Qf0eqXXwd6PEnR', 'order_Qf0ebOjzSpVQjk', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 07:08:20', '2025-06-09 07:08:52'),
(31, 46, 'pay_Qf47vNNNwq8p7l', 'order_Qf47dqAdn1NDJk', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 10:31:55', '2025-06-09 10:32:27'),
(32, 47, 'pay_Qf4KixmXEy9eM8', 'order_Qf4KTRgSo45jc9', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 10:44:04', '2025-06-09 10:44:35'),
(33, 48, 'pay_Qf4R8aK2VHmt1A', 'order_Qf4Qr1g2PO8Vdo', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 10:50:06', '2025-06-09 10:50:39'),
(34, 49, 'pay_Qf4Wh734gRNveS', 'order_Qf4WUrn1uE9a82', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 10:55:26', '2025-06-09 10:55:54'),
(35, 50, 'pay_Qf4zvEb0AVScAF', 'order_Qf4zi88sYI5hhV', 1000.00, 'INR', 'success', 'upi', NULL, '2025-06-09 11:23:06', '2025-06-09 11:23:34'),
(36, 51, '', 'order_QfNcWl3mG15kZs', 500.00, 'INR', 'pending', NULL, NULL, '2025-06-10 05:36:20', '2025-06-10 05:36:20'),
(37, 52, 'pay_QfNfPW7f8iUxAk', 'order_QfNeuhFReGRLtU', 1.00, 'INR', 'success', 'upi', NULL, '2025-06-10 05:38:35', '2025-06-10 05:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires`, `created_at`) VALUES
(10, 39, 'bd0d3a0ce077b768994bb58a75ca9ca09cccd50754aad838cd5fac0272e94393', '2025-06-18 19:12:52', '2025-05-19 19:12:52');

-- --------------------------------------------------------

--
-- Table structure for table `ride_requests`
--

DROP TABLE IF EXISTS `ride_requests`;
CREATE TABLE IF NOT EXISTS `ride_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `ride_date` date NOT NULL,
  `ride_time` time NOT NULL,
  `seats_needed` int NOT NULL,
  `additional_notes` text,
  `status` enum('pending','accepted','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ride_requests`
--

INSERT INTO `ride_requests` (`id`, `user_id`, `pickup_location`, `destination`, `ride_date`, `ride_time`, `seats_needed`, `additional_notes`, `status`, `created_at`) VALUES
(17, 39, 'chandigarh ', 'bangalore', '2025-05-10', '06:30:00', 5, '', 'pending', '2025-05-05 21:41:21'),
(16, 39, 'London', 'Bhagalpur', '2025-07-15', '10:00:00', 10, 'daru, ganja cigg sb allow h ', 'pending', '2025-05-05 20:42:54'),
(18, 76, 'Wakad Bridge, Patil Nagar, Balewadi, Pune, Maharashtra, India', 'Mumbai - Delhi Express Highway, Gujarat, India', '2025-06-09', '10:00:00', 5, 'Hello', 'pending', '2025-06-09 11:24:48'),
(19, 78, 'VH Road, Town Hall, Coimbatore, Tamil Nadu, India', 'Bhartiya City, Kannuru, Bengaluru, Karnataka, India', '2025-06-09', '10:00:00', 5, 'Hello', 'pending', '2025-06-09 11:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `ride_searches`
--

DROP TABLE IF EXISTS `ride_searches`;
CREATE TABLE IF NOT EXISTS `ride_searches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_location` varchar(255) DEFAULT NULL,
  `from_lat` double DEFAULT NULL,
  `from_lng` double DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `to_lat` double DEFAULT NULL,
  `to_lng` double DEFAULT NULL,
  `travel_date` date DEFAULT NULL,
  `searched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ride_searches`
--

INSERT INTO `ride_searches` (`id`, `from_location`, `from_lat`, `from_lng`, `to_location`, `to_lat`, `to_lng`, `travel_date`, `searched_at`) VALUES
(1, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Andheri, Maharashtra 400053, India', 19.113645, 72.8697339, '2025-05-24', '2025-05-24 08:33:50'),
(2, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Andhra Nagar, Telangana 503213, India', 18.8184321, 78.1400524, '2025-05-24', '2025-05-24 08:34:08'),
(3, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-26', '2025-05-24 09:42:43'),
(4, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-26', '2025-05-24 09:45:24'),
(5, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-26', '2025-05-24 10:07:42'),
(6, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-26', '2025-05-24 10:14:52'),
(7, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-25', '2025-05-24 10:22:37'),
(8, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-25', '2025-05-24 10:24:33'),
(9, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-25', '2025-05-24 10:27:54'),
(10, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-24', '2025-05-24 10:44:12'),
(11, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-05-25', '2025-05-24 11:34:06'),
(12, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-06-01', '2025-05-31 05:11:42'),
(13, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, '2025-06-01', '2025-05-31 12:41:02'),
(14, 'Bengaluru, Karnataka, India', 12.9628669, 77.57750899999999, 'Hyderabad, Telangana, India', 17.406498, 78.47724389999999, '2025-06-01', '2025-06-01 05:53:35'),
(15, 'Bhagalpur, Bihar, India', 25.242453, 86.9842256, 'Chandigarh, India', 30.7333148, 76.7794179, '2025-06-07', '2025-06-02 09:26:16'),
(16, '3rd Cross Rd, Lakshmi Layout, Gandhi Nagar, Munnekollal, Bengaluru, Karnataka, India', 12.9543082, 77.71026069999999, 'BTM Layout, Bengaluru, Karnataka, India', 12.9165757, 77.6101163, '2025-06-14', '2025-06-09 09:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `media_file` varchar(500) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `file_extension` varchar(10) NOT NULL,
  `file_size` int DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `description`, `media_file`, `media_type`, `file_extension`, `file_size`, `alt_text`, `link_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 'sdfghjkl;', 'dgfhjkm,.,l/.,mnbhvgcf', 'uploads/sliders/slider_1749901295_684d5fef723d8.jpg', 'image', 'jpg', 273852, 'Poolpal Banner', 'https://poolpal.in/contactus.php', 0, 1, '2025-06-14 11:41:35', '2025-06-14 14:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

DROP TABLE IF EXISTS `trips`;
CREATE TABLE IF NOT EXISTS `trips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `departure_city` varchar(100) DEFAULT NULL,
  `destination_city` varchar(100) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `arrival_date` date NOT NULL,
  `arrival_time` time NOT NULL,
  `seats` int DEFAULT NULL,
  `price` double DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `allow_smoking` tinyint(1) DEFAULT NULL,
  `pets_allowed` tinyint(1) DEFAULT NULL,
  `luggage_space` varchar(20) DEFAULT NULL,
  `notes` text,
  `driver_name` varchar(100) DEFAULT NULL,
  `driver_email` varchar(100) DEFAULT NULL,
  `driver_phone` varchar(20) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `distance` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `has_ac` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `departure_city`, `destination_city`, `departure_date`, `departure_time`, `arrival_date`, `arrival_time`, `seats`, `price`, `vehicle_type`, `allow_smoking`, `pets_allowed`, `luggage_space`, `notes`, `driver_name`, `driver_email`, `driver_phone`, `vehicle_number`, `duration`, `distance`, `created_at`, `has_ac`) VALUES
(41, 'Bengaluru, Karnataka, India', 'Hyderabad, Telangana, India', '2025-05-26', '10:00:00', '2025-05-26', '23:00:00', 1, 498, 'carpooling', 1, 0, 'Medium', 'Hello', 'Shubham Kumar Singh', 'shubhyysingh@gmail.com', '9504145236', 'KA 01AC 554', '13h 0m', '500 km', '2025-05-24 09:41:30', 1),
(42, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-06', '08:00:00', '2025-06-06', '20:30:00', 0, 500, 'carpooling', 1, 0, 'Medium', '', 'Eslavath Kumar', 'eslavathkumar50@gmail.com', '8309169447', 'KA01AC3118', '12h 30m', '500', '2025-05-31 12:56:42', 1),
(43, 'Mysuru, Karnataka, India', 'Belagavi, Karnataka, India', '2025-06-04', '08:30:00', '2025-06-04', '19:00:00', 3, 1000, 'carpooling', 1, 0, 'Medium', '', 'Eslavath Kumar', 'eslavathkumar50@gmail.com', '8309169447', 'KA01AC3118', '10h 30m', '500', '2025-05-31 12:58:00', 1),
(44, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-03', '05:00:00', '2025-06-03', '22:00:00', 4, 900, 'carpooling', 0, 0, 'Medium', '', 'Eslavath Kumar', 'eslavathkumar50@gmail.com', '8309169447', 'KA01AC3118', '17h 0m', '500', '2025-05-31 13:05:30', 1),
(45, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-05', '10:00:00', '2025-06-05', '17:00:00', 5, 498, 'carpooling', 0, 0, 'Medium', '', 'Eslavath Kumar', 'eslavathkumar50@gmail.com', '8309169447', 'KA01AC3118', '7h 0m', '500', '2025-05-31 13:06:41', 1),
(46, 'Hyderabad, Telangana, India', 'Bengaluru, Karnataka, India', '2025-06-02', '10:00:00', '2025-06-06', '12:00:00', 5, 97, '8ft_vehicle', 1, 0, 'Medium', '', 'Shubham Kumar Singh', 'shubhyykrsingh@gmail.com', '9504145235', 'KA01AC3118', '98h 0m', '500', '2025-06-01 20:18:22', 0),
(47, 'Bulandshahr, Uttar Pradesh 203001, India', 'Naugachhia, Bihar 853204, India', '2025-06-02', '10:00:00', '2025-06-03', '23:00:00', 5, 998, '', 1, 0, 'Small', '', 'Shubham Kumar Singh', 'shubhyykrsingh@gmail.com', '9504145235', 'KA01AC3118', '37h 0m', '500', '2025-06-01 20:27:56', 0),
(49, 'Delhi, India', 'Chandigarh, India', '2025-06-01', '14:14:00', '2025-06-14', '00:14:00', 5, 4998, 'Goods-8ft', 1, 0, 'Small', '', 'Shubham Kumar Singh', 'shubhyykrsingh@gmail.com', '9504145235', 'KA01AC3118', '154h 0m', '5000', '2025-06-01 20:45:09', 0),
(55, 'Mysuru, Karnataka, India', 'Deoghar, Jharkhand, India', '2025-06-14', '10:00:00', '2025-06-15', '05:00:00', 50, 500, 'Car-Taxi', 1, 0, 'Medium', '', 'YO YO HONEY SINGH', 'shubhyykrsingh@gmail.com', '9504145235', 'BR10AC5448', '19h 0m', '500 km', '2025-06-09 07:01:07', 0),
(56, 'Guwahati, Assam, India', 'Bengaluru, Karnataka, India', '2025-06-13', '10:00:00', '2025-06-13', '12:00:00', 6, 1, 'Car-Taxi', 1, 0, 'Medium', '', 'YO YO HONEY SINGH', 'shubhyykrsingh@gmail.com', '9504145235', 'BR10AC5448', '2h 0m', '500 km', '2025-06-10 05:38:04', 0),
(54, 'Guwahati, Assam, India', 'Lucknow, Uttar Pradesh, India', '2025-06-13', '05:00:00', '2025-06-13', '17:00:00', 51, 498, 'Car-Taxi', 1, 0, 'Small', '', 'YO YO HONEY SINGH', 'shubhyykrsingh@gmail.com', '9504145235', 'BR10AC5448', '12h 0m', '500 km', '2025-06-07 12:54:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_photo` varchar(255) DEFAULT NULL,
  `social_provider` varchar(20) DEFAULT NULL,
  `social_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`, `profile_photo`, `social_provider`, `social_id`) VALUES
(77, 'Poolpal', 'poolpal.in@gmail.com', '8051770440', NULL, '2025-06-07 13:35:10', 'uploads/profile_1749303310_6844400e23297.jpg', 'google', '107525105041971408172'),
(78, 'Shreys', 'shreyajaiswal1805@gmail.com', '8960753758', '$2y$10$7dDydc8y20VYT6YYMI1SgO.0XEFnJXGebjQNFAfN0FMnAvtSdykNy', '2025-06-09 11:16:16', 'uploads/profile_1749467776_6846c2804d60a.png', NULL, NULL),
(76, 'Shubhyy Singh', 'singhshubhyy@gmail.com', '9504145235', '$2y$10$0i6hrM7REff9tRc7kHC8EOgKzIltAx49SqtdUWoVFTacqpBHCsckW', '2025-06-10 12:36:53', 'uploads/profile_1749559013_684826e5de207.jpg', 'google', '105944421337807493981');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
