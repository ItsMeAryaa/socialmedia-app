/*
SQLyog - Free MySQL GUI v5.0
Host - 5.5.5-10.4.32-MariaDB : Database - db_socialmedia
*********************************************************************
Server version : 5.5.5-10.4.32-MariaDB
*/


create database if not exists `db_socialmedia`;

USE `db_socialmedia`;

/*Table structure for table `admins` */

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` char(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `admins` */

insert into `admins` values 
('A1','admin','admin26@gmail.com','$2y$10$McCl1eDt2dmSQtVnEQSFzeIm/hlgdziZwHX6c28wwJ9nFt3Q1mwYq');

/*Table structure for table `comment_likes` */

DROP TABLE IF EXISTS `comment_likes`;

CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`comment_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `comment_likes` */

insert into `comment_likes` values 
(3,8,1,'2025-07-08 15:32:42'),
(4,9,1,'2025-07-08 15:32:45'),
(5,10,1,'2025-07-08 15:32:48'),
(8,7,1,'2025-07-08 16:00:23'),
(11,7,3,'2025-07-08 17:36:38'),
(12,13,1,'2025-07-09 19:49:51'),
(13,11,1,'2025-07-10 15:30:04'),
(14,16,3,'2025-07-11 22:43:09'),
(18,19,2,'2025-07-16 16:54:27');

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `comments` */

insert into `comments` values 
(7,4,2,'halo Arya, saya Dangker salam kenal ya','2025-07-08 15:15:02',NULL),
(8,11,2,'wahhh keren banget pemandangannya!','2025-07-08 15:30:32',NULL),
(9,10,2,'Lagunya asik banget bang','2025-07-08 15:30:43',NULL),
(10,9,2,'gelo, jj nya keren bang. tutornya dong','2025-07-08 15:31:03',NULL),
(11,8,2,'apa ada info soal S2 dari anime ini tidak?','2025-07-08 15:31:48',NULL),
(13,4,3,'hai hai','2025-07-08 17:36:46',NULL),
(15,8,1,'S2 adanya di 2026 bro','2025-07-10 15:30:20',11),
(16,14,1,'asbacjabkhaba','2025-07-11 14:17:28',NULL),
(17,25,2,'???','2025-07-16 16:45:10',NULL),
(18,21,2,'asdaccdaadda ???','2025-07-16 16:45:31',NULL),
(19,25,2,'?','2025-07-16 16:53:39',17),
(20,33,2,'acscaaaacavd?','2025-07-16 22:25:20',NULL);

/*Table structure for table `friend_requests` */

DROP TABLE IF EXISTS `friend_requests`;

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user` int(11) DEFAULT NULL,
  `to_user` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_user` (`from_user`),
  KEY `to_user` (`to_user`),
  CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `friend_requests` */

insert into `friend_requests` values 
(2,1,2,'accepted','2025-07-08 17:03:57'),
(3,3,1,'accepted','2025-07-08 17:36:55'),
(5,1,2,'accepted','2025-07-13 22:35:34'),
(6,1,2,'accepted','2025-07-13 22:49:38'),
(7,2,1,'accepted','2025-07-14 10:02:27'),
(8,1,2,'accepted','2025-07-14 10:17:43'),
(10,1,3,'accepted','2025-07-15 16:30:00'),
(11,1,3,'accepted','2025-07-15 16:38:42'),
(24,1,3,'accepted','2025-07-15 22:35:22'),
(26,1,2,'accepted','2025-07-20 18:42:40'),
(29,1,2,'accepted','2025-07-21 15:25:52'),
(32,2,1,'accepted','2025-07-21 15:52:01'),
(33,1,2,'accepted','2025-07-21 16:03:08'),
(34,1,2,'accepted','2025-07-21 16:05:01');

/*Table structure for table `friends` */

DROP TABLE IF EXISTS `friends`;

CREATE TABLE `friends` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `since` datetime DEFAULT current_timestamp(),
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  PRIMARY KEY (`user_id`,`friend_id`),
  KEY `friend_id` (`friend_id`),
  CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `friends` */

insert into `friends` values 
(1,2,'2025-07-21 16:05:12','accepted'),
(2,1,'2025-07-21 16:05:12','accepted');

