-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Jul 2024 um 19:21
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `notizbuch`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `notizen`
--

CREATE TABLE `notizen` (
  `note_id` int(11) NOT NULL,
  `titel` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `fk_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `notizen`
--

INSERT INTO `notizen` (`note_id`, `titel`, `content`, `created_at`, `fk_user_id`) VALUES
(11, 'Erster Entwurf für das Projekt', 'Das Projekt befindet sich noch in der Anfangsphase. Wir haben grundlegende Funktionen implementiert.', '2024-07-08 13:58:51', 1),
(12, 'Designüberlegungen für die Benutzeroberfläche', 'Das neue Designkonzept konzentriert sich auf Benutzerfreundlichkeit und Ästhetik.', '2024-07-08 14:00:07', 2),
(13, 'Meeting-Protokoll vom letzten Kundenmeeting', 'Die Besprechung mit dem Kunden verlief erfolgreich. Neue Anforderungen wurden besprochen und dokumentiert.', '2024-07-08 14:04:33', 2),
(16, 'Einführung neuer Sicherheitsrichtlinien', 'Implementierung von strengeren Sicherheitsmaßnahmen zur Gewährleistung der Datensicherheit.', '2024-07-09 16:24:01', 2),
(17, 'Neue Marketingstrategien für das Jahr 2024', 'Analyse und Planung von Marketingkampagnen zur Steigerung der Markenbekanntheit und Kundenbindung.', '2024-07-09 16:27:25', 1),
(18, 'Überprüfung der Geschäftsergebnisse des letzten Quartals', 'Analyse der finanziellen Ergebnisse und strategische Ausrichtung für das nächste Quartal.', '2024-07-09 16:30:49', 2),
(19, 'Entwicklung eines neuen Produkts für den Marktstart', 'Vorbereitungen für die Markteinführung eines innovativen Produkts, das neue Marktsegmente erschließt.', '2024-07-11 09:51:00', 1),
(20, 'Fortsetzung der Entwicklungsarbeiten am neuen Feature', 'Die Entwicklung des neuen Features verläuft nach Plan. Wir optimieren die Performance und die Benutzeroberfläche.', '2024-07-11 09:51:16', 1),
(21, 'Evaluierung der aktuellen Geschäftsstrategie', 'Analyse und Bewertung der aktuellen Geschäftsstrategie zur Identifizierung von Optimierungspotenzialen.', '2024-07-11 10:20:09', 1),
(22, 'Vorbereitung auf das nächste Teammeeting', 'Agenda und Vorbereitungen für das anstehende Teammeeting sind abgeschlossen.', '2024-07-11 10:26:46', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `fk_user_id` int(11) DEFAULT NULL,
  `fk_note_id` int(11) DEFAULT NULL,
  `can_view` tinyint(1) DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `permissions`
--

INSERT INTO `permissions` (`id`, `fk_user_id`, `fk_note_id`, `can_view`, `can_edit`) VALUES
(4, 1, 11, 1, 1),
(5, 1, 12, 1, 1),
(6, 2, 12, 1, 1),
(7, 1, 13, 1, 1),
(8, 2, 13, 1, 1),
(12, 2, 16, 0, 1),
(13, 2, 17, 1, 1),
(14, 2, 20, 1, 1),
(15, 1, 21, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`) VALUES
(1, 'TestOne', '96cae35ce8a9b0244178bf28e4966c2ce1b8385723a96a6b838858cdd6ca0a1e', 'Testiger', 'Tester'),
(2, 'TestTwo', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'Testigzwo', 'Tester');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `notizen`
--
ALTER TABLE `notizen`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `fk_user_id` (`fk_user_id`);

--
-- Indizes für die Tabelle `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`fk_user_id`),
  ADD KEY `fk_note_id` (`fk_note_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `benutzername` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `notizen`
--
ALTER TABLE `notizen`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT für Tabelle `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `notizen`
--
ALTER TABLE `notizen`
  ADD CONSTRAINT `notizen_ibfk_1` FOREIGN KEY (`fk_user_id`) REFERENCES `users` (`id`);

--
-- Constraints der Tabelle `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`fk_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`fk_note_id`) REFERENCES `notizen` (`note_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
