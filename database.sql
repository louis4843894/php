-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-11-17 19:28:08
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `student_portfolio`
--

-- --------------------------------------------------------

--
-- 資料表結構 `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` enum('subject','language','competition','certificate') NOT NULL COMMENT '擅長科目/程式語言/參與競賽/取得證照',
  `title` varchar(200) NOT NULL COMMENT '成果名稱',
  `description` text DEFAULT NULL COMMENT '成果描述',
  `status` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT '待審核/已認證/不通過',
  `review_note` text DEFAULT NULL COMMENT '審核備註',
  `reviewed_by` int(11) DEFAULT NULL COMMENT '審核者ID',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT '審核時間',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `achievements`
--

INSERT INTO `achievements` (`id`, `user_id`, `category`, `title`, `description`, `status`, `review_note`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(7, 4, 'subject', '資料結構與演算法', '熟悉常見資料結構（如陣列、鏈結串列、樹）並能應用排序和搜尋演算法。', 'pending', NULL, NULL, NULL, '2025-11-17 17:08:47', '2025-11-17 17:08:47'),
(8, 4, 'language', 'Java', '具備物件導向程式設計（OOP）概念，能使用 Java 進行應用程式開發。', 'pending', NULL, NULL, NULL, '2025-11-17 17:08:47', '2025-11-17 17:08:47'),
(9, 4, 'competition', '資訊奧林匹亞競賽', '參加全國資訊奧林匹亞競賽，學習演算法解題。', 'pending', NULL, NULL, NULL, '2025-11-17 17:08:47', '2025-11-17 17:08:47'),
(10, 4, 'certificate', 'LPI Linux Essentials', '通過 LPI Linux Essentials 認證考試，掌握基本的 Linux 操作和指令。', 'pending', NULL, NULL, NULL, '2025-11-17 17:08:47', '2025-11-17 17:08:47'),
(12, 2, 'language', 'PHP', '熟悉 PHP 基礎語法、Session、表單處理與資料庫操作', 'approved', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54'),
(13, 2, 'language', 'MySQL', '熟練使用 MySQL 進行資料庫設計與 SQL 查詢', 'approved', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54'),
(14, 2, 'subject', '網頁設計', '能夠��用 HTML、CSS、JavaScript 建立響應式網頁', 'approved', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54'),
(15, 2, 'competition', '程式設計競賽', '參加校內程式設計競賽獲得第三名', 'pending', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54'),
(16, 3, 'language', 'Python', '學習 Python 基礎語法與資料處理', 'pending', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54'),
(17, 3, 'certificate', 'TQC+ 網頁設計認證', '取得 TQC+ 網頁設計專業級認證', 'approved', NULL, NULL, NULL, '2025-11-17 17:25:54', '2025-11-17 17:25:54');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `photo`, `bio`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin@school.edu', '系統管理員', 'admin', NULL, '負責審核學生學習成果', '2025-11-17 17:24:03'),
(2, 'student1', 'student123', 'student1@school.edu', '王小明', 'student', NULL, '熱愛程式設計，專注於網頁開發', '2025-11-17 17:24:03'),
(3, 'student2', 'student123', 'student2@school.edu', '李小華', 'student', NULL, '對資料庫與後端技術特別有興趣', '2025-11-17 17:24:03'),
(4, 'zdx_1013', '123456', 'cvu31161a@gmail.com', '鄭得諼', 'student', 'user_4_1763398311.png', '喜歡玩手遊', '2025-11-17 16:49:07');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_category` (`category`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievements_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