/*Table structure for table `likes` */

DROP TABLE IF EXISTS `likes`;

CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `likes` */

insert into `likes` values 
(4,8,1,'2025-07-08 14:51:38'),
(5,11,2,'2025-07-08 15:14:20'),
(6,10,2,'2025-07-08 15:14:22'),
(7,9,2,'2025-07-08 15:14:25'),
(8,8,2,'2025-07-08 15:14:27'),
(9,4,2,'2025-07-08 15:14:29'),
(10,12,1,'2025-07-08 15:32:33'),
(12,8,3,'2025-07-08 17:36:34'),
(13,13,1,'2025-07-08 17:42:32'),
(17,4,1,'2025-07-10 15:30:01'),
(18,14,2,'2025-07-10 17:31:42'),
(19,14,1,'2025-07-11 14:17:20'),
(20,13,2,'2025-07-11 22:40:23'),
(21,14,3,'2025-07-11 22:43:07'),
(22,13,3,'2025-07-11 22:43:12'),
(23,12,3,'2025-07-11 22:43:14'),
(24,11,3,'2025-07-11 22:43:19'),
(25,10,3,'2025-07-11 22:43:22'),
(26,9,3,'2025-07-11 22:43:24'),
(27,4,3,'2025-07-11 22:43:27'),
(31,28,1,'2025-07-15 16:49:30'),
(32,33,2,'2025-07-16 22:25:22');

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user` int(11) DEFAULT NULL,
  `to_user` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `from_user` (`from_user`),
  KEY `to_user` (`to_user`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `messages` */

insert into `messages` values 
(14,1,3,'[file]assets/uploads/messages/1752304062_TAHAP 2 [98DE255].mp4','2025-07-12 15:07:42',1,0),
(16,1,3,'[file]assets/uploads/messages/1752304149_Maroon_5_Sugar_480p_MUX_CBR-128k.mp3','2025-07-12 15:09:09',1,1),
(37,1,2,'hai','2025-07-20 19:09:21',1,0);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `notifications` */

insert into `notifications` values 
(1,1,'Dangker Dida menolak permintaan pertemananmu.',1,'2025-07-08 16:37:05'),
(2,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-08 17:03:57'),
(3,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-08 17:04:36'),
(4,1,'Arya Dida mengirimkan permintaan pertemanan.',1,'2025-07-08 17:36:55'),
(5,3,'Arya menerima permintaan pertemanan dari kamu!',1,'2025-07-08 17:38:27'),
(6,2,'Arya Dida mengirimkan permintaan pertemanan.',1,'2025-07-11 22:44:28'),
(7,3,'Dangker Dida menolak permintaan pertemanan dari kamu.',1,'2025-07-11 22:45:48'),
(8,2,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-13 22:33:26'),
(9,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-13 22:35:34'),
(10,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-13 22:48:12'),
(11,1,'Dangker Dida telah menghapus kamu dari daftar temannya.',1,'2025-07-13 22:48:52'),
(12,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-13 22:49:38'),
(13,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-13 22:50:30'),
(14,1,'Dangker Dida telah menghapus kamu dari daftar temannya.',1,'2025-07-14 10:02:15'),
(15,1,'Dangker Dida mengirimkan permintaan pertemanan.',1,'2025-07-14 10:02:27'),
(16,2,'Arya menerima permintaan pertemanan dari kamu!',1,'2025-07-14 10:02:55'),
(17,1,'Dangker Dida telah menghapus kamu dari daftar temannya.',1,'2025-07-14 10:17:26'),
(18,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-14 10:17:43'),
(19,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-14 10:17:53'),
(21,3,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-15 13:01:43'),
(22,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 16:30:01'),
(23,1,'Arya Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-15 16:31:48'),
(24,3,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-15 16:37:16'),
(25,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 16:38:42'),
(26,1,'Arya Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-15 16:38:58'),
(27,3,'Dangker Dida mengirimkan permintaan pertemanan.',1,'2025-07-15 17:36:16'),
(28,3,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-15 20:54:26'),
(29,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 20:58:40'),
(30,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 20:59:32'),
(31,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 21:00:35'),
(32,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 21:01:15'),
(33,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 21:11:05'),
(34,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:05:23'),
(35,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:09:53'),
(36,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:21:16'),
(37,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:24:05'),
(38,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:25:18'),
(39,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:25:32'),
(40,3,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-15 22:35:22'),
(41,1,'Arya Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-15 22:36:56'),
(43,2,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-20 18:42:34'),
(44,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-20 18:42:40'),
(45,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-20 18:47:19'),
(46,3,'Arya telah menghapus kamu dari daftar temannya.',0,'2025-07-21 14:21:10'),
(47,3,'Arya mengirimkan permintaan pertemanan.',0,'2025-07-21 14:21:19'),
(48,2,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-21 14:31:34'),
(49,3,'Arya mengirimkan permintaan pertemanan.',0,'2025-07-21 15:20:48'),
(50,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-21 15:25:52'),
(51,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-21 15:26:31'),
(52,2,'Arya telah menghapus kamu dari daftar temannya.',1,'2025-07-21 15:28:17'),
(53,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-21 15:28:21'),
(54,1,'Dangker Dida menolak permintaan pertemanan dari kamu.',1,'2025-07-21 15:46:51'),
(55,2,'Arya menerima permintaan pertemanan dari kamu!',1,'2025-07-21 15:55:20'),
(56,1,'Dangker Dida telah menghapus kamu dari daftar temannya.',1,'2025-07-21 15:57:42'),
(57,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-21 16:03:29'),
(58,2,'Arya mengirimkan permintaan pertemanan.',1,'2025-07-21 16:05:01'),
(59,1,'Dangker Dida menerima permintaan pertemanan dari kamu!',1,'2025-07-21 16:05:12');

/*Table structure for table `post_files` */

DROP TABLE IF EXISTS `post_files`;

CREATE TABLE `post_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` enum('image','video','audio') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `post_files_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `post_files` */

