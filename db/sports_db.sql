-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2024 at 12:58 PM
-- Server version: 8.0.37
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sports_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aggregatedstatistics`
--

CREATE TABLE `aggregatedstatistics` (
  `AggregatedID` int NOT NULL,
  `SportID` int DEFAULT NULL,
  `TeamID` int DEFAULT NULL,
  `PlayerID` int DEFAULT NULL,
  `MetricName` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MetricValue` float DEFAULT NULL,
  `CalculationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arbitrators`
--

CREATE TABLE `arbitrators` (
  `ArbitratorID` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SportID` int NOT NULL,
  `Experience` text COLLATE utf8mb4_general_ci,
  `MatchesOfficiated` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bigevent`
--

CREATE TABLE `bigevent` (
  `BigEventID` int NOT NULL,
  `EventName` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `EventDate` date DEFAULT NULL,
  `Location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bigeventawards`
--

CREATE TABLE `bigeventawards` (
  `AwardID` int NOT NULL,
  `BigEventID` int NOT NULL,
  `SportID` int DEFAULT NULL,
  `TeamID` int DEFAULT NULL,
  `PlayerID` int DEFAULT NULL,
  `AwardName` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `AwardDescription` text COLLATE utf8mb4_general_ci,
  `AwardCriteria` text COLLATE utf8mb4_general_ci,
  `AwardDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coaches`
--

CREATE TABLE `coaches` (
  `CoachID` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SportID` int NOT NULL,
  `Experience` text COLLATE utf8mb4_general_ci,
  `Achievements` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coachresources`
--

CREATE TABLE `coachresources` (
  `ResourceID` int NOT NULL,
  `CoachID` int NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `FileURL` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `UploadDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventparticipants`
--

CREATE TABLE `eventparticipants` (
  `ParticipationID` int NOT NULL,
  `EventID` int NOT NULL,
  `TeamID` int DEFAULT NULL,
  `PlayerID` int DEFAULT NULL,
  `Role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `EventID` int NOT NULL,
  `EventName` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `EventDate` date DEFAULT NULL,
  `SportID` int DEFAULT NULL,
  `Location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `EventType` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fancomments`
--

CREATE TABLE `fancomments` (
  `CommentID` int NOT NULL,
  `MatchID` int NOT NULL,
  `FanName` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Comment` text COLLATE utf8mb4_general_ci,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `MatchID` int NOT NULL,
  `Date` date NOT NULL,
  `SportID` int NOT NULL,
  `Team1ID` int NOT NULL,
  `Team2ID` int NOT NULL,
  `ScoreTeam1` int DEFAULT NULL,
  `ScoreTeam2` int DEFAULT NULL,
  `PlayerStats` json DEFAULT NULL,
  `ArbiterID` int DEFAULT NULL,
  `InjuriesReported` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playerperformance`
--

CREATE TABLE `playerperformance` (
  `PerformanceID` int NOT NULL,
  `MatchID` int NOT NULL,
  `PlayerID` int NOT NULL,
  `MetricName` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MetricValue` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `PlayerID` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SportID` int NOT NULL,
  `TeamID` int DEFAULT NULL,
  `Position` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `FootballStatisticsID` int DEFAULT NULL,
  `TennisStatisticsID` int DEFAULT NULL,
  `BasketballStatisticsID` int DEFAULT NULL,
  `Injuries` text COLLATE utf8mb4_general_ci,
  `Accomplishments` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE `questionnaires` (
  `QuestionnaireID` int NOT NULL,
  `StudentID` int DEFAULT NULL,
  `Responses` json DEFAULT NULL,
  `SuggestedSport` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `ScheduleID` int NOT NULL,
  `SportID` int NOT NULL,
  `Date` date NOT NULL,
  `Event` enum('Match','Tournament','Event') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `EventID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `SportID` int NOT NULL,
  `SportName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `strategysuggestions`
--

CREATE TABLE `strategysuggestions` (
  `SuggestionID` int NOT NULL,
  `TeamID` int NOT NULL,
  `MatchID` int DEFAULT NULL,
  `SuggestionText` text COLLATE utf8mb4_general_ci,
  `GeneratedDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teamperformance`
--

CREATE TABLE `teamperformance` (
  `PerformanceID` int NOT NULL,
  `TeamID` int NOT NULL,
  `MatchID` int NOT NULL,
  `MetricName` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MetricValue` float DEFAULT NULL,
  `RecordedDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `TeamID` int NOT NULL,
  `TeamName` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SportID` int NOT NULL,
  `CoachID` int DEFAULT NULL,
  `WinLossRecord` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `GeneralStatistics` text COLLATE utf8mb4_general_ci,
  `Accomplishments` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `TournamentID` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SportID` int NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `TransferID` int NOT NULL,
  `PlayerID` int NOT NULL,
  `FromTeamID` int NOT NULL,
  `ToTeamID` int DEFAULT NULL,
  `TransferDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aggregatedstatistics`
--
ALTER TABLE `aggregatedstatistics`
  ADD PRIMARY KEY (`AggregatedID`),
  ADD KEY `SportID` (`SportID`),
  ADD KEY `TeamID` (`TeamID`),
  ADD KEY `PlayerID` (`PlayerID`);

--
-- Indexes for table `arbitrators`
--
ALTER TABLE `arbitrators`
  ADD PRIMARY KEY (`ArbitratorID`),
  ADD KEY `SportID` (`SportID`);

--
-- Indexes for table `bigevent`
--
ALTER TABLE `bigevent`
  ADD PRIMARY KEY (`BigEventID`);

--
-- Indexes for table `bigeventawards`
--
ALTER TABLE `bigeventawards`
  ADD PRIMARY KEY (`AwardID`),
  ADD KEY `BigEventID` (`BigEventID`),
  ADD KEY `SportID` (`SportID`),
  ADD KEY `TeamID` (`TeamID`),
  ADD KEY `PlayerID` (`PlayerID`);

--
-- Indexes for table `coaches`
--
ALTER TABLE `coaches`
  ADD PRIMARY KEY (`CoachID`),
  ADD KEY `SportID` (`SportID`);

--
-- Indexes for table `coachresources`
--
ALTER TABLE `coachresources`
  ADD PRIMARY KEY (`ResourceID`),
  ADD KEY `CoachID` (`CoachID`);

--
-- Indexes for table `eventparticipants`
--
ALTER TABLE `eventparticipants`
  ADD PRIMARY KEY (`ParticipationID`),
  ADD KEY `EventID` (`EventID`),
  ADD KEY `TeamID` (`TeamID`),
  ADD KEY `PlayerID` (`PlayerID`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`EventID`),
  ADD KEY `SportID` (`SportID`);

--
-- Indexes for table `fancomments`
--
ALTER TABLE `fancomments`
  ADD PRIMARY KEY (`CommentID`),
  ADD KEY `MatchID` (`MatchID`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`MatchID`),
  ADD KEY `SportID` (`SportID`),
  ADD KEY `Team1ID` (`Team1ID`),
  ADD KEY `Team2ID` (`Team2ID`),
  ADD KEY `ArbiterID` (`ArbiterID`);

--
-- Indexes for table `playerperformance`
--
ALTER TABLE `playerperformance`
  ADD PRIMARY KEY (`PerformanceID`),
  ADD KEY `MatchID` (`MatchID`),
  ADD KEY `PlayerID` (`PlayerID`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`PlayerID`),
  ADD KEY `SportID` (`SportID`),
  ADD KEY `TeamID` (`TeamID`);

--
-- Indexes for table `questionnaires`
--
ALTER TABLE `questionnaires`
  ADD PRIMARY KEY (`QuestionnaireID`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `SportID` (`SportID`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`SportID`);

--
-- Indexes for table `strategysuggestions`
--
ALTER TABLE `strategysuggestions`
  ADD PRIMARY KEY (`SuggestionID`),
  ADD KEY `TeamID` (`TeamID`),
  ADD KEY `MatchID` (`MatchID`);

--
-- Indexes for table `teamperformance`
--
ALTER TABLE `teamperformance`
  ADD PRIMARY KEY (`PerformanceID`),
  ADD KEY `TeamID` (`TeamID`),
  ADD KEY `MatchID` (`MatchID`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`TeamID`),
  ADD KEY `SportID` (`SportID`),
  ADD KEY `CoachID` (`CoachID`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`TournamentID`),
  ADD KEY `SportID` (`SportID`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`TransferID`),
  ADD KEY `PlayerID` (`PlayerID`),
  ADD KEY `FromTeamID` (`FromTeamID`),
  ADD KEY `ToTeamID` (`ToTeamID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aggregatedstatistics`
--
ALTER TABLE `aggregatedstatistics`
  MODIFY `AggregatedID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arbitrators`
--
ALTER TABLE `arbitrators`
  MODIFY `ArbitratorID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bigevent`
--
ALTER TABLE `bigevent`
  MODIFY `BigEventID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bigeventawards`
--
ALTER TABLE `bigeventawards`
  MODIFY `AwardID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coaches`
--
ALTER TABLE `coaches`
  MODIFY `CoachID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coachresources`
--
ALTER TABLE `coachresources`
  MODIFY `ResourceID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventparticipants`
--
ALTER TABLE `eventparticipants`
  MODIFY `ParticipationID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `EventID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fancomments`
--
ALTER TABLE `fancomments`
  MODIFY `CommentID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `MatchID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `playerperformance`
--
ALTER TABLE `playerperformance`
  MODIFY `PerformanceID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `PlayerID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questionnaires`
--
ALTER TABLE `questionnaires`
  MODIFY `QuestionnaireID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ScheduleID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `SportID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `strategysuggestions`
--
ALTER TABLE `strategysuggestions`
  MODIFY `SuggestionID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teamperformance`
--
ALTER TABLE `teamperformance`
  MODIFY `PerformanceID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `TeamID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `TournamentID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `TransferID` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aggregatedstatistics`
--
ALTER TABLE `aggregatedstatistics`
  ADD CONSTRAINT `aggregatedstatistics_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`),
  ADD CONSTRAINT `aggregatedstatistics_ibfk_2` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `aggregatedstatistics_ibfk_3` FOREIGN KEY (`PlayerID`) REFERENCES `players` (`PlayerID`);

--
-- Constraints for table `arbitrators`
--
ALTER TABLE `arbitrators`
  ADD CONSTRAINT `arbitrators_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`);

--
-- Constraints for table `bigeventawards`
--
ALTER TABLE `bigeventawards`
  ADD CONSTRAINT `bigeventawards_ibfk_1` FOREIGN KEY (`BigEventID`) REFERENCES `bigevent` (`BigEventID`),
  ADD CONSTRAINT `bigeventawards_ibfk_2` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`),
  ADD CONSTRAINT `bigeventawards_ibfk_3` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `bigeventawards_ibfk_4` FOREIGN KEY (`PlayerID`) REFERENCES `players` (`PlayerID`);

--
-- Constraints for table `coaches`
--
ALTER TABLE `coaches`
  ADD CONSTRAINT `coaches_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`);

--
-- Constraints for table `coachresources`
--
ALTER TABLE `coachresources`
  ADD CONSTRAINT `coachresources_ibfk_1` FOREIGN KEY (`CoachID`) REFERENCES `coaches` (`CoachID`);

--
-- Constraints for table `eventparticipants`
--
ALTER TABLE `eventparticipants`
  ADD CONSTRAINT `eventparticipants_ibfk_1` FOREIGN KEY (`EventID`) REFERENCES `events` (`EventID`),
  ADD CONSTRAINT `eventparticipants_ibfk_2` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `eventparticipants_ibfk_3` FOREIGN KEY (`PlayerID`) REFERENCES `players` (`PlayerID`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`);

--
-- Constraints for table `fancomments`
--
ALTER TABLE `fancomments`
  ADD CONSTRAINT `fancomments_ibfk_1` FOREIGN KEY (`MatchID`) REFERENCES `matches` (`MatchID`);

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`),
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`Team1ID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`Team2ID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`ArbiterID`) REFERENCES `arbitrators` (`ArbitratorID`);

--
-- Constraints for table `playerperformance`
--
ALTER TABLE `playerperformance`
  ADD CONSTRAINT `playerperformance_ibfk_1` FOREIGN KEY (`MatchID`) REFERENCES `matches` (`MatchID`),
  ADD CONSTRAINT `playerperformance_ibfk_2` FOREIGN KEY (`PlayerID`) REFERENCES `players` (`PlayerID`);

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`),
  ADD CONSTRAINT `players_ibfk_2` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`);

--
-- Constraints for table `strategysuggestions`
--
ALTER TABLE `strategysuggestions`
  ADD CONSTRAINT `strategysuggestions_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `strategysuggestions_ibfk_2` FOREIGN KEY (`MatchID`) REFERENCES `matches` (`MatchID`);

--
-- Constraints for table `teamperformance`
--
ALTER TABLE `teamperformance`
  ADD CONSTRAINT `teamperformance_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `teamperformance_ibfk_2` FOREIGN KEY (`MatchID`) REFERENCES `matches` (`MatchID`);

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`),
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`CoachID`) REFERENCES `coaches` (`CoachID`);

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`SportID`) REFERENCES `sports` (`SportID`);

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`PlayerID`) REFERENCES `players` (`PlayerID`),
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`FromTeamID`) REFERENCES `teams` (`TeamID`),
  ADD CONSTRAINT `transfers_ibfk_3` FOREIGN KEY (`ToTeamID`) REFERENCES `teams` (`TeamID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
