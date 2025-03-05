-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Янв 09 2025 г., 14:55
-- Версия сервера: 8.3.0
-- Версия PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `polyclinic`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin`
--

INSERT INTO `admin` (`id`, `Name`, `Surname`, `Password`, `Username`) VALUES
(3, 'Джордж', 'Лукас', '$2y$10$gWAg3NMmdfm56zz5ZmDrgOtMVftbXQ2wus.u1.q2mVjdR4UHaLwQ6', 'ptr1'),
(2, 'Николай', 'Галкин', '$2y$10$hU7aMSUSoAwOxpgcsAhFyeNvNAJTGgUoC2OjmKgD5zhWstq5Fl9oW', 'admin2');

-- --------------------------------------------------------

--
-- Структура таблицы `coupon`
--

DROP TABLE IF EXISTS `coupon`;
CREATE TABLE IF NOT EXISTS `coupon` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Time` time NOT NULL,
  `Office` varchar(10) DEFAULT NULL,
  `doctor_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `coupon`
--

INSERT INTO `coupon` (`id`, `Date`, `Time`, `Office`, `doctor_id`, `patient_id`) VALUES
(1, '2024-12-25', '15:25:00', '101', 12, NULL),
(2, '2024-12-25', '15:50:00', '101', 12, NULL),
(3, '2024-12-25', '16:10:00', '101', 12, NULL),
(4, '2024-12-26', '15:36:00', '502', 15, NULL),
(5, '2024-12-27', '23:56:00', '502', 15, NULL),
(6, '2024-12-11', '15:58:00', '502', 15, NULL),
(7, '2024-12-30', '15:15:00', '251', 12, 2),
(8, '2024-12-27', '12:08:00', '101', 12, 2),
(9, '2024-12-17', '11:31:57', '101', 12, 2),
(10, '2025-01-02', '21:09:00', '201', 14, 4),
(11, '2024-12-31', '22:11:00', '205', 14, NULL),
(12, '2024-12-23', '11:31:51', '205', 14, 4),
(13, '2025-01-09', '20:25:00', '107', 11, 2),
(14, '2025-01-09', '20:55:00', '107', 11, 4),
(15, '2025-01-04', '10:30:00', '106', 11, 2),
(18, '2025-01-13', '10:50:00', '101', 12, 4),
(19, '2025-01-13', '11:05:00', '101', 12, 3),
(26, '2025-01-13', '12:50:00', '101', 12, 2),
(27, '2025-01-13', '13:05:00', '101', 12, 2),
(28, '2025-01-13', '13:20:00', '101', 12, 4),
(29, '2025-01-13', '13:35:00', '101', 12, 2),
(30, '2025-01-13', '13:50:00', '101', 12, 2),
(31, '2025-01-13', '14:05:00', '101', 12, 2),
(32, '2025-01-13', '14:20:00', '101', 12, 3),
(33, '2025-01-13', '14:35:00', '101', 12, 3),
(34, '2025-01-13', '14:50:00', '101', 12, 3),
(35, '2025-01-13', '15:05:00', '101', 12, 4),
(36, '2025-01-13', '15:20:00', '101', 12, 4),
(37, '2025-01-13', '15:35:00', '101', 12, 4),
(38, '2025-01-13', '15:50:00', '101', 12, 4),
(39, '2025-01-13', '16:05:00', '101', 12, 3),
(40, '2025-01-13', '16:20:00', '101', 12, NULL),
(41, '2025-01-13', '16:35:00', '101', 12, NULL),
(42, '2025-01-13', '16:50:00', '101', 12, NULL),
(43, '2025-01-13', '17:05:00', '101', 12, NULL),
(44, '2025-01-13', '17:20:00', '101', 12, NULL),
(45, '2025-01-13', '17:35:00', '101', 12, NULL),
(46, '2025-01-13', '17:50:00', '101', 12, NULL),
(47, '2025-01-13', '18:05:00', '101', 12, NULL),
(48, '2025-01-13', '18:20:00', '101', 12, NULL),
(49, '2025-01-13', '18:35:00', '101', 12, NULL),
(50, '2025-01-13', '18:50:00', '101', 12, NULL),
(51, '2025-01-13', '19:05:00', '101', 12, NULL),
(52, '2025-01-13', '19:20:00', '101', 12, NULL),
(53, '2025-01-13', '19:35:00', '101', 12, NULL),
(54, '2025-01-13', '19:50:00', '101', 12, NULL),
(55, '2025-01-13', '20:05:00', '101', 12, NULL),
(56, '2025-01-07', '12:20:00', '103', 12, NULL),
(57, '2025-01-07', '12:35:00', '103', 12, NULL),
(58, '2025-01-07', '12:50:00', '103', 12, NULL),
(59, '2025-01-07', '13:05:00', '103', 12, NULL),
(60, '2025-01-07', '13:20:00', '103', 12, NULL),
(61, '2025-01-07', '13:35:00', '103', 12, NULL),
(62, '2025-01-07', '13:50:00', '103', 12, NULL),
(63, '2025-01-07', '14:05:00', '103', 12, NULL),
(64, '2025-01-08', '12:00:00', '105', 13, 3),
(66, '2025-01-08', '12:30:00', '105', 13, 3),
(67, '2025-01-08', '12:45:00', '105', 13, NULL),
(68, '2025-01-08', '13:00:00', '105', 13, NULL),
(69, '2025-01-08', '13:15:00', '105', 13, NULL),
(70, '2025-01-08', '13:30:00', '105', 13, NULL),
(71, '2025-01-08', '13:45:00', '105', 13, NULL),
(72, '2025-01-08', '14:00:00', '105', 13, NULL),
(73, '2025-01-08', '14:15:00', '105', 13, NULL),
(74, '2025-01-08', '14:30:00', '105', 13, NULL),
(75, '2025-01-08', '14:45:00', '105', 13, NULL),
(76, '2025-01-08', '15:00:00', '105', 13, NULL),
(77, '2025-01-08', '15:15:00', '105', 13, NULL),
(78, '2025-01-08', '15:30:00', '105', 13, NULL),
(79, '2025-01-08', '15:45:00', '105', 13, NULL),
(80, '2025-01-08', '16:00:00', '105', 13, NULL),
(81, '2025-01-08', '16:15:00', '105', 13, NULL),
(82, '2025-01-08', '16:30:00', '105', 13, NULL),
(83, '2025-01-08', '16:45:00', '105', 13, NULL),
(84, '2025-01-01', '17:44:58', '105', 12, 2),
(85, '2025-01-01', '00:06:20', '203', 14, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `discount`
--

DROP TABLE IF EXISTS `discount`;
CREATE TABLE IF NOT EXISTS `discount` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Size` int NOT NULL,
  `Date` date NOT NULL,
  `patient_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `discount`
--

INSERT INTO `discount` (`id`, `Size`, `Date`, `patient_id`) VALUES
(1, 56, '2024-12-27', 2),
(4, 5, '2025-01-04', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `doctor`
--

DROP TABLE IF EXISTS `doctor`;
CREATE TABLE IF NOT EXISTS `doctor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `Patronymic` varchar(50) DEFAULT NULL,
  `Age` int DEFAULT NULL,
  `Specialization` varchar(100) NOT NULL,
  `Category` int DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `doctor`
