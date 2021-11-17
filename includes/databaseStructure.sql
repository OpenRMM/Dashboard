-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 17, 2021 at 10:07 AM
-- Server version: 10.3.29-MariaDB
-- PHP Version: 7.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `OpenRMM`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `computer_id` int(10) DEFAULT 0,
  `company_id` int(10) NOT NULL DEFAULT 0,
  `user_id` int(10) NOT NULL DEFAULT 0,
  `details` varchar(1000) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `changelog`
--

CREATE TABLE `changelog` (
  `ID` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `computer_data_name` varchar(50) NOT NULL,
  `computer_data_key` varchar(250) NOT NULL,
  `old_value` text NOT NULL DEFAULT '',
  `new_value` text NOT NULL,
  `change_type` varchar(25) NOT NULL DEFAULT '',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

CREATE TABLE `commands` (
  `ID` int(11) NOT NULL,
  `computer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `command` varchar(500) NOT NULL DEFAULT '',
  `time_sent` timestamp(6) NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `expire_after` int(5) NOT NULL,
  `expire_time` timestamp(6) NULL DEFAULT NULL,
  `data_received` text NOT NULL DEFAULT '',
  `time_received` timestamp(6) NULL DEFAULT NULL,
  `hex` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `ID` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `address` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `comments` longtext CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `hex` varchar(100) NOT NULL DEFAULT '',
  `date_added` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `active` int(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `ID` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `computer_type` varchar(25) NOT NULL DEFAULT 'Desktop',
  `active` int(1) NOT NULL DEFAULT 1,
  `online` int(1) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(500) NOT NULL DEFAULT '',
  `show_alerts` int(1) DEFAULT 1,
  `date_added` datetime(6) DEFAULT current_timestamp(6),
  `last_update` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `hex` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `computer_data`
--

CREATE TABLE `computer_data` (
  `ID` bigint(20) NOT NULL,
  `computer_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `data` mediumblob DEFAULT NULL,
  `last_update` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `computer_data`
--
DELIMITER $$
CREATE TRIGGER `Update computers last_update when new computer_data added` AFTER INSERT ON `computer_data` FOR EACH ROW UPDATE computers SET last_update=NOW() WHERE ID=NEW.computer_id
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE `general` (
  `ID` int(11) NOT NULL,
  `agent_latest_version` varchar(10) NOT NULL,
  `sitewide_alert` text NOT NULL,
  `asset_history` int(1) NOT NULL DEFAULT 0,
  `server_status` int(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `details` text NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `computer_id` int(11) NOT NULL DEFAULT 0,
  `active` int(1) NOT NULL DEFAULT 1,
  `last_run` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ID` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL DEFAULT 0,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(10) NOT NULL DEFAULT 0,
  `title` varchar(200) NOT NULL DEFAULT '',
  `tags` varchar(200) NOT NULL DEFAULT '',
  `assignee` int(11) NOT NULL,
  `requester` varchar(100) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL DEFAULT 'New',
  `priority` varchar(10) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `due` varchar(100) NOT NULL DEFAULT '',
  `category` varchar(100) NOT NULL DEFAULT '',
  `subcategory` varchar(100) NOT NULL DEFAULT '',
  `cc` varchar(100) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT 1,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `hex` varchar(150) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `ID` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `message` text NOT NULL DEFAULT '',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(10) NOT NULL DEFAULT 'private',
  `hex` varchar(150) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `account_type` varchar(100) NOT NULL DEFAULT '''''',
  `nicename` varchar(100) NOT NULL DEFAULT '''''',
  `user_color` varchar(8) NOT NULL DEFAULT '',
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(400) NOT NULL DEFAULT '',
  `notes` varchar(400) NOT NULL DEFAULT '',
  `last_login` varchar(15) NOT NULL DEFAULT '',
  `recents` longtext NOT NULL DEFAULT '',
  `recentTickets` text NOT NULL DEFAULT '',
  `recent_edit` longtext NOT NULL DEFAULT '',
  `alert_settings` varchar(255) NOT NULL DEFAULT '''''',
  `user_activity` text NOT NULL DEFAULT '\'\'',
  `hex` varchar(200) NOT NULL,
  `active` int(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `computer_data_name` (`computer_data_name`),
  ADD KEY `computer_data_key` (`computer_data_key`),
  ADD KEY `computer_id` (`computer_id`);

--
-- Indexes for table `commands`
--
ALTER TABLE `commands`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ComputerID` (`computer_id`),
  ADD KEY `userid` (`user_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `name` (`name`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `CompanyID` (`company_id`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `computer_data`
--
ALTER TABLE `computer_data`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Hostname` (`computer_id`),
  ADD KEY `WMI_Name` (`name`);

--
-- Indexes for table `general`
--
ALTER TABLE `general`
  ADD UNIQUE KEY `ID` (`ID`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ComputerID` (`computer_id`),
  ADD KEY `computer_id` (`computer_id`),
  ADD KEY `active` (`active`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `computer_id` (`computer_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `active` (`active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `changelog`
--
ALTER TABLE `changelog`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commands`
--
ALTER TABLE `commands`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `computer_data`
--
ALTER TABLE `computer_data`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commands`
--
ALTER TABLE `commands`
  ADD CONSTRAINT `commands_ibfk_1` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `computer_data`
--
ALTER TABLE `computer_data`
  ADD CONSTRAINT `computer_data_ibfk_1` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`ID`) ON DELETE CASCADE ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
