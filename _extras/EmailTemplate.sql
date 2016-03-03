-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Erstellungszeit: 03. Mrz 2016 um 22:07
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
-- Daten für Tabelle `EmailTemplate`
--

INSERT INTO `EmailTemplate` (`ID`, `ClassName`, `LastEdited`, `Created`, `Title`, `Subject`, `DocumentType`, `Content1`, `Content2`, `Content3`) VALUES
(6, 'EmailTemplate', '2016-03-03 13:52:22', '2016-03-02 02:18:43', 'Account | Ihr Datenblatt (Stand:[D])', 'Ihr Datenblatt (Stand:[D])', 'Account', '<p>Sehr geehrte/r [Anrede] [Vorname] [Nachname],</p><p>anbei erhalten Sie Ihr Datenblatt mit den gewünschten Zugangsdaten.</p><p>Bitte behandeln Sie diese Daten streng vertraulich.</p><p>Mit freundlichen Grüßen</p><p>[Firmeninhaber]</p>', NULL, NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `EmailTemplate`
--
ALTER TABLE `EmailTemplate`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ClassName` (`ClassName`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `EmailTemplate`
--
ALTER TABLE `EmailTemplate`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
