-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2026 at 10:44 AM
-- Server version: 11.4.10-MariaDB-cll-lve
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cwiltoo1_checkintesting`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_properties`
--

CREATE TABLE `account_properties` (
  `accountName` varchar(80) NOT NULL,
  `property` varchar(32) NOT NULL,
  `permission` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `account_properties`
--

INSERT INTO `account_properties` (`accountName`, `property`, `permission`) VALUES
('admin', 'Access Inactive Buildings', 1),
('admin', 'Accounts Page', 1),
('admin', 'Add Building Page', 1),
('admin', 'Archives Page', 1),
('admin', 'Can Toggle Check-ins', 1),
('admin', 'Contractors Page', 1),
('admin', 'Edit Buildings', 1),
('admin', 'Export Buildings', 1),
('admin', 'Filter IC', 1),
('admin', 'Filter Manager', 1),
('admin', 'Filter Today Only', 1),
('admin', 'Home Page', 1),
('admin', 'Import Page', 0),
('admin', 'Managers Page', 1),
('admin', 'Print QR', 1),
('admin', 'Search Building Name', 1),
('admin', 'See Building Name', 1),
('admin', 'See Check-in Status', 1),
('admin', 'See Check-in Time', 1),
('admin', 'See Days', 1),
('admin', 'See IC', 1),
('admin', 'See Manager', 1),
('admin', 'Select/Edit Multiple Buildings', 1),
('contractor', 'Access Inactive Buildings', 0),
('contractor', 'Accounts Page', 0),
('contractor', 'Add Building Page', 0),
('contractor', 'Archives Page', 1),
('contractor', 'Can Toggle Check-ins', 1),
('contractor', 'Contractors Page', 0),
('contractor', 'Edit Buildings', 0),
('contractor', 'Export Buildings', 1),
('contractor', 'Filter IC', 0),
('contractor', 'Filter Manager', 0),
('contractor', 'Filter Today Only', 1),
('contractor', 'Home Page', 1),
('contractor', 'Import Page', 0),
('contractor', 'Managers Page', 0),
('contractor', 'Print QR', 0),
('contractor', 'Search Building Name', 1),
('contractor', 'See Building Name', 1),
('contractor', 'See Check-in Status', 1),
('contractor', 'See Check-in Time', 1),
('contractor', 'See Days', 1),
('contractor', 'See IC', 0),
('contractor', 'See Manager', 1),
('contractor', 'Select/Edit Multiple Buildings', 0),
('manager', 'Access Inactive Buildings', 0),
('manager', 'Accounts Page', 0),
('manager', 'Add Building Page', 0),
('manager', 'Archives Page', 1),
('manager', 'Can Toggle Check-ins', 0),
('manager', 'Contractors Page', 0),
('manager', 'Edit Buildings', 0),
('manager', 'Export Buildings', 1),
('manager', 'Filter IC', 1),
('manager', 'Filter Manager', 1),
('manager', 'Filter Today Only', 1),
('manager', 'Home Page', 1),
('manager', 'Import Page', 0),
('manager', 'Managers Page', 0),
('manager', 'Print QR', 1),
('manager', 'Search Building Name', 1),
('manager', 'See Building Name', 1),
('manager', 'See Check-in Status', 1),
('manager', 'See Check-in Time', 1),
('manager', 'See Days', 1),
('manager', 'See IC', 1),
('manager', 'See Manager', 1),
('manager', 'Select/Edit Multiple Buildings', 0);

-- --------------------------------------------------------

--
-- Table structure for table `archive`
--

CREATE TABLE `archive` (
  `archiveDate` datetime NOT NULL,
  `name` varchar(80) NOT NULL,
  `manager` varchar(40) NOT NULL,
  `ic` varchar(80) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  `checkedTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `name` varchar(80) NOT NULL,
  `manager` varchar(40) NOT NULL,
  `ic` varchar(80) NOT NULL,
  `checked` tinyint(1) NOT NULL DEFAULT 0,
  `checkedTime` datetime DEFAULT NULL,
  `monday` tinyint(1) NOT NULL,
  `tuesday` tinyint(1) NOT NULL,
  `wednesday` tinyint(1) NOT NULL,
  `thursday` tinyint(1) NOT NULL,
  `friday` tinyint(1) NOT NULL,
  `saturday` tinyint(1) NOT NULL,
  `sunday` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractors`
--

CREATE TABLE `contractors` (
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sse_signal`
--

CREATE TABLE `sse_signal` (
  `id` int(11) NOT NULL,
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` uuid NOT NULL DEFAULT uuid(),
  `username` varchar(80) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `resetTimer` datetime DEFAULT NULL,
  `accountType` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_properties`
--
ALTER TABLE `account_properties`
  ADD PRIMARY KEY (`accountName`,`property`) USING BTREE;

--
-- Indexes for table `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`archiveDate`,`name`),
  ADD KEY `manager-index` (`manager`),
  ADD KEY `ic-index` (`ic`);

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `contractors`
--
ALTER TABLE `contractors`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `sse_signal`
--
ALTER TABLE `sse_signal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username-unique` (`username`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
