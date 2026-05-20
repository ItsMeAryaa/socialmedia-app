-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 20 Bulan Mei 2026 pada 12.08
-- Versi server: 8.4.3
-- Versi PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_socialmedia`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`) VALUES
('A1', 'admin', 'admin26@gmail.com', '$2y$10$McCl1eDt2dmSQtVnEQSFzeIm/hlgdziZwHX6c28wwJ9nFt3Q1mwYq');

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `parent_id`) VALUES
(7, 4, 2, 'halo Arya, saya Dangker salam kenal ya', '2025-07-08 15:15:02', NULL),
(8, 11, 2, 'wahhh keren banget pemandangannya!', '2025-07-08 15:30:32', NULL),
(9, 10, 2, 'Lagunya asik banget bang', '2025-07-08 15:30:43', NULL),
(10, 9, 2, 'gelo, jj nya keren bang. tutornya dong', '2025-07-08 15:31:03', NULL),
(11, 8, 2, 'apa ada info soal S2 dari anime ini tidak?', '2025-07-08 15:31:48', NULL),
(13, 4, 3, 'hai hai', '2025-07-08 17:36:46', NULL),
(15, 8, 1, 'S2 adanya di 2026 bro', '2025-07-10 15:30:20', 11),
(16, 14, 1, 'asbacjabkhaba', '2025-07-11 14:17:28', NULL),
(17, 25, 2, '???', '2025-07-16 16:45:10', NULL),
(18, 21, 2, 'asdaccdaadda ???', '2025-07-16 16:45:31', NULL),
(19, 25, 2, '?', '2025-07-16 16:53:39', 17),
(20, 33, 2, 'acscaaaacavd?', '2025-07-16 22:25:20', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` int NOT NULL,
  `comment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comment_likes`
--

INSERT INTO `comment_likes` (`id`, `comment_id`, `user_id`, `created_at`) VALUES
(3, 8, 1, '2025-07-08 15:32:42'),
(4, 9, 1, '2025-07-08 15:32:45'),
(5, 10, 1, '2025-07-08 15:32:48'),
(8, 7, 1, '2025-07-08 16:00:23'),
(11, 7, 3, '2025-07-08 17:36:38'),
(12, 13, 1, '2025-07-09 19:49:51'),
(13, 11, 1, '2025-07-10 15:30:04'),
(14, 16, 3, '2025-07-11 22:43:09'),
(18, 19, 2, '2025-07-16 16:54:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `friends`
--

CREATE TABLE `friends` (
  `user_id` int NOT NULL,
  `friend_id` int NOT NULL,
  `since` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `friends`
--

INSERT INTO `friends` (`user_id`, `friend_id`, `since`, `status`) VALUES
(1, 2, '2025-07-21 16:05:12', 'accepted'),
(2, 1, '2025-07-21 16:05:12', 'accepted');

-- --------------------------------------------------------

--
-- Struktur dari tabel `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int NOT NULL,
  `from_user` int DEFAULT NULL,
  `to_user` int DEFAULT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `from_user`, `to_user`, `status`, `created_at`) VALUES
