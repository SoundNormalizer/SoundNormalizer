-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 07, 2015 at 06:12 AM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.6.11-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `youtube2mp3`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversions`
--

CREATE TABLE IF NOT EXISTS `conversions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DuplicateOf` bigint(20) NOT NULL,
  `Type` text NOT NULL,
  `LocalName` text NOT NULL,
  `YouTubeID` text NOT NULL,
  `FileName` text NOT NULL,
  `FileHash` text NOT NULL,
  `Started` tinyint(1) NOT NULL DEFAULT '0',
  `Completed` tinyint(1) NOT NULL DEFAULT '0',
  `StatusCode` int(11) DEFAULT NULL,
  `Normalized` tinyint(1) NOT NULL,
  `IP` text NOT NULL,
  `TimeAdded` bigint(20) NOT NULL,
  `TimeStarted` bigint(20) DEFAULT NULL,
  `TimeCompleted` bigint(20) DEFAULT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