--

INSERT INTO `doctor` (`id`, `Name`, `Surname`, `Patronymic`, `Age`, `Specialization`, `Category`, `Password`, `Username`) VALUES
(11, 'Леонардо', 'Каприо', 'Петрович', 46, 'Эндокринолог', 2, '$2y$10$T/gJTLkdjqe6QYBWIQ6gp.P8TvKDzRiCU8y15GXDVmb8k7psVC1K2', 'doctor5'),
(12, 'Никита', 'Беляев', 'Александрович', 29, 'Терапевт', 1, '$2y$10$wJCph1YpS2QDOhNt3yuYwu6uEOqcfYQ32uE11UUpi1BCJDuFu1HjO', 'doctor6'),
(13, 'Владмир', 'Петров', 'Петрович', 55, 'Хирург', 2, '$2y$10$veIRruVQyh86mR/wvp.qwufslWiAV9wM1v/OVlZcqA9.jAqev9rHi', 'doctor7'),
(14, 'Дуэн', 'Скала', 'Джонсон', 40, 'Психологическая консультация', 3, '$2y$10$Xurw3tFWdyQ4e5wq2sHas.IC9bwcFJxgavdCwM793hZRvAZwFyCZ2', 'doctor8'),
(15, 'Вин', 'Дизель', 'Петрович', 43, 'Гинеколог', 3, '$2y$10$aPxYpGRiEBvo.tHCHI5oieqPTfv3TAQbo0fgpVSIwqBDV/v9ccq8S', 'doctor9');

-- --------------------------------------------------------

--
-- Структура таблицы `medical_card`
--