(2, 1, 2, 'accepted', '2025-07-08 17:03:57'),
(3, 3, 1, 'accepted', '2025-07-08 17:36:55'),
(5, 1, 2, 'accepted', '2025-07-13 22:35:34'),
(6, 1, 2, 'accepted', '2025-07-13 22:49:38'),
(7, 2, 1, 'accepted', '2025-07-14 10:02:27'),
(8, 1, 2, 'accepted', '2025-07-14 10:17:43'),
(10, 1, 3, 'accepted', '2025-07-15 16:30:00'),
(11, 1, 3, 'accepted', '2025-07-15 16:38:42'),
(24, 1, 3, 'accepted', '2025-07-15 22:35:22'),
(26, 1, 2, 'accepted', '2025-07-20 18:42:40'),
(29, 1, 2, 'accepted', '2025-07-21 15:25:52'),
(32, 2, 1, 'accepted', '2025-07-21 15:52:01'),
(33, 1, 2, 'accepted', '2025-07-21 16:03:08'),
(34, 1, 2, 'accepted', '2025-07-21 16:05:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(4, 8, 1, '2025-07-08 14:51:38'),
(5, 11, 2, '2025-07-08 15:14:20'),
(6, 10, 2, '2025-07-08 15:14:22'),
(7, 9, 2, '2025-07-08 15:14:25'),
(8, 8, 2, '2025-07-08 15:14:27'),
(9, 4, 2, '2025-07-08 15:14:29'),
(10, 12, 1, '2025-07-08 15:32:33'),
(12, 8, 3, '2025-07-08 17:36:34'),
(13, 13, 1, '2025-07-08 17:42:32'),
(17, 4, 1, '2025-07-10 15:30:01'),
(18, 14, 2, '2025-07-10 17:31:42'),
(19, 14, 1, '2025-07-11 14:17:20'),
(20, 13, 2, '2025-07-11 22:40:23'),
(21, 14, 3, '2025-07-11 22:43:07'),
(22, 13, 3, '2025-07-11 22:43:12'),
(23, 12, 3, '2025-07-11 22:43:14'),
(24, 11, 3, '2025-07-11 22:43:19'),
(25, 10, 3, '2025-07-11 22:43:22'),
(26, 9, 3, '2025-07-11 22:43:24'),
(27, 4, 3, '2025-07-11 22:43:27'),
(31, 28, 1, '2025-07-15 16:49:30'),
(32, 33, 2, '2025-07-16 22:25:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `from_user` int DEFAULT NULL,
  `to_user` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `from_user`, `to_user`, `message`, `created_at`, `is_read`, `is_deleted`) VALUES
(14, 1, 3, '[file]assets/uploads/messages/1752304062_TAHAP 2 [98DE255].mp4', '2025-07-12 15:07:42', 1, 0),
(16, 1, 3, '[file]assets/uploads/messages/1752304149_Maroon_5_Sugar_480p_MUX_CBR-128k.mp3', '2025-07-12 15:09:09', 1, 1),
(37, 1, 2, 'hai', '2025-07-20 19:09:21', 1, 0),
(38, 1, 2, '[file]assets/uploads/messages/1775803020_450084909_1210768649924295_527618978163127093_n.jpg', '2026-04-10 14:37:00', 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `content` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `content`, `is_read`, `created_at`) VALUES
(1, 1, 'Dangker Dida menolak permintaan pertemananmu.', 1, '2025-07-08 16:37:05'),
(2, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-08 17:03:57'),
(3, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-08 17:04:36'),
(4, 1, 'Arya Dida mengirimkan permintaan pertemanan.', 1, '2025-07-08 17:36:55'),
(5, 3, 'Arya menerima permintaan pertemanan dari kamu!', 1, '2025-07-08 17:38:27'),
(6, 2, 'Arya Dida mengirimkan permintaan pertemanan.', 1, '2025-07-11 22:44:28'),
(7, 3, 'Dangker Dida menolak permintaan pertemanan dari kamu.', 1, '2025-07-11 22:45:48'),
(8, 2, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-13 22:33:26'),
(9, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-13 22:35:34'),
(10, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-13 22:48:12'),
(11, 1, 'Dangker Dida telah menghapus kamu dari daftar temannya.', 1, '2025-07-13 22:48:52'),
(12, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-13 22:49:38'),
(13, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-13 22:50:30'),
(14, 1, 'Dangker Dida telah menghapus kamu dari daftar temannya.', 1, '2025-07-14 10:02:15'),
(15, 1, 'Dangker Dida mengirimkan permintaan pertemanan.', 1, '2025-07-14 10:02:27'),
(16, 2, 'Arya menerima permintaan pertemanan dari kamu!', 1, '2025-07-14 10:02:55'),
(17, 1, 'Dangker Dida telah menghapus kamu dari daftar temannya.', 1, '2025-07-14 10:17:26'),
(18, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-14 10:17:43'),
(19, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-14 10:17:53'),
(21, 3, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-15 13:01:43'),
(22, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 16:30:01'),
(23, 1, 'Arya Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-15 16:31:48'),
(24, 3, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-15 16:37:16'),
(25, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 16:38:42'),
(26, 1, 'Arya Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-15 16:38:58'),
(27, 3, 'Dangker Dida mengirimkan permintaan pertemanan.', 1, '2025-07-15 17:36:16'),
(28, 3, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-15 20:54:26'),
(29, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 20:58:40'),
(30, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 20:59:32'),
(31, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 21:00:35'),
(32, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 21:01:15'),
(33, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 21:11:05'),
(34, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:05:23'),
(35, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:09:53'),
(36, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:21:16'),
(37, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:24:05'),
(38, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:25:18'),
(39, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:25:32'),
(40, 3, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-15 22:35:22'),
(41, 1, 'Arya Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-15 22:36:56'),
(43, 2, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-20 18:42:34'),
(44, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-20 18:42:40'),
(45, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-20 18:47:19'),
(46, 3, 'Arya telah menghapus kamu dari daftar temannya.', 0, '2025-07-21 14:21:10'),
(47, 3, 'Arya mengirimkan permintaan pertemanan.', 0, '2025-07-21 14:21:19'),
(48, 2, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-21 14:31:34'),
(49, 3, 'Arya mengirimkan permintaan pertemanan.', 0, '2025-07-21 15:20:48'),
(50, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-21 15:25:52'),
(51, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-21 15:26:31'),
(52, 2, 'Arya telah menghapus kamu dari daftar temannya.', 1, '2025-07-21 15:28:17'),
(53, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-21 15:28:21'),
(54, 1, 'Dangker Dida menolak permintaan pertemanan dari kamu.', 1, '2025-07-21 15:46:51'),
(55, 2, 'Arya menerima permintaan pertemanan dari kamu!', 1, '2025-07-21 15:55:20'),
(56, 1, 'Dangker Dida telah menghapus kamu dari daftar temannya.', 1, '2025-07-21 15:57:42'),
(57, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-21 16:03:29'),
(58, 2, 'Arya mengirimkan permintaan pertemanan.', 1, '2025-07-21 16:05:01'),
(59, 1, 'Dangker Dida menerima permintaan pertemanan dari kamu!', 1, '2025-07-21 16:05:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(4, 1, 'Hello World!', '2025-07-08 13:13:23', '2025-07-11 15:18:12'),
(8, 1, 'Black Clover', '2025-07-08 14:51:34', '2025-07-08 14:51:34'),
(9, 1, 'Main COC dulu nggak sehhh!!!!!!', '2025-07-08 14:52:56', '2025-07-08 14:52:56'),
(10, 1, 'Lagu korea nih boss!!!', '2025-07-08 14:54:06', '2025-07-08 14:54:06'),
(11, 1, 'SD GMIT 03 Kalabahi', '2025-07-08 15:13:05', '2025-07-08 15:13:05'),
(12, 2, 'hai saya Dangker, salam kenal semuanya!! ayo kita berteman', '2025-07-08 15:17:18', '2025-07-08 15:30:08'),
(13, 1, 'Tedis sebelum corona ?', '2025-07-08 17:42:03', '2025-07-14 16:05:20'),
(14, 3, 'nca hbec ebwih', '2025-07-10 17:30:41', '2025-07-10 17:30:41'),
(21, 1, 'asad', '2025-07-14 22:04:44', '2025-07-14 22:04:44'),
(24, 1, 'acnjscakj', '2025-07-14 22:09:55', '2025-07-14 22:09:55'),
(25, 1, 'sadsaf', '2025-07-14 22:11:21', '2025-07-14 22:11:21'),
(28, 3, 'fxgftyc ggguh', '2025-07-15 16:40:13', '2025-07-15 16:40:13'),
(30, 2, '? ? ? ? ? ? ? ? ? ? ? ? ? ? ?', '2025-07-16 16:04:46', '2025-07-16 16:04:46'),
(31, 2, '???', '2025-07-16 16:10:56', '2025-07-16 16:10:56'),
(32, 2, '????', '2025-07-16 16:15:19', '2025-07-16 16:15:19'),
(33, 1, '?csdcsdvs', '2025-07-16 22:19:04', '2025-07-16 22:19:04'),
(34, 1, 'Kimetsu No Yaiba S5?', '2025-07-20 18:37:11', '2025-07-20 18:37:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `post_files`
--

CREATE TABLE `post_files` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_type` enum('image','video','audio') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `post_files`
--

INSERT INTO `post_files` (`id`, `post_id`, `file_path`, `file_type`) VALUES
(2, 8, 'assets/uploads/post_686cbff61b6fd.jpg', 'image'),
(3, 9, 'assets/uploads/post_686cc048e093d.mp4', 'video'),
(4, 10, 'assets/uploads/post_686cc08ece376.mp3', 'audio'),
(5, 11, 'assets/uploads/post_686cc5014c063.jpg', 'image'),
(6, 13, 'assets/uploads/post_686ce7eb4b3e5.jpg', 'image'),
(7, 14, 'assets/uploads/post_686f8842f3372.mp4', 'video'),
(9, 21, 'assets/uploads/post_68750e7c1962f.jpg', 'image'),
(10, 24, 'assets/uploads/post_68750fb359087.mp4', 'video'),
(11, 25, 'assets/uploads/post_6875100a4234b.jpg', 'image'),
(13, 33, 'assets/uploads/post_6877b4d89d03e.jpg', 'image'),
(14, 34, 'assets/uploads/post_687cc6d7472d2.jpg', 'image');

-- --------------------------------------------------------

--
-- Struktur dari tabel `post_shares`
--

CREATE TABLE `post_shares` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `share_type` enum('copy','whatsapp','facebook','twitter') COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `post_shares`
--

INSERT INTO `post_shares` (`id`, `post_id`, `share_type`, `share_time`) VALUES
(2, 24, 'whatsapp', '2025-07-15 21:41:50'),
(4, 21, 'copy', '2025-07-15 22:04:12'),
(5, 33, 'copy', '2025-07-16 22:25:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profiles`
--

CREATE TABLE `profiles` (
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cover_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profiles`
--

INSERT INTO `profiles` (`user_id`, `name`, `last_name`, `bio`, `photo`, `cover_photo`, `updated_at`) VALUES
(1, 'Arya', '', 'Hello World!\r\n', 'assets/uploads/profile_687522bd52a0d.jpg', 'assets/uploads/cover/cover_687524cea7497.jpg', '2025-07-15 00:06:22'),
(2, 'Dangker Dida', '', 'Dangker\r\n', 'assets/uploads/profile/profile_6873957a2d46c.jpg', 'assets/uploads/cover/cover_6875254203a96.jpg', '2025-07-15 00:03:45'),
(3, 'Arya Dida', '', NULL, NULL, NULL, '2025-07-08 17:35:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stories`
--

CREATE TABLE `stories` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('text','image','video','music') COLLATE utf8mb4_general_ci NOT NULL,
  `content` text COLLATE utf8mb4_general_ci,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `type`, `content`, `file_path`, `created_at`, `expires_at`) VALUES
(29, 1, 'image', 'jnj gvgj ibhb', 'assets/uploads/stories/story_69d89aaac00c8.jpg', '2026-04-10 14:37:30', '2026-04-11 06:37:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `story_views`
--

CREATE TABLE `story_views` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `story_id` int NOT NULL,
  `viewed_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `story_views`
--

INSERT INTO `story_views` (`id`, `user_id`, `story_id`, `viewed_at`) VALUES
(1, 1, 22, '2025-07-14 22:32:59'),
(5, 2, 22, '2025-07-14 22:38:57'),
(6, 2, 23, '2025-07-14 22:40:52'),
(7, 1, 23, '2025-07-14 22:41:01'),
(8, 1, 24, '2025-07-14 22:41:51'),
(13, 3, 22, '2025-07-14 23:03:32'),
(14, 1, 25, '2025-07-15 00:10:10'),
(15, 2, 25, '2025-07-15 00:10:26'),
(16, 1, 26, '2025-07-15 15:15:13'),
(17, 3, 25, '2025-07-15 16:39:18'),
(18, 3, 26, '2025-07-15 16:39:28'),
(19, 2, 26, '2025-07-15 17:35:52'),
(20, 1, 27, '2025-07-16 22:24:23'),
(21, 2, 27, '2025-07-16 22:24:47'),
(22, 1, 28, '2025-07-20 18:29:31'),
(23, 1, 29, '2026-04-10 14:37:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_active` datetime DEFAULT NULL,
  `reset_token` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `security_question` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `security_answer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `last_active`, `reset_token`, `reset_token_expiry`, `security_question`, `security_answer`) VALUES
(1, 'arya@gmail.com', '$2y$10$6FKfFWS9eNs98DYxS23mueCwomLruJRKhwl9coRPfXIFPTjz2Z8hq', '2025-07-08 12:11:59', '2026-04-10 14:47:31', NULL, NULL, 'Dimana kamu pertama kali liburan dengan keluarga?', 'Bali'),
(2, 'dangkerdida@gmail.com', '$2y$10$9P0ScB.EkpGrQ1Dd3F/6Nu663DumOZi5phz0pSaT3sTR7kQxLMlyW', '2025-07-08 15:14:13', '2025-07-21 17:00:56', '125399', '2025-07-21 08:41:56', 'Dimana kamu pertama kali liburan dengan keluarga?', 'Alor'),
(3, 'ariyantodida@gmail.com', '$2y$10$tUkganGqfucXv6q9O9yuVevfeCn5ZGKcyXBzwUVTc08IkpkvytMIO', '2025-07-08 17:35:00', '2025-07-16 17:37:01', NULL, NULL, 'Dimana kamu pertama kali liburan dengan keluarga?', 'Semau');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`comment_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indeks untuk tabel `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_user` (`from_user`),
  ADD KEY `to_user` (`to_user`);

--
-- Indeks untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_user` (`from_user`),
  ADD KEY `to_user` (`to_user`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `post_files`
--
ALTER TABLE `post_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indeks untuk tabel `post_shares`
--
ALTER TABLE `post_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post_id` (`post_id`);

--
-- Indeks untuk tabel `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indeks untuk tabel `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `story_views`
--
ALTER TABLE `story_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_view` (`user_id`,`story_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `post_files`
--
ALTER TABLE `post_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `post_shares`
--
ALTER TABLE `post_shares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `story_views`
--
ALTER TABLE `story_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `post_files`
--
ALTER TABLE `post_files`
  ADD CONSTRAINT `post_files_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `post_shares`
--
ALTER TABLE `post_shares`
  ADD CONSTRAINT `fk_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
