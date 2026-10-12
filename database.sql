-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 11:44 AM
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
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discovery_items`
--

CREATE TABLE `discovery_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_id` int(10) UNSIGNED NOT NULL,
  `item_hash` char(64) NOT NULL,
  `title` varchar(500) NOT NULL,
  `item_url` varchar(2048) NOT NULL,
  `summary` text DEFAULT NULL,
  `review_status` enum('pending','published','dismissed') NOT NULL DEFAULT 'pending',
  `scholarship_id` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `discovered_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discovery_sources`
--

CREATE TABLE `discovery_sources` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `feed_url` varchar(2048) NOT NULL,
  `host_allowlist` varchar(255) NOT NULL,
  `source_type` enum('rss','atom') NOT NULL DEFAULT 'rss',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `last_checked_at` datetime DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `country` varchar(120) NOT NULL DEFAULT 'International',
  `study_level` varchar(100) NOT NULL DEFAULT 'Various levels',
  `funding_type` enum('Fully funded','Tuition waiver','Partial funding','Other') NOT NULL DEFAULT 'Other',
  `deadline` date DEFAULT NULL,
  `official_url` varchar(2048) DEFAULT NULL,
  `summary` text NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `eligibility` mediumtext DEFAULT NULL,
  `documents` mediumtext DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `source_name` varchar(255) DEFAULT NULL,
  `source_item_url` varchar(2048) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `title`, `provider`, `country`, `study_level`, `funding_type`, `deadline`, `official_url`, `summary`, `description`, `eligibility`, `documents`, `status`, `source_name`, `source_item_url`, `created_at`, `updated_at`) VALUES
(1, 'Example scholarship — replace this record', 'Example provider', 'International', 'Master’s', 'Other', NULL, NULL, 'This is sample content for testing. Replace it with a verified opportunity before publishing.', 'This sample is a placeholder, not a real scholarship announcement. Confirm every detail from the official provider website.', NULL, NULL, 'draft', NULL, NULL, '2026-10-12 12:21:05', '2026-10-12 12:21:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `discovery_items`
--
ALTER TABLE `discovery_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_discovery_hash` (`item_hash`),
  ADD KEY `idx_review_status` (`review_status`,`discovered_at`),
  ADD KEY `fk_discovery_source` (`source_id`),
  ADD KEY `fk_discovery_scholarship` (`scholarship_id`),
  ADD KEY `fk_discovery_admin` (`reviewed_by`);

--
-- Indexes for table `discovery_sources`
--
ALTER TABLE `discovery_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_feed_url` (`feed_url`(500));

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_deadline` (`status`,`deadline`),
  ADD KEY `idx_country_level` (`country`,`study_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discovery_items`
--
ALTER TABLE `discovery_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discovery_sources`
--
ALTER TABLE `discovery_sources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discovery_items`
--
ALTER TABLE `discovery_items`
  ADD CONSTRAINT `fk_discovery_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_discovery_scholarship` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_discovery_source` FOREIGN KEY (`source_id`) REFERENCES `discovery_sources` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
