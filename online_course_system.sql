-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Mar 2025 pada 17.04
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_course_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `instructor_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `education_level` enum('SD','SMP','SMA') NOT NULL,
  `class_level` int(11) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `instructor_id`, `category`, `education_level`, `class_level`, `creation_date`) VALUES
(1, 'Physics: Motion and Energy', 'Learn the basics of physics including motion, energy, and their applications', 2, 'Science', 'SMA', 2, '2025-03-13 06:41:48'),
(2, 'Algebra Fundamentals', 'Master the basics of algebra including equations, functions, and more', 3, 'Mathematics', 'SMP', 3, '2025-03-13 06:41:48'),
(3, 'English Grammar Basics', 'Learn the fundamentals of English grammar', 2, 'Language', 'SD', 6, '2025-03-13 06:41:48'),
(4, 'Biology: Cell Structure', 'Explore the structure and function of cells', 3, 'Science', 'SMA', 1, '2025-03-13 06:41:48'),
(5, 'Indonesian History', 'Learn about the rich history of Indonesia', 2, 'Social Studies', 'SMA', 3, '2025-03-13 06:41:48'),
(6, 'Creative Writing', 'Develop skills in creative writing and storytelling', 3, 'Language', 'SMP', 2, '2025-03-13 06:41:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `course_materials`
--

CREATE TABLE `course_materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `order_number` int(11) NOT NULL,
  `duration_minutes` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `course_materials`
--

INSERT INTO `course_materials` (`material_id`, `course_id`, `title`, `content`, `order_number`, `duration_minutes`) VALUES
(1, 1, 'Introduction to Motion', 'Understanding the basic concepts of motion', 1, 45),
(2, 1, 'Newtons Laws', 'Exploring Newtons three laws of motion', 2, 60),
(3, 1, 'Energy Principles', 'Understanding potential and kinetic energy', 3, 45),
(4, 2, 'Basic Equations', 'Learn how to solve basic algebraic equations', 1, 40),
(5, 2, 'Functions and Graphs', 'Understanding functions and their graphical representations', 2, 55);

-- --------------------------------------------------------

--
-- Struktur dari tabel `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `registrations`
--

INSERT INTO `registrations` (`registration_id`, `user_id`, `course_id`, `registration_date`, `progress`) VALUES
(1, 2, 1, '2025-03-13 06:42:04', 0),
(2, 2, 3, '2025-03-13 06:42:04', 0),
(3, 3, 2, '2025-03-13 06:42:04', 0),
(4, 3, 4, '2025-03-13 06:42:04', 0),
(5, 5, 1, '2025-03-13 10:24:49', 0),
(6, 5, 3, '2025-03-13 11:33:14', 0),
(7, 5, 2, '2025-03-13 14:35:16', 0),
(8, 5, 4, '2025-03-13 14:37:45', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `registration_date`) VALUES
(1, 'Anggita', 'anggita@gmail.com', '$2y$10$dPwxlyYVw6.urr4N8/bcYuW0C67C2feAF/YlHDpJL.i3QBmYzVelW', '2025-03-13 06:41:39'),
(2, 'Lyvia Valentina', 'valentinalyvia@gmail.com', '$2y$10$dPwxlyYVw6.urr4N8/bcYuW0C67C2feAF/YlHDpJL.i3QBmYzVelW', '2025-03-13 06:41:39'),
(3, 'Gaozhan', 'gaozhan@gmail.com', '$2y$10$dPwxlyYVw6.urr4N8/bcYuW0C67C2feAF/YlHDpJL.i3QBmYzVelW', '2025-03-13 06:41:39'),
(4, 'Lyvia Valentina', 'lyvia@gmail.com', '$2y$10$pz8RBn2hygDxJFydY5mfX.XLrNFuE5idg6buX.mR/XTyPzKpljSh6', '2025-03-13 06:46:47'),
(5, 'Anggita', 'anggitashfr@gmail.com', '$2y$10$OC0E8H1WSIfnB2QSEkNUeO18AUx4Xiy4Wa6MY9I1YfhZL2IvxhnDC', '2025-03-13 06:47:41');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indeks untuk tabel `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indeks untuk tabel `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Ketidakleluasaan untuk tabel `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
