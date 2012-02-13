-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Фев 08 2012 г., 16:56
-- Версия сервера: 5.5.20
-- Версия PHP: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `effect`
--

-- --------------------------------------------------------

--
-- Структура таблицы `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `name` varchar(100) NOT NULL,
  `time` int(12) NOT NULL,
  `eff` int(5) NOT NULL,
  `win` float NOT NULL,
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
