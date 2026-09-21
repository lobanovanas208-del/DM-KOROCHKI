-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 21 2026 г., 13:21
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `korochki`
--

-- --------------------------------------------------------

--
-- Структура таблицы `kursi`
--

CREATE TABLE `kursi` (
  `id_kursi` int(1) NOT NULL,
  `kurs` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `kursi`
--

INSERT INTO `kursi` (`id_kursi`, `kurs`) VALUES
(1, 'Основы алгоритмизации и программирования'),
(2, 'Основы веб-дизайна'),
(3, 'Основы проектирования баз данных');

-- --------------------------------------------------------

--
-- Структура таблицы `oplati`
--

CREATE TABLE `oplati` (
  `id_oplati` int(1) NOT NULL,
  `oplata` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `oplati`
--

INSERT INTO `oplati` (`id_oplati`, `oplata`) VALUES
(1, 'наличными'),
(2, 'переводом по номеру телефона');

-- --------------------------------------------------------

--
-- Структура таблицы `otziv`
--

CREATE TABLE `otziv` (
  `id_otziv` int(1) NOT NULL,
  `coment` varchar(255) DEFAULT NULL,
  `zvezda` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `otziv`
--

INSERT INTO `otziv` (`id_otziv`, `coment`, `zvezda`) VALUES
(1, 'комментарий', 'количество звезд');

-- --------------------------------------------------------

--
-- Структура таблицы `polzovateli`
--

CREATE TABLE `polzovateli` (
  `id_polzovateli` int(1) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fio` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role_id` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `polzovateli`
--

INSERT INTO `polzovateli` (`id_polzovateli`, `login`, `password`, `fio`, `phone`, `email`, `role_id`) VALUES
(1, 'Admin', 'KorokNET', 'Userov User Userovich', '8(901)152-83-16', 'example@com', 2),
(2, 'User', '12345687', 'Testov Test Testovich', '8(963)183-83-12', 'test@ru', 1),
(3, 'Testik', '12345678', 'lobanova anastasia vitalevna', '8(952)244-52-42', 'ban@ru', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `roli`
--

CREATE TABLE `roli` (
  `id_roli` int(1) NOT NULL,
  `rol` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `roli`
--

INSERT INTO `roli` (`id_roli`, `rol`) VALUES
(1, 'пользователь'),
(2, 'администратор');

-- --------------------------------------------------------

--
-- Структура таблицы `statusi`
--

CREATE TABLE `statusi` (
  `id_statusi` int(1) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `statusi`
--

INSERT INTO `statusi` (`id_statusi`, `status`) VALUES
(1, 'Новая'),
(2, 'Идет обучение'),
(3, 'Обучение завершено');

-- --------------------------------------------------------

--
-- Структура таблицы `zayvka`
--

CREATE TABLE `zayvka` (
  `id_request` int(1) NOT NULL,
  `course_id` int(1) DEFAULT NULL,
  `data_nach` varchar(255) NOT NULL,
  `payment_id` int(1) DEFAULT NULL,
  `status_id` int(1) DEFAULT NULL,
  `user_id` int(1) DEFAULT NULL,
  `review_id` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `kursi`
--
ALTER TABLE `kursi`
  ADD PRIMARY KEY (`id_kursi`);

--
-- Индексы таблицы `oplati`
--
ALTER TABLE `oplati`
  ADD PRIMARY KEY (`id_oplati`);

--
-- Индексы таблицы `otziv`
--
ALTER TABLE `otziv`
  ADD PRIMARY KEY (`id_otziv`);

--
-- Индексы таблицы `polzovateli`
--
ALTER TABLE `polzovateli`
  ADD PRIMARY KEY (`id_polzovateli`),
  ADD KEY `role_id` (`role_id`);

--
-- Индексы таблицы `roli`
--
ALTER TABLE `roli`
  ADD PRIMARY KEY (`id_roli`);

--
-- Индексы таблицы `statusi`
--
ALTER TABLE `statusi`
  ADD PRIMARY KEY (`id_statusi`);

--
-- Индексы таблицы `zayvka`
--
ALTER TABLE `zayvka`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_status` (`status_id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_payment` (`payment_id`),
  ADD KEY `fk_review` (`review_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `otziv`
--
ALTER TABLE `otziv`
  MODIFY `id_otziv` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `statusi`
--
ALTER TABLE `statusi`
  MODIFY `id_statusi` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `polzovateli`
--
ALTER TABLE `polzovateli`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `roli` (`id_roli`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `polzovateli_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roli` (`id_roli`);

--
-- Ограничения внешнего ключа таблицы `zayvka`
--
ALTER TABLE `zayvka`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `kursi` (`id_kursi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment` FOREIGN KEY (`payment_id`) REFERENCES `oplati` (`id_oplati`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review` FOREIGN KEY (`review_id`) REFERENCES `otziv` (`id_otziv`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status_id`) REFERENCES `statusi` (`id_statusi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `polzovateli` (`id_polzovateli`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
