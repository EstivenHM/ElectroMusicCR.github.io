-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 07:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `electromusiccrdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(180) NOT NULL,
  `ticket_url` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `image_path`, `event_date`, `event_time`, `location`, `ticket_url`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'MARTIN GARRIX EN COSTA RICA', 'Cada vez  mas cerca para el concierto mas esperado por los fans de la musica electronica\r\n\r\nmartin garrix por primera vez en costa rica', '/public/images/trivia/cf7630d28a14fe6675e537d4c4dbe1f4.jpg', '2026-11-27', '14:00:00', 'Anfiteatro, Parque Viva', 'https://smarticket.net/evento.php?id=204&sfnsn=wa', 'published', 1, '2026-09-17 04:14:39', '2026-09-17 04:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `description`, `image_path`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'B Jones afronta una nueva etapa de recuperación tras superar el cáncer', 'Tras varios ciclos de quimioterapia, B Jones pudo anunciar una de las noticias más esperadas: el cáncer había desaparecido. Poco después volvió durante unas horas a uno de los lugares más importantes de su carrera, el Mainstage de Tomorrowland, en una actuación especialmente emotiva después de meses de hospital. Pero aquel regreso no significaba que su recuperación hubiese terminado.\r\n\r\nDespués del festival, Bea tuvo que volver al hospital para continuar con su tratamiento y someterse a un trasplante de médula. Actualmente sigue recuperándose y todavía tiene por delante una operación de cadera, meses de fisioterapia y rehabilitación antes de poder volver a caminar y trabajar con normalidad.\r\n\r\nDespués de un año especialmente difícil, la noticia más importante es que B Jones está libre de cáncer. hora queda algo mucho menos inmediato: recuperar poco a poco su salud, su movilidad y su vida cotidiana. Quienes quieran ayudar pueden hacerlo mediante una aportación a la campaña o simplemente compartiéndola.', '/public/images/trivia/5c331c35c9a6958f9eff659702321b39.jpg', 'published', 1, '2026-09-17 03:56:04', '2026-09-17 04:28:25');

-- --------------------------------------------------------

--
-- Table structure for table `publics`
--

CREATE TABLE `publics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `descriptions` text NOT NULL,
  `fileroute` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `version` varchar(20) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES
