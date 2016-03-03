-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Erstellungszeit: 03. Mrz 2016 um 22:03
-- Server-Version: 10.0.23-MariaDB-0+deb8u1
-- PHP-Version: 5.6.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `csym-live`
--

-- --------------------------------------------------------

--
-- Daten für Tabelle `DocumentTemplate`
--

INSERT INTO `DocumentTemplate` (`ID`, `ClassName`, `LastEdited`, `Created`, `Title`, `DocumentType`, `FirstNumber`, `Prefix`, `Content1`, `Content2`, `Content3`, `YearlyReset`, `ZeroFill`) VALUES
(5, 'DocumentTemplate', '2016-03-03 13:50:34', '2016-03-01 00:21:54', 'Account | 0', 'Account', 0, NULL, NULL, NULL, NULL, 1, 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `DocumentTemplate`
--
ALTER TABLE `DocumentTemplate`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ClassName` (`ClassName`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `DocumentTemplate`
--
ALTER TABLE `DocumentTemplate`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
