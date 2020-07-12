-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 21, 2017 at 09:07 AM
-- Server version: 10.0.29-MariaDB-0ubuntu0.16.04.1
-- PHP Version: 7.0.13-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `odin`
--

-- --------------------------------------------------------

--
-- Table structure for table `authentication_provider`
--

CREATE TABLE `authentication_provider` (
  `id` tinyint(2) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_id` int(11) UNSIGNED NOT NULL,
  `provider` varchar(25) NOT NULL,
  `type` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `authentication_provider`
--

INSERT INTO `authentication_provider` (`id`, `deleted`, `created_date`, `created_by_id`, `provider`, `type`, `name`) VALUES
(1, 0, '2017-02-20 17:41:14', 1, 'buit-in', 'Database', 'Built-In Database'),
(2, 0, '2017-02-20 17:41:14', 1, 'google', 'Oauth2', 'Google'),
(3, 0, '2017-02-20 17:41:14', 1, 'facebook', 'Oauth2', 'Facebook'),
(4, 0, '2017-02-20 17:41:14', 1, 'twitter', 'Oauth2', 'Twitter');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `authentication_provider_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `email` varchar(100) NOT NULL,
  `salt` varchar(150) NOT NULL,
  `hash` varchar(150) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `change_password` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `deleted`, `created_date`, `created_by_id`, `authentication_provider_id`, `email`, `salt`, `hash`, `first_name`, `last_name`, `slug`, `last_login`, `change_password`) VALUES
(1, 1, now(), 1, 1, 'SYSTEM@LOCAL', '', '', 'LOCAL', 'SYSTEM', 'system', NULL, 0),(2, 0, now(), 1, 1, 'info@wayneoliver.co.za', '', '', 'Wayne', 'Oliver', 'wayne-oliver', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_custom`
--

CREATE TABLE `user_custom` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `photo_url` varchar(255) NOT NULL DEFAULT 'static/img/avatar-generic.png'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_custom`
--

INSERT INTO `user_custom` (`user_id`, `photo_url`) VALUES
(1, 'static/img/avatar-generic.png'),(2, 'static/img/avatar-generic.png');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `id` int(11) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `role` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`id`, `deleted`, `created_date`, `created_by_id`, `role`) VALUES
(1, 0, '2017-02-17 09:09:50', 1, 'Global Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `user_role_membership`
--

CREATE TABLE `user_role_membership` (
  `id` int(11) UNSIGNED NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_role_membership`
--

INSERT INTO `user_role_membership` (`id`, `deleted`, `created_date`, `created_by_id`, `user_id`, `role_id`) VALUES
(1, 0, '2017-02-17 09:10:13', 1, 1, 1),(2, 0, '2017-02-17 09:10:13', 1, 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authentication_provider`
--
ALTER TABLE `authentication_provider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD KEY `authentication_provider_id` (`authentication_provider_id`);

--
-- Indexes for table `user_custom`
--
ALTER TABLE `user_custom`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_entity_per_entity_type` (`role`) USING BTREE;

--
-- Indexes for table `user_role_membership`
--
ALTER TABLE `user_role_membership`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authentication_provider`
--
ALTER TABLE `authentication_provider`
  MODIFY `id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_role_membership`
--
ALTER TABLE `user_role_membership`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`authentication_provider_id`) REFERENCES `authentication_provider` (`id`);

--
-- Constraints for table `user_custom`
--
ALTER TABLE `user_custom`
  ADD CONSTRAINT `user_custom_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