('001_initial_schema', '2026-09-17 21:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `trivia`
--

CREATE TABLE `trivia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `image_path` varchar(500) DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `trivia`
--

INSERT INTO `trivia` (`id`, `title`, `description`, `image_path`, `scheduled_date`, `starts_at`, `ends_at`, `status`, `created_by`, `created_at`) VALUES
(15, 'Que tanto conoces a Martin Garrix?', '¡Demuestre qué tanto sabe, sume puntos y compita a ver quién es el más carga con Martin Garrix previo a su primer concierto en Costa Rica este 27 de noviembre! Recordá que hay una nueva trivia todos los días.!', '/public/images/trivia/c23dbd48d3408a006e8ac54935faad5a.jpg', '2026-09-23', '2026-09-23 00:00:00', '2026-09-24 00:00:00', 'active', 1, '2026-09-23 16:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `trivia_attempts`
--

CREATE TABLE `trivia_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submission_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visitor_token_hash` char(64) DEFAULT NULL,
  `selected_option_id` bigint(20) UNSIGNED NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `points_awarded` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_attempts`
--

INSERT INTO `trivia_attempts` (`id`, `submission_id`, `question_id`, `user_id`, `visitor_token_hash`, `selected_option_id`, `is_correct`, `points_awarded`, `created_at`) VALUES
(7, 7, 23, NULL, NULL, 89, 1, 1, '2026-09-23 16:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `trivia_options`
--

CREATE TABLE `trivia_options` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_options`
--

INSERT INTO `trivia_options` (`id`, `question_id`, `option_text`, `is_correct`, `position`) VALUES
(89, 23, 'Martijn Gerard Garritsen', 1, 1),
(90, 23, 'Martin Garrett Smith', 0, 2),
(91, 23, 'Maarten van Garrix', 0, 3),
(92, 23, 'Martin Gerrit Jansen', 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `trivia_players`
--

CREATE TABLE `trivia_players` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nickname` varchar(40) NOT NULL,
  `nickname_normalized` varchar(40) NOT NULL,
  `recovery_code_hash` char(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_players`
--

INSERT INTO `trivia_players` (`id`, `nickname`, `nickname_normalized`, `recovery_code_hash`, `is_active`, `created_at`, `last_seen_at`) VALUES
(1, 'Estiven', 'estiven', '92c4f4e3367a33155747eecc18d6fbe8265f011e93946fd08819b6e683bd288c', 1, '2026-09-17 02:07:53', NULL),
(2, 'Estiven3', 'estiven3', 'ecf1dd3f5149a0c57558f5d0a6e7b4e2197077baab8c3f5f0bd70fafc6cdd309', 1, '2026-09-17 04:41:24', NULL),
(3, 'Estiven41', 'estiven41', '204172ecbf28cc4b170389c699bad23814c3d2b36e611df2a6436c78d29d359a', 1, '2026-09-23 03:39:43', NULL),
(4, 'Ander', 'ander', 'de785f5d56a54c0f20f1c58f115c9865dcd4ef6871501db87c025188a7c6b0b9', 1, '2026-09-23 16:37:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trivia_questions`
--

CREATE TABLE `trivia_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trivia_id` bigint(20) UNSIGNED NOT NULL,
  `question_text` varchar(500) NOT NULL,
  `explanation` text DEFAULT NULL,
  `question_date` date NOT NULL,
  `points` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `trivia_questions`
--

INSERT INTO `trivia_questions` (`id`, `trivia_id`, `question_text`, `explanation`, `question_date`, `points`, `position`, `created_at`) VALUES
(23, 15, '¿Cuál es el nombre real de Martin Garrix?', 'Nació en Amstelveen, Países Bajos. Eligió \"Martin Garrix\" como su nombre artístico porque era más fácil de pronunciar e identificar a nivel internacional que su nombre nativo en neerlandés.', '2026-09-23', 1, 1, '2026-09-23 16:36:10');

-- --------------------------------------------------------

--
-- Stand-in structure for view `trivia_ranking`
-- (See below for the actual view)
--
CREATE TABLE `trivia_ranking` (
`position` bigint(21)
,`nickname` varchar(40)
,`points` decimal(27,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `trivia_settings`
--

CREATE TABLE `trivia_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_settings`
--

INSERT INTO `trivia_settings` (`id`, `title`, `description`, `image_path`, `updated_by`, `updated_at`) VALUES
(1, 'Que tanto conoces a Marin Garrix?', '¡Demuestre qué tanto sabe, sume puntos y compita a ver quién es el más carga con Martin Garrix previo a su primer concierto en Costa Rica este 27 de noviembre! Recordá que hay una nueva trivia todos los días.!', '/public/images/trivia/9815bb7e81339faeec0bbd67e8e401a8.jpg', 1, '2026-09-23 16:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `trivia_submissions`
--

CREATE TABLE `trivia_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trivia_id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `attempt_number` tinyint(3) UNSIGNED NOT NULL,
  `points_total` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `trivia_submissions`
--

INSERT INTO `trivia_submissions` (`id`, `trivia_id`, `player_id`, `attempt_number`, `points_total`, `status`, `created_at`) VALUES
(7, 15, 4, 1, 1, 'submitted', '2026-09-23 16:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `display_name` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` tinyint(3) UNSIGNED NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `display_name`, `password_hash`, `role`, `last_update`) VALUES
(1, 'EstivenHM', 'EstivenHM', '$2y$10$4SO3CsD7b04pL7XpzT0MJ.amH5wNsLd9gOz.q/PZFhdacwanY4UAy', 1, '2026-09-17 02:04:43');

-- --------------------------------------------------------

--
-- Structure for view `trivia_ranking`
--
DROP TABLE IF EXISTS `trivia_ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `trivia_ranking`  AS SELECT row_number() over ( order by `totals`.`points` desc,`totals`.`nickname`) AS `position`, `totals`.`nickname` AS `nickname`, `totals`.`points` AS `points` FROM (select `player`.`nickname` AS `nickname`,coalesce(sum(case when `submission`.`status` = 'submitted' then `submission`.`points_total` else 0 end),0) AS `points` from (`trivia_players` `player` left join `trivia_submissions` `submission` on(`submission`.`player_id` = `player`.`id`)) where `player`.`is_active` = 1 group by `player`.`id`,`player`.`nickname`) AS `totals` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_status_date` (`status`,`event_date`),
  ADD KEY `fk_events_created_by` (`created_by`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_status_created` (`status`,`created_at`),
  ADD KEY `fk_news_created_by` (`created_by`);

--
-- Indexes for table `publics`
--
ALTER TABLE `publics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_publics_status_updated` (`status`,`updated_at`),
  ADD KEY `fk_publics_created_by` (`created_by`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `trivia`
--
ALTER TABLE `trivia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_trivia_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_trivia_status_dates` (`status`,`starts_at`,`ends_at`),
  ADD KEY `fk_trivia_created_by` (`created_by`),
  ADD KEY `idx_trivia_publication` (`status`,`scheduled_date`);

--
-- Indexes for table `trivia_attempts`
--
ALTER TABLE `trivia_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempts_ranking` (`user_id`,`points_awarded`),
  ADD KEY `fk_attempts_question` (`question_id`),
  ADD KEY `fk_attempts_option` (`selected_option_id`),
  ADD KEY `idx_attempts_submission` (`submission_id`);

--
-- Indexes for table `trivia_options`
--
ALTER TABLE `trivia_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_options_question` (`question_id`);

--
-- Indexes for table `trivia_players`
--
ALTER TABLE `trivia_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_trivia_players_nickname` (`nickname_normalized`),
  ADD KEY `idx_trivia_players_active` (`is_active`,`nickname_normalized`);

--
-- Indexes for table `trivia_questions`
--
ALTER TABLE `trivia_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_trivia_question_position` (`trivia_id`,`position`),
  ADD KEY `idx_questions_date` (`question_date`);

--
-- Indexes for table `trivia_settings`
--
ALTER TABLE `trivia_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trivia_settings_updated_by` (`updated_by`);

--
-- Indexes for table `trivia_submissions`
--
ALTER TABLE `trivia_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_submission_player_trivia` (`player_id`,`trivia_id`),
  ADD KEY `idx_submissions_ranking` (`player_id`,`status`,`points_total`),
  ADD KEY `fk_submissions_trivia` (`trivia_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publics`
--
ALTER TABLE `publics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trivia`
--
ALTER TABLE `trivia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trivia_attempts`
--
ALTER TABLE `trivia_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trivia_options`
--
ALTER TABLE `trivia_options`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `trivia_players`
--
ALTER TABLE `trivia_players`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trivia_questions`
--
ALTER TABLE `trivia_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trivia_submissions`
--
ALTER TABLE `trivia_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_news_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `publics`
--
ALTER TABLE `publics`
  ADD CONSTRAINT `fk_publics_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `trivia`
--
ALTER TABLE `trivia`
  ADD CONSTRAINT `fk_trivia_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `trivia_attempts`
--
ALTER TABLE `trivia_attempts`
  ADD CONSTRAINT `fk_attempts_option` FOREIGN KEY (`selected_option_id`) REFERENCES `trivia_options` (`id`),
  ADD CONSTRAINT `fk_attempts_question` FOREIGN KEY (`question_id`) REFERENCES `trivia_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attempts_submission` FOREIGN KEY (`submission_id`) REFERENCES `trivia_submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `trivia_options`
--
ALTER TABLE `trivia_options`
  ADD CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `trivia_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trivia_questions`
--
ALTER TABLE `trivia_questions`
  ADD CONSTRAINT `fk_questions_trivia` FOREIGN KEY (`trivia_id`) REFERENCES `trivia` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trivia_settings`
--
ALTER TABLE `trivia_settings`
  ADD CONSTRAINT `fk_trivia_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `trivia_submissions`
--
ALTER TABLE `trivia_submissions`
  ADD CONSTRAINT `fk_submissions_player` FOREIGN KEY (`player_id`) REFERENCES `trivia_players` (`id`),
  ADD CONSTRAINT `fk_submissions_trivia` FOREIGN KEY (`trivia_id`) REFERENCES `trivia` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