insert into `post_files` values 
(2,8,'assets/uploads/post_686cbff61b6fd.jpg','image'),
(3,9,'assets/uploads/post_686cc048e093d.mp4','video'),
(4,10,'assets/uploads/post_686cc08ece376.mp3','audio'),
(5,11,'assets/uploads/post_686cc5014c063.jpg','image'),
(6,13,'assets/uploads/post_686ce7eb4b3e5.jpg','image'),
(7,14,'assets/uploads/post_686f8842f3372.mp4','video'),
(9,21,'assets/uploads/post_68750e7c1962f.jpg','image'),
(10,24,'assets/uploads/post_68750fb359087.mp4','video'),
(11,25,'assets/uploads/post_6875100a4234b.jpg','image'),
(13,33,'assets/uploads/post_6877b4d89d03e.jpg','image'),
(14,34,'assets/uploads/post_687cc6d7472d2.jpg','image');

/*Table structure for table `post_shares` */

DROP TABLE IF EXISTS `post_shares`;

CREATE TABLE `post_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `share_type` enum('copy','whatsapp','facebook','twitter') NOT NULL,
  `share_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_post_id` (`post_id`),
  CONSTRAINT `fk_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `post_shares` */

insert into `post_shares` values 
(2,24,'whatsapp','2025-07-15 21:41:50'),
(4,21,'copy','2025-07-15 22:04:12'),
(5,33,'copy','2025-07-16 22:25:27');

/*Table structure for table `posts` */

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `posts` */

