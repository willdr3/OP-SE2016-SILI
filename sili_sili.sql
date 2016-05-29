-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 05, 2016 at 09:58 AM
-- Server version: 5.5.44
-- PHP Version: 5.6.9-0+deb8u1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sili_sili`
--

-- --------------------------------------------------------

--
-- Table structure for table `Activity`
--

CREATE TABLE IF NOT EXISTS `Activity` (
  `activityID` int(10) NOT NULL AUTO_INCREMENT,
  `userID` int(10) NOT NULL,
  `sayID` int(10) NOT NULL,
  `activity` varchar(10) NOT NULL,
  `timeOfAction` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activityID`),
  KEY `userID` (`userID`),
  KEY `sayID` (`sayID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=267 ;

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE IF NOT EXISTS `Comments` (
  `sayID` int(10) NOT NULL,
  `commentID` int(10) NOT NULL,
  PRIMARY KEY (`sayID`,`commentID`),
  KEY `commentID` (`commentID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Listeners`
--

CREATE TABLE IF NOT EXISTS `Listeners` (
  `userID` int(10) NOT NULL,
  `listenerUserID` int(10) NOT NULL,
  `dateFollowed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`,`listenerUserID`),
  KEY `listenerUserID` (`listenerUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE IF NOT EXISTS `Message` (
  `userID` int(10) NOT NULL,
  `recipientUserID` int(10) NOT NULL,
  `message` varchar(255) NOT NULL,
  `timeSent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`,`recipientUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Profile`
--

CREATE TABLE IF NOT EXISTS `Profile` (
  `profileID` varchar(20) NOT NULL,
  `userID` int(10) NOT NULL,
  `userName` varchar(25) NOT NULL,
  `profileImage` varchar(256) NOT NULL,
  `userBio` varchar(500) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `gender` varchar(1) NOT NULL,
  `location` varchar(50) NOT NULL,
  `joinDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Says`
--

CREATE TABLE IF NOT EXISTS `Says` (
  `sayID` int(10) NOT NULL AUTO_INCREMENT,
  `timePosted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int(10) NOT NULL,
  `message` varchar(260) NOT NULL,
  PRIMARY KEY (`sayID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Table structure for table `UserLogin`
--

CREATE TABLE IF NOT EXISTS `UserLogin` (
  `userID` int(10) NOT NULL AUTO_INCREMENT,
  `userEmail` varchar(100) NOT NULL,
  `userPassword` varchar(256) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `UserSessions`
--

CREATE TABLE IF NOT EXISTS `UserSessions` (
  `sessionID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `rememberMeToken` varchar(64) DEFAULT NULL,
  `lastVisit` datetime DEFAULT NULL,
  `lastVisitAgent` text,
  `loginIP` varchar(39) NOT NULL DEFAULT '0.0.0.0',
  `loginDatetime` datetime NOT NULL,
  `loginAgent` text,
  PRIMARY KEY (`sessionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Activity`
--
ALTER TABLE `Activity`
  ADD CONSTRAINT `Activity_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `UserLogin` (`userID`),
  ADD CONSTRAINT `Activity_ibfk_2` FOREIGN KEY (`sayID`) REFERENCES `Says` (`sayID`);

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `Comments_ibfk_1` FOREIGN KEY (`sayID`) REFERENCES `Says` (`sayID`),
  ADD CONSTRAINT `Comments_ibfk_2` FOREIGN KEY (`commentID`) REFERENCES `Says` (`sayID`);

--
-- Constraints for table `Listeners`
--
ALTER TABLE `Listeners`
  ADD CONSTRAINT `Listeners_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `UserLogin` (`userID`),
  ADD CONSTRAINT `Listeners_ibfk_2` FOREIGN KEY (`listenerUserID`) REFERENCES `UserLogin` (`userID`);

--
-- Constraints for table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `Message_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `UserLogin` (`userID`);

--
-- Constraints for table `Profile`
--
ALTER TABLE `Profile`
  ADD CONSTRAINT `Profile_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `UserLogin` (`userID`);

--
-- Constraints for table `Says`
--
ALTER TABLE `Says`
  ADD CONSTRAINT `Says_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `UserLogin` (`userID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
