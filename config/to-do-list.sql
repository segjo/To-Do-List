-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Nov 2018 um 09:51
-- Server-Version: 5.7.24-0ubuntu0.16.04.1
-- PHP-Version: 7.0.32-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `to-do-list`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Item`
--

CREATE TABLE `Item` (
  `ItemId` int(11) NOT NULL,
  `ListId` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Deadline` datetime DEFAULT NULL,
  `SortIndex` int(11) NOT NULL DEFAULT '0',
  `CreatedAt` datetime NOT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `List`
--

CREATE TABLE `List` (
  `ListId` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Priority` int(11) NOT NULL DEFAULT '0',
  `SortIndex` int(11) DEFAULT '0',
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--

CREATE TABLE `User` (
  `UserId` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(250) NOT NULL,
  `ActivateCode` varchar(100) DEFAULT NULL,
  `EmailActivated` tinyint(1) NOT NULL DEFAULT '0',
  `UserName` varchar(100) NOT NULL,
  `Image` varchar(250) DEFAULT NULL,
  `EncryptedPassword` varchar(100) NOT NULL,
  `Salt` varchar(100) NOT NULL,
  `CreatedAt` datetime NOT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User2List`
--

CREATE TABLE `User2List` (
  `UserListId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `ListId` int(11) NOT NULL,
  `Owner` tinyint(1) NOT NULL DEFAULT '0',
  `ShareCode` varchar(100) DEFAULT NULL,
  `ShareActivated` tinyint(1) NOT NULL DEFAULT '0',
  `DeletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Item`
--
ALTER TABLE `Item`
  ADD PRIMARY KEY (`ItemId`),
  ADD KEY `ListId` (`ListId`);

--
-- Indizes für die Tabelle `List`
--
ALTER TABLE `List`
  ADD PRIMARY KEY (`ListId`),
  ADD KEY `OwnerId` (`Name`);

--
-- Indizes für die Tabelle `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`UserId`);

--
-- Indizes für die Tabelle `User2List`
--
ALTER TABLE `User2List`
  ADD PRIMARY KEY (`UserListId`),
  ADD KEY `UserId` (`UserId`),
  ADD KEY `ListId` (`ListId`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Item`
--
ALTER TABLE `Item`
  MODIFY `ItemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT für Tabelle `List`
--
ALTER TABLE `List`
  MODIFY `ListId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT für Tabelle `User`
--
ALTER TABLE `User`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
--
-- AUTO_INCREMENT für Tabelle `User2List`
--
ALTER TABLE `User2List`
  MODIFY `UserListId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Item`
--
ALTER TABLE `Item`
  ADD CONSTRAINT `Item_ibfk_1` FOREIGN KEY (`ListId`) REFERENCES `List` (`ListId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `User2List`
--
ALTER TABLE `User2List`
  ADD CONSTRAINT `User2List_ibfk_3` FOREIGN KEY (`UserId`) REFERENCES `User` (`UserId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `User2List_ibfk_4` FOREIGN KEY (`ListId`) REFERENCES `List` (`ListId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;