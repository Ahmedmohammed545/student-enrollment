-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 10:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student`
--

-- --------------------------------------------------------

--
-- Table structure for table `grade_progression`
--

CREATE TABLE `grade_progression` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `from_class` varchar(10) DEFAULT NULL,
  `to_class` varchar(10) DEFAULT NULL,
  `from_section` varchar(10) DEFAULT NULL,
  `to_section` varchar(10) DEFAULT NULL,
  `from_academic_year` varchar(10) NOT NULL,
  `to_academic_year` varchar(10) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_registrations`
--

CREATE TABLE `pending_registrations` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `student_id` varchar(8) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL,
  `grade_level` varchar(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `pcontact` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL DEFAULT '2000-01-01',
  `age` int(11) NOT NULL DEFAULT 0,
  `academic_year` varchar(9) NOT NULL DEFAULT '2025-2026',
  `graduation_year` year(4) NOT NULL DEFAULT 2030,
  `photo` varchar(255) NOT NULL,
  `transcript` varchar(255) NOT NULL DEFAULT '',
  `birth_certificate` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_registrations`
--

INSERT INTO `pending_registrations` (`id`, `parent_id`, `student_id`, `name`, `grade_level`, `address`, `pcontact`, `date_of_birth`, `age`, `academic_year`, `graduation_year`, `photo`, `transcript`, `birth_certificate`, `status`, `datetime`) VALUES
(1, NULL, '', 'Ahmed Mohammedseid', '11th', 'Ethiopia', '+251931756278', '2000-01-01', 0, '2025-2026', '2030', '1746874891_4192.jpg', '', NULL, 'declined', '2025-05-10 13:01:31'),
(20, 2, 'LIA1983', 'hayat mohammed', '4th', 'Ethiopia', '+251931756278', '2015-11-30', 9, '2025', '2034', '1746929433', '1746929433_3153_transcript.pdf', '1746929433_1997_birth_certificate.pdf', 'declined', '2025-05-11 04:10:33'),
(27, 10, 'LIA6892', 'new student', '3rd', 'Ethiopia', '+251931756278', '2006-09-09', 18, '2026', '2036', '1748066424', '1748066424_1940_transcript.pdf', '1748066424_2385_birth_certificate.pdf', 'declined', '2025-05-24 08:00:24'),
(29, 10, 'LIA1783', 'new student1', '8th', 'Ethiopia', '+251931756278', '2006-09-09', 18, '2025', '2030', '1748066875', '1748066875_9711_transcript.pdf', '1748066875_5301_birth_certificate.pdf', 'declined', '2025-05-24 08:07:55');

-- --------------------------------------------------------

--
-- Table structure for table `student_info`
--

CREATE TABLE `student_info` (
  `id` int(11) NOT NULL,
  `student_id` varchar(8) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL,
  `class` varchar(10) NOT NULL,
  `section` varchar(5) NOT NULL,
  `address` varchar(20) NOT NULL,
  `pcontact` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL DEFAULT '2000-01-01',
  `age` int(11) NOT NULL DEFAULT 0,
  `academic_year` varchar(9) NOT NULL DEFAULT '2025-2026',
  `graduation_year` year(4) NOT NULL DEFAULT 2030,
  `photo` varchar(255) NOT NULL,
  `transcript` varchar(255) NOT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_info`
--

INSERT INTO `student_info` (`id`, `student_id`, `name`, `class`, `section`, `address`, `pcontact`, `date_of_birth`, `age`, `academic_year`, `graduation_year`, `photo`, `transcript`, `birth_certificate`, `datetime`) VALUES
(29, 'LIA9986', 'Brook Tadesse', '1st', 'C', 'Alaqm', '+251931756278', '2006-09-02', 18, '2028', '2026', '1747041000', '1747041000', '1747041000_6924_birth_certificate.pdf', '2025-05-12 11:11:10'),
(30, 'LIA2479', 'Kidus Asnake', '3rd', 'C', 'Lebu', '+251912345678', '2008-05-21', 16, '2025', '2026', '1747046967', '1747046967', '1747046967_5853_birth_certificate.pdf', '2025-05-12 12:53:29'),
(31, 'LIA4654', 'abd abas', '1st', 'C', 'Addis Ababa', '+251912345687', '2000-09-05', 24, '2025', '2026', '1747048004', '1747048004', '1747048004_7070_birth_certificate.pdf', '2025-05-12 13:07:40'),
(32, 'LIA9333', 'Kidus Asnake', '3rd', 'C', 'Dicha', '+251931756278', '2005-09-09', 19, '2025', '2032', '1747650613', '1747650613', '1747650613_7240_birth_certificate.pdf', '2025-05-19 12:31:03'),
(33, 'LIA5399', 'Brook Tadesse', '5th', 'C', 'Ethiopia', '+251931756278', '2003-09-09', 21, '2025', '2033', '1748058611', '1748058611', '1748058611_9482_birth_certificate.pdf', '2025-05-24 05:57:59'),
(34, 'LIA3175', 'New student', '1st', 'A', 'Dire dawa', '+251931756278', '2005-09-01', 19, '2027', '2029', '1748048241', '1748048241', '1748048241_2864_birth_certificate.pdf', '2025-05-24 06:01:26'),
(35, 'LIA4417', 'new student', '1st', 'C', 'Ethiopia', '+251931756278', '2006-09-09', 18, '2027', '2030', '1748066601', '1748066601', '1748066601_8147_birth_certificate.pdf', '2025-05-24 08:03:53'),
(36, 'LIA5209', 'Abdullah Abas', '12th', 'D', 'Addis Ababa', '+251931756278', '2006-09-09', 18, '2026', '2027', '1748072170', '1748072170', '1748072170_5085_birth_certificate.pdf', '2025-05-24 09:37:57'),
(37, '', 'hayat mohammed', '10th', 'B', 'Atana wingate', '+251913456789', '2000-01-01', 25, '2025-2026', '2028', 'student_2025-05-24-10-24-06.png', 'transcript_2025-05-24-10-24-06.pdf', 'birth_certificate_2025-05-24-10-24-06.pdf', '2025-05-24 09:41:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `datetime` datetime DEFAULT current_timestamp(),
  `role` enum('admin','client') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `email`, `photo`, `status`, `datetime`, `role`) VALUES
(1, 'Ahmed Mohammedseid', 'Ahmed@Mohammedseid', 'b102ce1d5eebac2b6d74bda8c87c47a050c80491', 'ahmedmohammedaddisababa@gmail.com', 'Ahmed@Mohammedseid-20250524103055.jpg', 'active', '2025-05-10 10:57:01', 'admin'),
(2, 'Ahmed Mohammedseid', 'Ahmed', '$2y$10$Jfr.rVKQf5tGbRdj8rA36..8Nntt2/AT7fJqM3mCQiKzrYPGLvdUe', '', '', 'active', '2025-05-11 03:57:18', 'client'),
(6, 'Brook Tadesse', 'BURBURE', 'b102ce1d5eebac2b6d74bda8c87c47a050c80491', 'br@gmail.com', '', 'active', '2025-05-12 13:12:42', 'client'),
(7, 'parent', 'parent', 'b102ce1d5eebac2b6d74bda8c87c47a050c80491', '123@gmail.com', '', 'active', '2025-05-24 06:37:33', 'client'),
(10, 'hayat', 'hayu', 'b102ce1d5eebac2b6d74bda8c87c47a050c80491', 'ahm@gmail.com', '', 'active', '2025-05-24 07:17:47', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grade_progression`
--
ALTER TABLE `grade_progression`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `pending_registrations`
--
ALTER TABLE `pending_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `student_info`
--
ALTER TABLE `student_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_student_id` (`student_id`);

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
-- AUTO_INCREMENT for table `grade_progression`
--
ALTER TABLE `grade_progression`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `pending_registrations`
--
ALTER TABLE `pending_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `student_info`
--
ALTER TABLE `student_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grade_progression`
--
ALTER TABLE `grade_progression`
  ADD CONSTRAINT `grade_progression_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `pending_registrations` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
