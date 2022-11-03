-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Erstellungszeit: 03. Nov 2022
-- Server-Version: 10.5.17-MariaDB-1:10.5.17+maria~deb11-log
-- PHP-Version: 7.0.33-0+deb9u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kb_users`
--

CREATE TABLE IF NOT EXISTS `kb_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_uid` tinytext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_email` tinytext NOT NULL,
  `user_pwd` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_vkey` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_verified` tinyint(1) NOT NULL DEFAULT 0,
  `user_timestamp` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `user_name` tinytext NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