insert into `posts` values 
(4,1,'Hello World!','2025-07-08 13:13:23','2025-07-11 15:18:12'),
(8,1,'Black Clover','2025-07-08 14:51:34','2025-07-08 14:51:34'),
(9,1,'Main COC dulu nggak sehhh!!!!!!','2025-07-08 14:52:56','2025-07-08 14:52:56'),
(10,1,'Lagu korea nih boss!!!','2025-07-08 14:54:06','2025-07-08 14:54:06'),
(11,1,'SD GMIT 03 Kalabahi','2025-07-08 15:13:05','2025-07-08 15:13:05'),
(12,2,'hai saya Dangker, salam kenal semuanya!! ayo kita berteman','2025-07-08 15:17:18','2025-07-08 15:30:08'),
(13,1,'Tedis sebelum corona ?','2025-07-08 17:42:03','2025-07-14 16:05:20'),
(14,3,'nca hbec ebwih','2025-07-10 17:30:41','2025-07-10 17:30:41'),
(21,1,'asad','2025-07-14 22:04:44','2025-07-14 22:04:44'),
(24,1,'acnjscakj','2025-07-14 22:09:55','2025-07-14 22:09:55'),
(25,1,'sadsaf','2025-07-14 22:11:21','2025-07-14 22:11:21'),
(28,3,'fxgftyc ggguh','2025-07-15 16:40:13','2025-07-15 16:40:13'),
(30,2,'? ? ? ? ? ? ? ? ? ? ? ? ? ? ?','2025-07-16 16:04:46','2025-07-16 16:04:46'),
(31,2,'???','2025-07-16 16:10:56','2025-07-16 16:10:56'),
(32,2,'????','2025-07-16 16:15:19','2025-07-16 16:15:19'),
(33,1,'?csdcsdvs','2025-07-16 22:19:04','2025-07-16 22:19:04'),
(34,1,'Kimetsu No Yaiba S5?','2025-07-20 18:37:11','2025-07-20 18:37:11');

/*Table structure for table `profiles` */

DROP TABLE IF EXISTS `profiles`;

CREATE TABLE `profiles` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `profiles` */

insert into `profiles` values 
(1,'Arya','Hello World!\r\n','assets/uploads/profile_687522bd52a0d.jpg','assets/uploads/cover/cover_687524cea7497.jpg','2025-07-15 00:06:22'),
(2,'Dangker Dida','Dangker\r\n','assets/uploads/profile/profile_6873957a2d46c.jpg','assets/uploads/cover/cover_6875254203a96.jpg','2025-07-15 00:03:45'),
(3,'Arya Dida',NULL,NULL,NULL,'2025-07-08 17:35:00');

/*Table structure for table `stories` */

DROP TABLE IF EXISTS `stories`;

CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('text','image','video','music') NOT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `stories` */

/*Table structure for table `story_views` */

DROP TABLE IF EXISTS `story_views`;

CREATE TABLE `story_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_view` (`user_id`,`story_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `story_views` */

insert into `story_views` values 
(1,1,22,'2025-07-14 22:32:59'),
(5,2,22,'2025-07-14 22:38:57'),
(6,2,23,'2025-07-14 22:40:52'),
(7,1,23,'2025-07-14 22:41:01'),
(8,1,24,'2025-07-14 22:41:51'),
(13,3,22,'2025-07-14 23:03:32'),
(14,1,25,'2025-07-15 00:10:10'),
(15,2,25,'2025-07-15 00:10:26'),
(16,1,26,'2025-07-15 15:15:13'),
(17,3,25,'2025-07-15 16:39:18'),
(18,3,26,'2025-07-15 16:39:28'),
(19,2,26,'2025-07-15 17:35:52'),
(20,1,27,'2025-07-16 22:24:23'),
(21,2,27,'2025-07-16 22:24:47'),
(22,1,28,'2025-07-20 18:29:31');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT NULL,
  `reset_token` varchar(6) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert into `users` values 
(1,'arya@gmail.com','$2y$10$2v4UWkgrPpuCAAToUu09Y.Bg43YDTpz5uvMtqgSPiiGmEzItegr3e','2025-07-08 12:11:59','2025-07-21 17:00:58',NULL,NULL,'Dimana kamu pertama kali liburan dengan keluarga?','Bali'),
(2,'dangkerdida@gmail.com','$2y$10$9P0ScB.EkpGrQ1Dd3F/6Nu663DumOZi5phz0pSaT3sTR7kQxLMlyW','2025-07-08 15:14:13','2025-07-21 17:00:56','125399','2025-07-21 08:41:56','Dimana kamu pertama kali liburan dengan keluarga?','Alor'),
(3,'ariyantodida@gmail.com','$2y$10$tUkganGqfucXv6q9O9yuVevfeCn5ZGKcyXBzwUVTc08IkpkvytMIO','2025-07-08 17:35:00','2025-07-16 17:37:01',NULL,NULL,'Dimana kamu pertama kali liburan dengan keluarga?','Semau');