DROP TABLE IF EXISTS `medical_card`;
CREATE TABLE IF NOT EXISTS `medical_card` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coupon_id` int NOT NULL,
  `Date` date DEFAULT NULL,
  `Diagnosis` varchar(255) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `patient_id` int NOT NULL,
  `payment_made` enum('да','нет') NOT NULL DEFAULT 'нет',
  `Description` varchar(255) DEFAULT NULL,
  `Type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `fk_medical_card_coupon` (`coupon_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `medical_card`
--

INSERT INTO `medical_card` (`id`, `coupon_id`, `Date`, `Diagnosis`, `Amount`, `patient_id`, `payment_made`, `Description`, `Type`) VALUES
(2, 8, '2024-12-27', 'Не пришел', 187.40, 2, 'да', '-', '-'),
(3, 9, '2024-12-17', 'Острое воспаление хитрости', 4065.60, 2, 'да', 'Жаловался сильно на кашель и хрипоту', 'Обследовал'),
(4, 12, '2024-12-23', 'Перелом руки', 1000.00, 4, 'нет', 'Шел по пешеходному переходу оступился упал очнулся гипс', 'Псих консул'),
(6, 10, '2025-01-02', 'ОРВИ', 1000.00, 4, 'нет', 'Принимать мед с чаем', 'обследование'),
(5, 84, '2025-01-01', 'Растяжение плеча', 96.80, 2, 'нет', 'Делал жим лежа и хрустнуло плечо, советую зафиксировать сустав', 'Осмотр по требованию'),
(7, 7, '2024-12-30', 'Растяжение плеча', 660.00, 2, 'нет', 'Занимался и резко заболело плечо, наложили повязку', 'Перевязка');

-- --------------------------------------------------------

--
-- Структура таблицы `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `Patronymic` varchar(50) DEFAULT NULL,
  `Age` int DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `patient`
--

INSERT INTO `patient` (`id`, `Name`, `Surname`, `Patronymic`, `Age`, `PhoneNumber`, `Address`, `Password`, `Username`) VALUES
(2, 'Владислав', 'Лапин', 'Сергеевич', 20, '89037303024', 'Москва', '$2y$10$Sum08nUlWiGutjetYEyMd.h3N1waUkVlVDfhXwtr.SypGQpFsCc3m', 'vlados12'),
(3, 'Илья', 'Сулименко', 'Дмитриевич', 20, '89589877236', 'Москва, Жулебино', '$2y$10$nwlyixI/xId.A6Qlx8xzDOD6IAn/iM0RlA/2NHGF9fHpB4kVXU3k2', 'sulimenko'),
(4, 'Владимир', 'Мачулин', 'Владимирович', 20, '89652658798', 'Москва Братиславская Верхние поля', '$2y$10$/fFdSh41h4xnY/b4XYOiIe5vhSKOdNbDA/kBold3N8jUBeEjJ8y4G', 'vovka52'),
(5, 'Владислав', 'Рпагрпа', 'вьмоутмо', 35, '4884848448', 'мгомгиркг', '$2y$10$/Bz.x3dNNkvpFaFhoZELyeKQNJgU1a1REZgHEbLWCiWYlkcqkETey', 'logvlad'),
(6, 'Усейн', 'Болт', 'Усович', 35, '89037303025', 'Москва', '$2y$10$zdoVSn0BL.kW0DFCcSBJ7.ycu6Y.zWwOiT07l4MQWSa2Xg7KNZOey', 'log'),
(9, 'Петр', 'Лапин', 'Горячевич', 25, '89652241231', 'Москва', '$2y$10$YCs4Va/IGRyKqlt6xtQOtOlZU4vnyfyGjHTkQjYVwW.mK.N.Aab9m', 'petr12'),
(10, 'Иван', 'Лапин', 'Иванович', 34, '89652134565', 'Москва Лавочкина д12 кв5', '$2y$10$9GMTostbQcHLAJmru81Wxem1Ad9Hlx/lqP5wOkb.wueRcttRzJNBm', 'ivanushka1'),
(14, 'Игорь', 'Петров', 'Иванович', 37, '86523124567', 'Москва, Лавочкина 32 кв 7', '$2y$10$LUw4P9ELy15FyPV8CDX3Pe880Os5SwiY5wAPZH.DORGT29tuXYtza', 'petrov32');

-- --------------------------------------------------------

--
-- Структура таблицы `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Amount` decimal(10,2) DEFAULT NULL,
  `medical_card_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `medical_card_id` (`medical_card_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `payment`
--

INSERT INTO `payment` (`id`, `Amount`, `medical_card_id`) VALUES
(25, 187.40, 2),
(24, 660.00, 7),
(23, 1000.00, 6),
(22, 1000.00, 4);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
