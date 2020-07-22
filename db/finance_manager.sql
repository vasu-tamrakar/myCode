-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 30, 2020 at 04:42 AM
-- Server version: 5.6.12-log
-- PHP Version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finance_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_category`
--

DROP TABLE IF EXISTS `tbl_fm_category`;
CREATE TABLE IF NOT EXISTS `tbl_fm_category` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_category`
--

INSERT INTO `tbl_fm_category` (`id`, `parent_id`, `category_name`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(1, 0, 'Travel', 'active', 0, '2020-03-27 11:24:59', '2020-03-27 11:24:59'),
(2, 0, 'Shopping', 'active', 0, '2020-03-27 11:25:20', '2020-03-27 11:25:20'),
(3, 0, 'Travel', 'active', 0, '2020-03-27 11:25:41', '2020-03-27 11:25:41'),
(4, 0, 'Travel', 'active', 0, '2020-03-27 11:26:02', '2020-03-27 11:26:02'),
(5, 0, 'Travel', 'active', 0, '2020-03-27 11:26:24', '2020-03-27 11:26:24'),
(6, 0, 'Entertainment', 'active', 0, '2020-03-27 11:26:44', '2020-03-27 11:26:44'),
(7, 0, 'Travel', 'active', 0, '2020-03-27 11:27:15', '2020-03-27 11:27:15'),
(8, 0, 'Travel', 'active', 0, '2020-03-27 11:27:58', '2020-03-27 11:27:58'),
(9, 0, 'Travel', 'active', 0, '2020-03-27 11:28:32', '2020-03-27 11:28:32'),
(10, 0, 'Auto & Transport', 'active', 0, '2020-03-27 11:41:29', '2020-03-27 11:41:29'),
(11, 0, 'Shopping', 'active', 0, '2020-03-27 11:41:41', '2020-03-27 11:41:41'),
(12, 0, 'Travel', 'active', 0, '2020-03-27 11:41:58', '2020-03-27 11:41:58'),
(13, 0, 'Travel', 'active', 0, '2020-03-27 11:42:16', '2020-03-27 11:42:16'),
(14, 0, 'Travel', 'active', 0, '2020-03-27 11:42:36', '2020-03-27 11:42:36'),
(15, 0, 'Entertainment', 'active', 0, '2020-03-27 11:42:53', '2020-03-27 11:42:53'),
(16, 0, 'Travel', 'active', 0, '2020-03-27 11:43:16', '2020-03-27 11:43:16'),
(17, 0, 'Travel', 'active', 0, '2020-03-27 11:43:54', '2020-03-27 11:43:54'),
(18, 0, 'Travel', 'active', 0, '2020-03-27 11:44:22', '2020-03-27 11:44:22'),
(19, 0, 'Travel', 'active', 0, '2020-03-27 11:44:56', '2020-03-27 11:44:56'),
(20, 0, 'Travel', 'active', 0, '2020-03-27 11:45:40', '2020-03-27 11:45:40'),
(21, 0, 'Shopping', 'active', 0, '2020-03-27 11:46:58', '2020-03-27 11:46:58'),
(22, 0, 'Travel', 'active', 0, '2020-03-27 11:47:56', '2020-03-27 11:47:56'),
(23, 0, 'Auto & Transport', 'active', 0, '2020-03-27 11:49:07', '2020-03-27 11:49:07'),
(24, 0, 'Travel', 'active', 0, '2020-03-27 11:50:34', '2020-03-27 11:50:34');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_category_mapping`
--

DROP TABLE IF EXISTS `tbl_fm_category_mapping`;
CREATE TABLE IF NOT EXISTS `tbl_fm_category_mapping` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_category_mapping_fk1` (`category_id`),
  KEY `tbl_category_mapping_fk2` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_email`
--

DROP TABLE IF EXISTS `tbl_fm_email`;
CREATE TABLE IF NOT EXISTS `tbl_fm_email` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `primary_email` tinyint(4) NOT NULL COMMENT '1- Primary, 2- Secondary',
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_user_email_fk1` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_email`
--

INSERT INTO `tbl_fm_email` (`id`, `user_id`, `email`, `primary_email`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(1, 1, 'amandeep.singh@cssindiaonline.com', 1, 'active', 0, '2020-03-03 09:45:07', '2020-03-03 09:45:07');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_invoice`
--

DROP TABLE IF EXISTS `tbl_fm_invoice`;
CREATE TABLE IF NOT EXISTS `tbl_fm_invoice` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `invoice_date` date NOT NULL,
  `pay_by` date NOT NULL,
  `invoice_shift_start_date` datetime NOT NULL,
  `invoice_shift_end_date` datetime NOT NULL,
  `invoice_shift_notes` text NOT NULL,
  `line_item_notes` text NOT NULL,
  `manual_invoice_notes` text NOT NULL,
  `booked_by` int(10) UNSIGNED NOT NULL,
  `invoice_for` int(10) UNSIGNED NOT NULL,
  `total` double NOT NULL,
  `gst` double NOT NULL,
  `sub_total` double NOT NULL,
  `invoice_type` int(11) NOT NULL,
  `invoice_finalised_date` datetime NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_invoice_fk1` (`user_id`),
  KEY `tbl_finance_invoice_fk2` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_invoice_line_item`
--

DROP TABLE IF EXISTS `tbl_fm_invoice_line_item`;
CREATE TABLE IF NOT EXISTS `tbl_fm_invoice_line_item` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `funding_type` int(10) UNSIGNED NOT NULL,
  `support_registration_group` int(10) UNSIGNED NOT NULL,
  `support_category` int(10) UNSIGNED NOT NULL,
  `support_outcome_domain` int(10) UNSIGNED NOT NULL,
  `line_item_number` varchar(100) NOT NULL,
  `line_item_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text NOT NULL,
  `quote_required` int(10) UNSIGNED NOT NULL,
  `price_control` int(10) UNSIGNED NOT NULL,
  `travel_required` int(10) UNSIGNED NOT NULL,
  `cancellation_fees` int(10) UNSIGNED NOT NULL,
  `ndis_reporting` int(10) UNSIGNED NOT NULL,
  `non_f2f` int(10) UNSIGNED NOT NULL,
  `upper_price_limit` double NOT NULL,
  `national_price_limit` double NOT NULL,
  `national_very_price_limit` double NOT NULL,
  `levelId` int(10) UNSIGNED NOT NULL,
  `pay_pointId` int(10) UNSIGNED NOT NULL,
  `schedule_constraint` int(10) UNSIGNED NOT NULL,
  `public_holiday` int(10) UNSIGNED NOT NULL,
  `member_ratio` int(10) UNSIGNED NOT NULL,
  `participant_ratio` int(10) UNSIGNED NOT NULL,
  `measure_by` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_invoice_line_item_fk1` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_invoice_manual_invoice_line_item`
--

DROP TABLE IF EXISTS `tbl_fm_invoice_manual_invoice_line_item`;
CREATE TABLE IF NOT EXISTS `tbl_fm_invoice_manual_invoice_line_item` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `funding_type` int(10) UNSIGNED NOT NULL,
  `measure_by` int(10) UNSIGNED NOT NULL,
  `line_item` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `cost` double UNSIGNED NOT NULL,
  `sub_total` double UNSIGNED NOT NULL,
  `gst` double UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL,
  `plan_line_itemId` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_invoice_manual_invoice_line_item_fk1` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_logs`
--

DROP TABLE IF EXISTS `tbl_fm_logs`;
CREATE TABLE IF NOT EXISTS `tbl_fm_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `ctivity` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_logs_fk1` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_statement`
--

DROP TABLE IF EXISTS `tbl_fm_statement`;
CREATE TABLE IF NOT EXISTS `tbl_fm_statement` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `statement_number` varchar(50) NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime NOT NULL,
  `issue_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `statement_notes` varchar(255) NOT NULL,
  `statement_file_path` varchar(255) NOT NULL,
  `statement_type` smallint(5) UNSIGNED NOT NULL,
  `statement_for` int(10) UNSIGNED NOT NULL,
  `booked_by` smallint(5) UNSIGNED NOT NULL,
  `booker_mail` smallint(5) UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_statement_fk1` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_statement`
--

INSERT INTO `tbl_fm_statement` (`id`, `user_id`, `statement_number`, `from_date`, `to_date`, `issue_date`, `due_date`, `statement_notes`, `statement_file_path`, `statement_type`, `statement_for`, `booked_by`, `booker_mail`, `total`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(1, 1, '45567', '2020-03-02 12:18:57', '2020-03-13 12:18:57', '2020-03-18 12:18:57', '2020-03-26 12:18:57', 'jhkjlkkl', '', 0, 0, 0, 0, 0, 'active', 0, '2020-03-23 06:50:16', '2020-03-23 06:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_statement_attachment`
--

DROP TABLE IF EXISTS `tbl_fm_statement_attachment`;
CREATE TABLE IF NOT EXISTS `tbl_fm_statement_attachment` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `statement_id` int(10) UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL,
  `gst` double UNSIGNED NOT NULL,
  `sub_total` double UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_statement_attachment_fk1` (`statement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_statement_line_item`
--

DROP TABLE IF EXISTS `tbl_fm_statement_line_item`;
CREATE TABLE IF NOT EXISTS `tbl_fm_statement_line_item` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `statement_id` int(10) UNSIGNED NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(150) NOT NULL,
  `vendor_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` float NOT NULL,
  `transaction_type` tinyint(4) NOT NULL COMMENT '1 - Credit/ 2 - Debit',
  `cheque_number` varchar(50) NOT NULL,
  `main_balance` double NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_finance_statement_line_item_fk1` (`statement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=447 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_statement_line_item`
--

INSERT INTO `tbl_fm_statement_line_item` (`id`, `statement_id`, `transaction_date`, `description`, `vendor_id`, `category_id`, `amount`, `transaction_type`, `cheque_number`, `main_balance`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(1, 0, '0000-00-00', 'AUTOPAY THANK YOU', 1, 10, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:41:29'),
(2, 0, '0000-00-00', 'description', 2, 11, 0, 0, 'cheque_number', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:41:42'),
(3, 0, '0000-00-00', 'SMARTPAY CASHBACK OFFE 2', 3, 12, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:41:58'),
(4, 0, '0000-00-00', 'SMARTPAY 15996568 TTEL H TXHC58650', 4, 13, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:42:16'),
(5, 0, '0000-00-00', 'SMART PAY PROC FEE 1', 5, 14, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:42:36'),
(6, 0, '0000-00-00', 'CINEMAX INDIA L BANGALORE 5', 6, 15, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:42:53'),
(7, 0, '0000-00-00', 'SMART PAY PROC FEE 1', 7, 16, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:43:18'),
(8, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT L NEW DELHI 2', 8, 17, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:43:54'),
(9, 0, '0000-00-00', 'SMARTPAY 15996568 TTEL H TXHC59312 1', 9, 18, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:44:22'),
(10, 0, '0000-00-00', 'SMART PAY PROC FEE', 10, 19, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:44:56'),
(11, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631 2', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 0, '0000-00-00', 'G2S - Fring +442030510330 6', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 0, '0000-00-00', 'AUTOPAY THANK YOU 6', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 0, '0000-00-00', 'PAYPAL *SKYPE 35314369001 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 0, '0000-00-00', 'PAYPAL *SKYPE 35314369001 9', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 0, '0000-00-00', 'AUTOPAY THANK YOU 2', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 0, '0000-00-00', 'SMARTPAY 93859474 DOCO OM TXHC53630', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 0, '0000-00-00', 'SMART PAY PROC FEE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 0, '0000-00-00', 'HOSTELBOOKERS.COM 44(0)207 4061', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 0, '0000-00-00', 'HOSTELBOOKERS.COM 44(0)207 4061', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 0, '0000-00-00', 'HOSTELBOOKERS.COM 44(0)207 4061', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 0, '0000-00-00', 'HOSTELBOOKERS.COM 44(0)207 4061', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(26, 0, '0000-00-00', 'Schweizer Jugendherber Z rich', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(27, 0, '0000-00-00', 'EASYJET AEJJB932000000 BEDFORDSHIRE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(28, 0, '0000-00-00', 'BACKPACKERS VILLA SONN INTERLAKEN', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(29, 0, '0000-00-00', 'EASYJET A0000000EJDB23 LUTON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(30, 0, '0000-00-00', 'AGODA HOTEL RESERVATIO BUDAPEST', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(31, 0, '0000-00-00', 'PAYTM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(32, 0, '0000-00-00', 'PAYTM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(33, 0, '0000-00-00', 'PAYTM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(34, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(35, 0, '0000-00-00', 'PAYTM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(36, 0, '0000-00-00', 'AUTOPAY THANK YOU 6', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(37, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(38, 0, '0000-00-00', 'SMART PAY PROC FEE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(39, 0, '0000-00-00', 'GOOGLE *paid storage google.com/ch 2', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(40, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(41, 0, '0000-00-00', 'APPLE ONLINE STORE SYDNEY', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(42, 0, '0000-00-00', 'OPTUS PRE PAID MELBOURNE ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(43, 0, '0000-00-00', 'G2S - Fring +442030510330', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(44, 0, '0000-00-00', 'PAYPAL *SKYPE 35314369001', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(45, 0, '0000-00-00', 'PAYPAL *SKYPE 35314369001', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(46, 0, '0000-00-00', 'AMARNATHJI YATRA MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(47, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(48, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(49, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(50, 0, '0000-00-00', 'RELIANCE LEISURES LTD BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(51, 0, '0000-00-00', 'CINEPOLIS INDIA BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(52, 0, '0000-00-00', 'RELIANCE LEISURES LTD BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(53, 0, '0000-00-00', 'VODAFONE-BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(54, 0, '0000-00-00', 'P-ZONE BANGALORE ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(55, 0, '0000-00-00', 'LEISURE ENTERTAINMENT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(56, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT L NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(57, 0, '0000-00-00', 'BAKASUR BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(58, 0, '0000-00-00', 'VODAFONE-BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(59, 0, '0000-00-00', 'FAME CINEMAS MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(60, 0, '0000-00-00', 'MAC FAST FOODS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(61, 0, '0000-00-00', 'VODAFONE-BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(62, 0, '0000-00-00', 'TATA SKY COM MUMBAI 4', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(63, 0, '0000-00-00', 'AUTOPAY THANK YOU 2', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(64, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(65, 0, '0000-00-00', 'G2S - Fring +442030510330 ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(66, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(67, 0, '0000-00-00', 'INDIAN RAILWAY CATERIN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(68, 0, '0000-00-00', 'MPPKVVCL BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(69, 0, '0000-00-00', 'One97 Communications L NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(70, 0, '0000-00-00', 'AMAZONS SVCS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(71, 0, '0000-00-00', 'GOOGLE *Google Storage GOOGLE.COM/C', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(72, 0, '0000-00-00', 'BSNL PAYMENTS MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(73, 0, '0000-00-00', 'MPPKVVCL BILLDESK MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(74, 0, '0000-00-00', 'PVR LIMITED GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(75, 0, '0000-00-00', 'BSNL PAYMENTS MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(76, 0, '0000-00-00', 'Tata_Sky_DTH_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(77, 0, '0000-00-00', 'TATA INDICOM-DOCOMO MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(78, 0, '0000-00-00', 'SMARTPAY 15996568 TTELBH TXHC9539', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(79, 0, '0000-00-00', 'LINKEDIN-252*5528243 LINKEDIN.COM', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(80, 0, '0000-00-00', 'AIRTICKETS LTD AGIA PARASKE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(81, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(82, 0, '0000-00-00', 'STERLING HOLIDAY RESORTMUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(83, 0, '0000-00-00', 'PAYPAL *ENVATO MKPL EN 4029357733', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(84, 0, '0000-00-00', 'LINKEDIN-262*0459553 LINKEDIN.COM', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(85, 0, '0000-00-00', 'RCL_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(86, 0, '0000-00-00', 'FUTURE LIFESTYLE FASHI INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(87, 0, '0000-00-00', 'FREECHARGE.IN-PAYU GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(88, 0, '0000-00-00', 'CINEMAX INDIA L BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(89, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(90, 0, '0000-00-00', 'G2S - Fring +442030510330', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(91, 0, '0000-00-00', 'TATA INDICOM-DOCOMO MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(92, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(93, 0, '0000-00-00', 'RCL_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(94, 0, '0000-00-00', 'SMARTPAY 15996568 TTELBH TXHC9195', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(95, 0, '0000-00-00', 'GOOGLE *Google Storage GOOGLE.COM/C', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(96, 0, '0000-00-00', 'MPPKVVCL BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(97, 0, '0000-00-00', 'One97 Communications L NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(98, 0, '0000-00-00', 'AMAZONS SVCS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(99, 0, '0000-00-00', 'SMART PAY PROC FEE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(100, 0, '0000-00-00', 'PVR LIMITED GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(101, 0, '0000-00-00', 'RCL_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(102, 0, '0000-00-00', 'Tata_Sky_DTH_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(103, 0, '0000-00-00', 'IDEA PAYMENT MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(104, 0, '0000-00-00', 'Dominos_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(105, 0, '0000-00-00', 'BSNL PAYMENTS MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(106, 0, '0000-00-00', 'RCL_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(107, 0, '0000-00-00', 'AIRTICKETS LTD AGIA PARASKE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(108, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(109, 0, '0000-00-00', 'STERLING HOLIDAY RESORTMUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(110, 0, '0000-00-00', 'PAYPAL *ENVATO MKPL EN 4029357733', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(111, 0, '0000-00-00', 'FUTURE LIFESTYLE FASHI INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(112, 0, '0000-00-00', 'RCL_BD MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(113, 0, '0000-00-00', 'VODAFONE-BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(114, 0, '0000-00-00', 'APL*APPLEONLINESTOREUS 800-676-2775', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(115, 0, '0000-00-00', 'CITRUS PAY PVR CINEMAS MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(116, 0, '0000-00-00', 'US STUDENT&EV I901 FEE 800-375-5283', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(117, 0, '0000-00-00', 'GOOGLE *Google Play GOOGLE.COM/CH', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(118, 0, '0000-00-00', 'WWW.1AND1.COM TEL8774612631', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(119, 0, '0000-00-00', 'COACHUSA/MEGABUS 201-225-7580', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(120, 0, '0000-00-00', 'Super_PayU MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(121, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(122, 0, '0000-00-00', 'PAYPAL *SKYPE 35314369001', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(123, 0, '0000-00-00', 'APPLE ITUNES STORE-INR ITUNES.COM', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(124, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(125, 0, '0000-00-00', 'G2S - Fring +44203051033', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(126, 0, '0000-00-00', 'AUTOPAY THANK YOU', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(127, 0, '0000-00-00', 'WWW.1AND1.COM TEL877461263', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(128, 0, '0000-00-00', 'ETS*GRE Test Services 609-771-7670', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(129, 0, '0000-00-00', 'ETS*CTF TOEFL iBT Test 609-771-7100', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(130, 0, '0000-00-00', 'AGODA HOTEL RESERVATIO BUDAPEST', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(131, 0, '0000-00-00', 'PAYTM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(132, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(133, 0, '0000-00-00', 'ADITYA BIRLA HEALTH INSMUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(134, 0, '0000-00-00', 'AMAZON SELLER SERVICES MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(135, 0, '0000-00-00', 'WWW CIGNATTKINSURANCE IGURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(136, 0, '0000-00-00', 'POORVIKA MOBILES PRIVATBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(137, 0, '0000-00-00', 'AMAZON STANDING INSTRUCMUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(138, 0, '0000-00-00', 'TATA SKY LTD www.paynimo.', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(139, 0, '0000-00-00', 'AMAZON SELLER SERVICES MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(140, 0, '0000-00-00', 'PEPPERFRY COM MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(141, 0, '0000-00-00', 'LANDMARK ONLINE INDIA PMUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(142, 0, '0000-00-00', 'HDFC STANDARD LIFE CONVMUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(143, 0, '0000-00-00', 'Netflix (PGSI) https://www.', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(144, 0, '0000-00-00', 'ccavenue com retail aluva', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(145, 0, '0000-00-00', 'ccavenue com retail aluva', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(146, 0, '0000-00-00', 'MOCHI THE SHOE SHOPPE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(147, 0, '0000-00-00', 'LENSKART COCO GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(148, 0, '0000-00-00', 'ITSY BITSY PVT LTD, B BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(149, 0, '0000-00-00', 'RELIANCE LIFESTYLE HOLDBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(150, 0, '0000-00-00', 'MOHOLOHOLA FARMS PVT L BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(151, 0, '0000-00-00', 'BON APPETIT FOOD AND B BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(152, 0, '0000-00-00', 'zara india-9218 BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(153, 0, '0000-00-00', 'CHAI POINT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(154, 0, '0000-00-00', 'DEHATI MURGEE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(155, 0, '0000-00-00', 'CONVERSE- FLFL BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(156, 0, '0000-00-00', 'GREEN NATURES FRESH PR BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(157, 0, '0000-00-00', 'ESSENTIALS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(158, 0, '0000-00-00', 'VANITY FAYRE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(159, 0, '0000-00-00', 'CAFE COFFEE DAY INORBIT MALL ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(160, 0, '0000-00-00', 'SUBWAY - BREAD BASKET BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(161, 0, '0000-00-00', 'SUBWAY - BREAD BASKET BANGALORE ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(162, 0, '0000-00-00', 'APOLLO HOSPITALS ENTERPBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(163, 0, '0000-00-00', 'BIBA APPARELS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(164, 0, '0000-00-00', 'R.K. GARMENTS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(165, 0, '0000-00-00', 'SRI NANDI KHADI GRAMOD BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(166, 0, '0000-00-00', 'TRIBAL CO. OP. MARKETI XXX', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(167, 0, '0000-00-00', 'GUJARAT STATE HANDLOOM BENGALURU', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(168, 0, '0000-00-00', 'MRIGNAYANI BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(169, 0, '0000-00-00', 'GREEN NATURES FRESH PR BANGALORE 5', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(170, 0, '0000-00-00', 'SOCH BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(171, 0, '0000-00-00', 'PVR LIMITED BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(172, 0, '0000-00-00', 'ESSENTIALS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(173, 0, '0000-00-00', 'DEPARTMENT OF HOME AFF SOUTHPORT', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(174, 0, '0000-00-00', 'GITHUB.COM 4154486673', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(175, 0, '0000-00-00', 'MOIN FOOD COURT NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(176, 0, '0000-00-00', 'MOIN FOOD COURT NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(177, 0, '0000-00-00', 'PAYTM APP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(178, 0, '0000-00-00', 'DOMINOS PIZZA NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(179, 0, '0000-00-00', 'ARVIND LIFESTYLE BRANDSNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(180, 0, '0000-00-00', 'RAJKAMAL PRAKASHAN PVT NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(181, 0, '0000-00-00', 'CAFE DELHI HEIGHT NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(182, 0, '0000-00-00', 'RELIANCE LIFESTYLE HOLDNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(183, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(184, 0, '0000-00-00', 'BAKER STREET EXP NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(185, 0, '0000-00-00', 'Flipkart Internet Priv Bangalore', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(186, 0, '0000-00-00', 'MAD BANANAS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(187, 0, '0000-00-00', 'WINNER SPORTS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(188, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(189, 0, '0000-00-00', 'V K ENTERPRISES NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(190, 0, '0000-00-00', 'MAD BANANAS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(191, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(192, 0, '0000-00-00', 'PVR LTD NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(193, 0, '0000-00-00', 'OM SWEETS PVT LTD FARIDABAD', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(194, 0, '0000-00-00', 'PAYTM APP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(195, 0, '0000-00-00', 'JUBILANT FOODWORKS LIM 310', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(196, 0, '0000-00-00', 'GITHUB.COM 4KLR9 4154486673', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(197, 0, '0000-00-00', 'WWW AIRTEL IN GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(198, 0, '0000-00-00', 'THE MOBILE ACCESS THE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(199, 0, '0000-00-00', 'AMAZON SELLER SERVICES MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(200, 0, '0000-00-00', 'THE MOBILE ACCESS THE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(201, 0, '0000-00-00', 'PAYPAL *SHENZHENDAK 4029357733', 11, 20, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:45:40'),
(202, 0, '0000-00-00', 'RELIANCE DIGITAL BANGALORE', 12, 21, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:46:58'),
(203, 0, '0000-00-00', 'SUVEEDHA INDORE', 13, 22, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:47:56'),
(204, 0, '0000-00-00', 'PAYTM www.paytm.in', 14, 23, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:49:07'),
(205, 0, '0000-00-00', 'Netflix (PGSI) https://www.', 15, 24, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 11:50:35'),
(206, 0, '0000-00-00', 'PAYPAL *MAKEMYTRIPI 2261451400', 16, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:24:36'),
(207, 0, '0000-00-00', 'UBER INR www.uber.com', 17, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:24:37'),
(208, 0, '0000-00-00', 'BUDDY RETAIL PRIVATE L NEW DELHI', 18, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:24:47'),
(209, 0, '0000-00-00', 'PUNJAB GRILL INDORE', 19, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:25:00'),
(210, 0, '0000-00-00', 'UBER INR www.uber.com', 20, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:25:00'),
(211, 0, '0000-00-00', 'ccavenue com retail aluva', 21, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:25:15'),
(212, 0, '0000-00-00', 'BESCOM BILLDESK MUMBA', 22, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:25:32'),
(213, 0, '0000-00-00', 'FUTURE RETAIL LIMITED BANGALORE ', 23, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:25:52'),
(214, 0, '0000-00-00', 'CALIFORNIA BURRITO BANGALORE', 24, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '2020-03-27 10:26:11'),
(215, 0, '0000-00-00', 'SHIV SAGAR BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(216, 0, '0000-00-00', 'DALVKOT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(217, 0, '0000-00-00', 'MORE BANGALORE 4', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(218, 0, '0000-00-00', 'PRESTIGE SHANTINIKETAN BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(219, 0, '0000-00-00', 'Simply Fresh BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(220, 0, '0000-00-00', 'NARAYANA HRUDAYALAYA LIBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(221, 0, '0000-00-00', 'GANESH MEDICALS AND GENBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(222, 0, '0000-00-00', 'SOCH BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(223, 0, '0000-00-00', 'KABOOM (T2KB) SINGAPORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(224, 0, '0000-00-00', 'GITHUB.COM 4154486673', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(225, 0, '0000-00-00', 'F1RST Tax & Duty Free Melbourne Ai', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(226, 0, '0000-00-00', 'GANESH MEDICALS AND GENBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(227, 0, '0000-00-00', 'Simply Fresh BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(228, 0, '0000-00-00', 'PRESTIGE ESTATES PROJE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(229, 0, '0000-00-00', 'CINEPOLIS INDIA PVT LT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(230, 0, '0000-00-00', 'DALVKOT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(231, 0, '0000-00-00', 'ESSENTIALS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(232, 0, '0000-00-00', 'MSW*MOUNTAIN TRAIL FOO angalore', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(233, 0, '0000-00-00', 'SPAR SHANTINIKETAN BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(234, 0, '0000-00-00', 'DALVKOT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(235, 0, '0000-00-00', 'MAX HYPERMARKET INDIA PBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(236, 0, '0000-00-00', 'PSAOWA BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(237, 0, '0000-00-00', 'MAX HYPERMARKET INDIA PBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(238, 0, '0000-00-00', 'Simply Fresh BANGALOR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(239, 0, '0000-00-00', 'KANHA WORKERS SAHKARI MANDLA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(240, 0, '0000-00-00', 'BURITO BOYS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(241, 0, '0000-00-00', 'THE FOOD STREET AND VAARAIPUR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(242, 0, '0000-00-00', 'BAG ZONE RAIPUR ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(243, 0, '0000-00-00', 'EDGE VENTURES BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(244, 0, '0000-00-00', 'BAG ZONE RAIPUR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(245, 0, '0000-00-00', 'KANHA WORKERS SAHKARI MANDLA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(246, 0, '0000-00-00', 'BLR EBD TOI EXPRESS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(247, 0, '0000-00-00', 'GANESH MEDICALS AND GA BENGALURU', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(248, 0, '0000-00-00', 'MC DONALDS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(249, 0, '0000-00-00', 'FOOD BOX BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(250, 0, '0000-00-00', 'Simply Fresh BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(251, 0, '0000-00-00', 'DOMINOS 2 BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(252, 0, '0000-00-00', 'CINEPOLIS INDIA PVT LT BANGALORE 6', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(253, 0, '0000-00-00', 'MAC FAST FOODS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(254, 0, '0000-00-00', 'TRINITY SERVICE STATIO BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(255, 0, '0000-00-00', 'PETRO SURCHARGE WAIVER', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(256, 0, '0000-00-00', 'HATHWAY CABLE & DA INR MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(257, 0, '0000-00-00', 'ZOMATO COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(258, 0, '0000-00-00', 'WWW SONYLIV COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(259, 0, '0000-00-00', 'WWW HOTSTAR COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(260, 0, '0000-00-00', 'BESCOM BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(261, 0, '0000-00-00', 'Netflix (PGSI) MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(262, 0, '0000-00-00', 'INTERGLOBE AVIATION LT .', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(263, 0, '0000-00-00', 'WWW AIRTEL IN GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(264, 0, '0000-00-00', 'WWW BIGBASKET COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(265, 0, '0000-00-00', 'ccavenue com retail aluva', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(266, 0, '0000-00-00', 'DEFENCE EXHIBITION ORG NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(267, 0, '0000-00-00', 'TATA SKY LTD www.paynimo.', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(268, 0, '0000-00-00', 'DEFENCE EXHIBITION ORG NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(269, 0, '0000-00-00', 'MPONLINE LIMITED BHOPAL', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(270, 0, '0000-00-00', 'BOOK MY SHOW MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(271, 0, '0000-00-00', 'PETRO SURCHARGE WAIVER', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(272, 0, '0000-00-00', 'BALRAJ FILL AND FLY SIMROL', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(273, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(274, 0, '0000-00-00', 'GURUKRIPA FUELS INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(275, 0, '0000-00-00', 'BOOK MY SHOW MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(276, 0, '0000-00-00', 'PMI - CERTIFICATION 610-3564600', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(277, 0, '0000-00-00', 'GITHUB.COM 4KLR9 4154486673', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(278, 0, '0000-00-00', 'BALRAJ FILL AND FLY SIMROL', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(279, 0, '0000-00-00', 'Oravel Stays Priva INR www.oyorooms', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(280, 0, '0000-00-00', 'Primrose Service Apart Bangalore HQ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(281, 0, '0000-00-00', 'FREECHARGE MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(282, 0, '0000-00-00', 'FREECHARGE MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(283, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(284, 0, '0000-00-00', 'SHAHEED SURENDERA PAL FNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(285, 0, '0000-00-00', 'MAA DURGA STORE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(286, 0, '0000-00-00', 'FUNCITY NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(287, 0, '0000-00-00', 'PAYTM APP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(288, 0, '0000-00-00', 'VATS MEDICOS Delhi NCR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(289, 0, '0000-00-00', 'DENTAL CARE CENTRE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(290, 0, '0000-00-00', 'SHAHEED SURENDERA PAL FNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(291, 0, '0000-00-00', 'Hathway OBRN Mumbai', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(292, 0, '0000-00-00', 'FREECHARGE BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(293, 0, '0000-00-00', 'PKVVCL BILLDESK MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(294, 0, '0000-00-00', 'SUPERTECH GROUP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(295, 0, '0000-00-00', 'MOIN FOOD COURT NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(296, 0, '0000-00-00', 'MOIN FOOD COURT NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(297, 0, '0000-00-00', 'EasyEMI Sony Cashback Sep', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(298, 0, '0000-00-00', 'Primrose Service Apart Bangalore HQ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(299, 0, '0000-00-00', 'GURUKRIPA FUELS INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(300, 0, '0000-00-00', 'SHAHEED SURENDERA PAL FNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(301, 0, '0000-00-00', 'SANDWEDGES NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(302, 0, '0000-00-00', 'FUNCITY NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(303, 0, '0000-00-00', 'FREECHARGE 36', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(304, 0, '0000-00-00', 'MAD BANANAS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(305, 0, '0000-00-00', 'MAA DURGA STORE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(306, 0, '0000-00-00', 'One97 Communications L NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(307, 0, '0000-00-00', 'WWW.1AND1.COM 06105601589 U', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(308, 0, '0000-00-00', 'PRIVATEINTERNETACCESS 8663896788', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(309, 0, '0000-00-00', 'RISTORANTE PREGO NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(310, 0, '0000-00-00', 'VATS MEDICOS Delhi NCR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(311, 0, '0000-00-00', 'RISTORANTE PREGO NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(312, 0, '0000-00-00', 'PUNJABI BY NATURE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(313, 0, '0000-00-00', 'BSNL BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(314, 0, '0000-00-00', 'BHARTI INFOTEL LTD NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(315, 0, '0000-00-00', 'RELIANCE LIFESTYLE HOLDNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(316, 0, '0000-00-00', 'V K ENTERPRISES NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(317, 0, '0000-00-00', 'BHARAT PETROLEUM CORPORNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(318, 0, '0000-00-00', 'www.airtel.in www.airtel.i', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(319, 0, '0000-00-00', 'SUPERTECH ESTATE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(320, 0, '0000-00-00', 'RELIANCE JIO INFOCOMM LMUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(321, 0, '0000-00-00', 'SUPERTECH ESTATE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(322, 0, '0000-00-00', 'VIJAY SALES GHAZIABAD', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(323, 0, '0000-00-00', 'ONE ASSIST MFEE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(324, 0, '0000-00-00', 'PRIVATEINTERNETACCESS 8663896788', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(325, 0, '0000-00-00', 'WWW BIGBASKET COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(326, 0, '0000-00-00', 'VIA BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(327, 0, '0000-00-00', 'VIA BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(328, 0, '0000-00-00', 'APL*APPLEONLINESTOREUS 800-676-2775', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(329, 0, '0000-00-00', 'REDBUS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(330, 0, '0000-00-00', 'Setasign - Jan Slabon Helmstedt E', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(331, 0, '0000-00-00', 'SUPERTECH GROUP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(332, 0, '0000-00-00', 'WWW.1AND1.COM 06105601589', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(333, 0, '0000-00-00', 'SAHID SURENDAR PAL FIL NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(334, 0, '0000-00-00', 'EASE MY TRIP NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(335, 0, '0000-00-00', 'PETRO SURCHARGE WAIVER', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(336, 0, '0000-00-00', 'WWW NAUKRI COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(337, 0, '0000-00-00', 'MAD BANANAS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(338, 0, '0000-00-00', 'ARVIND LIFESTYLE BRANDSNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(339, 0, '0000-00-00', 'BAMBIOLA NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(340, 0, '0000-00-00', 'RODEO CP NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(341, 0, '0000-00-00', 'MCDONALD S NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(342, 0, '0000-00-00', 'HYPERCITY RETAIL INDIA NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(343, 0, '0000-00-00', 'CAKECITY THE PATISSERI HARYANA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(344, 0, '0000-00-00', 'KEVENTERS (THE ORIGINA HARYANA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(345, 0, '0000-00-00', 'PETRO SURCHARGE WAIVER', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(346, 0, '0000-00-00', 'ONE97 COMMUNICATIONS LTNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(347, 0, '0000-00-00', 'SUPERTECH GROUP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(348, 0, '0000-00-00', 'DOMINOS BILLDESK MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(349, 0, '0000-00-00', 'BOOK MY SHOW MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(350, 0, '0000-00-00', 'VAANGO NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(351, 0, '0000-00-00', 'TJORI COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(352, 0, '0000-00-00', 'COOKS CORNER NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(353, 0, '0000-00-00', 'PVR LIMITED NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(354, 0, '0000-00-00', 'BASKIN ROBBINS NEW DELH', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(355, 0, '0000-00-00', 'THEOS NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(356, 0, '0000-00-00', 'THE ARTS AND CRAFTS STONOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(357, 0, '0000-00-00', 'MAKEMYTRIP INDIA PVT LTNEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(358, 0, '0000-00-00', 'MAYAS SWEETS AND RESTAUNOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(359, 0, '0000-00-00', 'AGODA HOTEL RESERVATIO BUDAPEST', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(360, 0, '0000-00-00', 'POORVIKA MOBILES PRIVATBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(361, 0, '0000-00-00', 'AMAZON STANDING INSTRUCMUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(362, 0, '0000-00-00', 'Netflix (PGSI) https://www.', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(363, 0, '0000-00-00', 'BESCOM BILLDESK MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(364, 0, '0000-00-00', 'ccavenue com retail aluva', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(365, 0, '0000-00-00', 'MOCHI THE SHOE SHOPPE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(366, 0, '0000-00-00', 'LENSKART COCO GURGAON 6', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(367, 0, '0000-00-00', 'ITSY BITSY PVT LTD, B BANGALORE 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(368, 0, '0000-00-00', 'RELIANCE LIFESTYLE HOLDBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(369, 0, '0000-00-00', 'zara india-9218 BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(370, 0, '0000-00-00', 'VANITY FAYRE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(371, 0, '0000-00-00', 'SUBWAY - BREAD BASKET BANGALORE 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(372, 0, '0000-00-00', 'APOLLO HOSPITALS ENTERPBANGALORE 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(373, 0, '0000-00-00', 'TRIBAL CO. OP. MARKETI XXX', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(374, 0, '0000-00-00', 'MRIGNAYANI BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(375, 0, '0000-00-00', 'PVR LIMITED BANGALORE 7', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(376, 0, '0000-00-00', 'ESSENTIALS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(377, 0, '0000-00-00', 'DEPARTMENT OF HOME AFF SOUTHPORT', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(378, 0, '0000-00-00', 'THE MOBILE ACCESS THE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(379, 0, '0000-00-00', 'SUVEEDHA INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(380, 0, '0000-00-00', 'BUDDY RETAIL PRIVATE L NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(381, 0, '0000-00-00', 'PUNJAB GRILL INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(382, 0, '0000-00-00', 'BESCOM BILLDESK MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(383, 0, '0000-00-00', 'FUTURE RETAIL LIMITED BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(384, 0, '0000-00-00', 'CALIFORNIA BURRITO BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(385, 0, '0000-00-00', 'SHIV SAGAR BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(386, 0, '0000-00-00', 'COPPER CHIMNEY BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(387, 0, '0000-00-00', 'Simply Fresh BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(388, 0, '0000-00-00', 'GANESH MEDICALS AND GENBANGALORE 3', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(389, 0, '0000-00-00', 'F1RST Tax & Duty Free Melbourne Ai', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(390, 0, '0000-00-00', 'KABOOM (T2KB) SINGAPORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(391, 0, '0000-00-00', 'F1RST Tax & Duty Free Melbourne A', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(392, 0, '0000-00-00', 'Oravel Stays Priva INR www.oyorooms', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(393, 0, '0000-00-00', 'Primrose Service Apart Bangalore HQ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(394, 0, '0000-00-00', 'BALRAJ FILL AND FLY SIMROL', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(395, 0, '0000-00-00', 'PETRO SURCHARGE WAIVER', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(396, 0, '0000-00-00', 'UBER GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(397, 0, '0000-00-00', 'DEFENCE EXHIBITION ORG NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(398, 0, '0000-00-00', 'MPONLINE LIMITED BHOPAL', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(399, 0, '0000-00-00', 'INTERGLOBE AVIATION LT .', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(400, 0, '0000-00-00', 'WWW HOTSTAR COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(401, 0, '0000-00-00', 'WWW SONYLIV COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(402, 0, '0000-00-00', 'ZOMATO COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(403, 0, '0000-00-00', 'HATHWAY CABLE & DA INR MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(404, 0, '0000-00-00', 'ZOMATO COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(405, 0, '0000-00-00', 'FOOD BOX BANGALORE 4', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `tbl_fm_statement_line_item` (`id`, `statement_id`, `transaction_date`, `description`, `vendor_id`, `category_id`, `amount`, `transaction_type`, `cheque_number`, `main_balance`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(406, 0, '0000-00-00', 'MC DONALDS BANGALORE 1', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(407, 0, '0000-00-00', 'TRAVEL NEWS SERVICES INHYDERABAD', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(408, 0, '0000-00-00', 'BLR EBD TOI EXPRESS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(409, 0, '0000-00-00', 'KANHA WORKERS SAHKARI MANDLA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(410, 0, '0000-00-00', 'EDGE VENTURES BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(411, 0, '0000-00-00', 'TEA BREAK BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(412, 0, '0000-00-00', 'BURITO BOYS BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(413, 0, '0000-00-00', 'MAX HYPERMARKET INDIA PBANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(414, 0, '0000-00-00', 'CINEPOLIS INDIA PVT LT BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(415, 0, '0000-00-00', 'PRESTIGE ESTATES PROJE BANGALORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(416, 0, '0000-00-00', 'BAG ZONE RAIPUR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(417, 0, '0000-00-00', 'HATHWAY CABLE & DA INR MUMBAI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(418, 0, '0000-00-00', 'Primrose Service Apart Bangalore HQ', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(419, 0, '0000-00-00', 'SUYASH RETAIL ENTERPRI NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(420, 0, '0000-00-00', 'SHIVALIK MEDICAL CENTR NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(421, 0, '0000-00-00', 'DECATHLON SPORTS INDIA NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(422, 0, '0000-00-00', 'EBAY INDIA MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(423, 0, '0000-00-00', 'AGGREGATOR EMI - OFFUS', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(424, 0, '0000-00-00', 'BIKANERVALA NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(425, 0, '0000-00-00', 'APNA SWEETS INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(426, 0, '0000-00-00', 'KAVITAS RESTAURANT Delhi NCR', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(427, 0, '0000-00-00', 'DREAMZ INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(428, 0, '0000-00-00', 'SUPERTECH GROUP NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(429, 0, '0000-00-00', 'PADAM PETROLEUM NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(430, 0, '0000-00-00', 'P N HAIR AND BEAUTY SE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(431, 0, '0000-00-00', 'JET PRIVILEGE PVT LTD www.jetpriv', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(432, 0, '0000-00-00', 'JET AIRWAYS MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(433, 0, '0000-00-00', 'RABS HOSPITALITY DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(434, 0, '0000-00-00', 'APPETIZ INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(435, 0, '0000-00-00', 'MITHAS 24 INDORE', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(436, 0, '0000-00-00', 'PAYTM MOBILE SOLUT INR www.paytm.in', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(437, 0, '0000-00-00', 'CHINA GARDEN NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(438, 0, '0000-00-00', 'REEBOK NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(439, 0, '0000-00-00', 'ADDIDAS NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(440, 0, '0000-00-00', 'REEBOK NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(441, 0, '0000-00-00', 'FOOTSTEPS NEW DELHI', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(442, 0, '0000-00-00', 'LAKSHMI COFFEE HOUSE NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(443, 0, '0000-00-00', 'KALPAK NOIDA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(444, 0, '0000-00-00', 'ZOMATO COM GURGAON', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(445, 0, '0000-00-00', 'EasyEMI Sony Cashback Sep 17 (Ref# ST173410077000010325908)', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(446, 0, '0000-00-00', 'FREECHARGE MUMBA', 0, 0, 0, 0, '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_user`
--

DROP TABLE IF EXISTS `tbl_fm_user`;
CREATE TABLE IF NOT EXISTS `tbl_fm_user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(35) NOT NULL,
  `password` text NOT NULL,
  `pin` varchar(64) NOT NULL,
  `profile_image` varchar(255) NOT NULL,
  `prefer_contact` varchar(50) NOT NULL,
  `otp` text NOT NULL,
  `otp_expire_time` datetime NOT NULL,
  `loginattempt` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_user`
--

INSERT INTO `tbl_fm_user` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `pin`, `profile_image`, `prefer_contact`, `otp`, `otp_expire_time`, `loginattempt`, `status`, `archive`, `created`, `updated`) VALUES
(1, 'Amandeep', 'Singh', '', 'ff@gmail.com', '$2y$10$rX0/Wcx6ywLcf8N40PNeOe9CXDizsSABvrZysNCrmaMQQQILqa6PS', '', '', '', '', '0000-00-00 00:00:00', 0, 'active', 0, '2020-03-03 05:15:07', '2020-03-30 04:08:28'),
(2, 'lavanya', 'slsalasd', '', 'll@gmail.com', '$2y$10$HCdGD9anidzFoN02PRpBXu1t61pD9E7IkHpp7xIdVB.X9BfRVGJMe', '', '', '', '', '0000-00-00 00:00:00', 0, 'active', 0, '2020-03-29 22:46:57', '2020-03-30 04:16:57');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_user_address`
--

DROP TABLE IF EXISTS `tbl_fm_user_address`;
CREATE TABLE IF NOT EXISTS `tbl_fm_user_address` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `primary_address` tinyint(4) NOT NULL COMMENT '1- Primary, 2- Secondary',
  `street` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `postal` varchar(15) NOT NULL,
  `state` varchar(50) NOT NULL,
  `lat` varchar(200) NOT NULL,
  `lng` varchar(200) NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_user_address_fk1` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_user_login_history`
--

DROP TABLE IF EXISTS `tbl_fm_user_login_history`;
CREATE TABLE IF NOT EXISTS `tbl_fm_user_login_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `token` text NOT NULL,
  `user_agent` text NOT NULL,
  `login_time` timestamp NULL DEFAULT NULL,
  `logout_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_access` timestamp NULL DEFAULT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_user_login_history_fk1` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_user_login_history`
--

INSERT INTO `tbl_fm_user_login_history` (`id`, `user_id`, `ip_address`, `token`, `user_agent`, `login_time`, `logout_time`, `last_access`, `status`, `archive`, `created_date`, `updated_date`) VALUES
(1, 1, '::1', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJ0aW1lIjoiMjAyMC0wMy0wMyAxMTowNjo0NSJ9.8K8YM2aHfrUGjl5SJZ4fmWYHMsJkm9MXXBUoXcS0jo0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36', '2020-03-03 05:36:45', '2020-03-03 06:57:52', NULL, 'active', 0, '2020-03-03 10:06:45', '2020-03-03 10:27:52'),
(4, 2, '::1', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjIiLCJ0aW1lIjoiMjAyMC0wMy0zMCAwNDoxNzoxNCJ9.8jmtAEZq8A7kmYm4CJQNHOk3YJ2eVWS_y-GkFzGZNc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36', '2020-03-29 22:47:14', '2020-03-30 00:10:33', NULL, 'active', 0, '2020-03-30 04:17:15', '2020-03-30 04:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_vendor`
--

DROP TABLE IF EXISTS `tbl_fm_vendor`;
CREATE TABLE IF NOT EXISTS `tbl_fm_vendor` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(100) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `vendor_email` varchar(50) NOT NULL,
  `vendor_phone` varchar(15) NOT NULL,
  `vendor_address` varchar(150) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(5) NOT NULL,
  `country` varchar(5) NOT NULL,
  `pincode` varchar(15) NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_fm_vendor`
--

INSERT INTO `tbl_fm_vendor` (`id`, `vendor_name`, `category_id`, `vendor_email`, `vendor_phone`, `vendor_address`, `city`, `state`, `country`, `pincode`, `created_by`, `status`, `archive`, `created`, `updated`) VALUES
(1, 'AUTOPAY THANK YOU', 10, '', 'phone', 'address', '', '', '', '', 0, 'active', 0, '2020-03-27 10:16:19', '2020-03-30 04:29:59'),
(2, 'description', 11, '', '', '', '', '', '', '', 2, 'active', 0, '2020-03-27 10:16:23', '2020-03-30 04:27:02'),
(3, 'SMARTPAY CASHBACK', 12, '', '', 'ddd', '', '', '', '', 2, 'active', 0, '2020-03-27 10:16:30', '2020-03-30 04:30:27'),
(4, 'SMARTPAY ', 13, '', '', 'dd', '', '', '', '', 2, 'active', 0, '2020-03-27 10:16:38', '2020-03-30 04:30:28'),
(5, 'SMART PAY', 14, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:16:48', '2020-03-27 11:42:36'),
(6, 'CINEMAX INDIA', 15, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:17:01', '2020-03-27 11:42:53'),
(7, 'SMART PAY', 16, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:17:14', '2020-03-27 11:43:18'),
(8, 'MAKEMYTRIP INDIA L NEW', 17, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:17:30', '2020-03-27 11:43:54'),
(9, 'SMARTPAY ', 18, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:17:45', '2020-03-27 11:44:22'),
(10, 'SMART PAY', 19, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:18:03', '2020-03-27 11:44:56'),
(11, 'PAYPAL SHENZHENDAK', 20, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:08', '2020-03-27 11:45:40'),
(12, 'RELIANCE DIGITAL', 21, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:13', '2020-03-27 11:46:58'),
(13, 'SUVEEDHA INDORE', 22, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:19', '2020-03-27 11:47:56'),
(14, 'paytm', 23, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:19', '2020-03-27 11:49:07'),
(15, 'Netflix PGSI', 24, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:27', '2020-03-27 11:50:35'),
(16, 'PAYPAL MAKEMYTRIPI', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:36', '2020-03-27 10:24:36'),
(17, 'uber', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:37', '2020-03-27 10:24:37'),
(18, 'BUDDY RETAIL L NEW', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:24:47', '2020-03-27 10:24:47'),
(19, 'PUNJAB GRILL', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:25:00', '2020-03-27 10:25:00'),
(20, 'uber', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:25:00', '2020-03-27 10:25:00'),
(21, '', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:25:15', '2020-03-27 10:25:15'),
(22, 'BESCOM BILLDESK', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:25:32', '2020-03-27 10:25:32'),
(23, '', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:25:52', '2020-03-27 10:25:52'),
(24, 'CALIFORNIA BURRITO', NULL, '', '', '', '', '', '', '', 0, 'active', 0, '2020-03-27 10:26:11', '2020-03-27 10:26:11'),
(25, 'sdasdee', NULL, '', '4123123124', 'asdad asdasee', '', '', '', '', 2, 'active', 0, '2020-03-29 23:06:15', '2020-03-30 04:39:37'),
(26, 'ddd', NULL, '', '3466475877', 'jjj ggg hhh ', '', '', '', '', 2, 'active', 1, '2020-03-29 23:10:14', '2020-03-30 04:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fm_vendor_mapping`
--

DROP TABLE IF EXISTS `tbl_fm_vendor_mapping`;
CREATE TABLE IF NOT EXISTS `tbl_fm_vendor_mapping` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `archive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-Not deleted, 1-deleted',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_vendor_mapping_fk1` (`user_id`),
  KEY `tbl_vendor_mapping_fk2` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_fm_category_mapping`
--
ALTER TABLE `tbl_fm_category_mapping`
  ADD CONSTRAINT `tbl_category_mapping_fk1` FOREIGN KEY (`category_id`) REFERENCES `tbl_fm_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `tbl_category_mapping_fk2` FOREIGN KEY (`user_id`) REFERENCES `tbl_fm_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_fm_invoice`
--
ALTER TABLE `tbl_fm_invoice`
  ADD CONSTRAINT `tbl_finance_invoice_fk1` FOREIGN KEY (`user_id`) REFERENCES `tbl_fm_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `tbl_finance_invoice_fk2` FOREIGN KEY (`vendor_id`) REFERENCES `tbl_fm_vendor` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
