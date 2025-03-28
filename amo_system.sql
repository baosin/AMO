-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-02-19 07:23:12
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
-- 資料庫： `amo_system`
--

-- --------------------------------------------------------

--
-- 資料表結構 `closed_orders`
--

CREATE TABLE `closed_orders` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `close_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `daily_restaurants`
--

CREATE TABLE `daily_restaurants` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `menus`
--

INSERT INTO `menus` (`id`, `restaurant_id`, `name`, `price`, `is_available`, `created_at`) VALUES
(1, 1, '無刺魚肚飯', 130.00, 1, '2025-02-17 09:04:26'),
(2, 1, '酥炸鱈魚飯', 130.00, 1, '2025-02-17 09:04:26'),
(3, 1, '紅燒牛肉飯', 130.00, 1, '2025-02-17 09:04:26'),
(4, 1, '招牌雞腿飯', 120.00, 1, '2025-02-17 09:04:26'),
(5, 1, '花雕雞肉飯', 120.00, 1, '2025-02-17 09:04:26'),
(6, 1, '雞排飯', 110.00, 1, '2025-02-17 09:04:26'),
(7, 1, '香酥排骨飯', 110.00, 1, '2025-02-17 09:04:26'),
(8, 1, '紅燒小排飯', 100.00, 1, '2025-02-17 09:04:26'),
(9, 1, '白飯', 10.00, 1, '2025-02-17 09:04:26'),
(11, 3, '合菜蓋飯', 70.00, 1, '2025-02-18 03:44:01'),
(12, 3, '麻油土雞蓋飯', 120.00, 1, '2025-02-18 03:44:01'),
(13, 3, '炙燒鯖魚蓋飯', 110.00, 1, '2025-02-18 03:44:01'),
(14, 3, '椒鹽雞腿蓋飯', 110.00, 1, '2025-02-18 03:44:01'),
(15, 3, '和風牛肉蓋飯', 110.00, 1, '2025-02-18 03:44:01'),
(16, 3, '咖哩豬排蓋飯', 100.00, 1, '2025-02-18 03:44:01'),
(17, 3, '椒鹽豬排蓋飯', 100.00, 1, '2025-02-18 03:44:01'),
(18, 3, '蔥燒丸子蓋飯', 90.00, 1, '2025-02-18 03:44:01'),
(19, 3, '和風豬排蓋飯', 90.00, 1, '2025-02-18 03:44:01'),
(20, 3, '泰式腿排蓋飯', 90.00, 1, '2025-02-18 03:44:01'),
(21, 3, '日式燒肉蓋飯', 90.00, 1, '2025-02-18 03:44:01'),
(22, 3, '茄汁雞排蓋飯', 80.00, 1, '2025-02-18 03:44:01'),
(23, 3, '府城蝦卷蓋飯', 80.00, 1, '2025-02-18 03:44:01');

-- --------------------------------------------------------

--
-- 資料表結構 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `orders`
--

INSERT INTO `orders` (`id`, `user_name`, `menu_id`, `quantity`, `note`, `created_at`) VALUES
(20, '22', 12, 2, '', '2025-02-19 03:56:04'),
(21, '88', 12, 1, '', '2025-02-19 03:56:33'),
(22, '22', 14, 1, '', '2025-02-19 06:01:37');

-- --------------------------------------------------------

--
-- 資料表結構 `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `modified_by` varchar(50) NOT NULL,
  `old_data` text NOT NULL,
  `new_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_deadline` time DEFAULT '14:00:00' COMMENT '訂餐截止時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `phone`, `is_active`, `created_at`, `order_deadline`) VALUES
(1, '金大元', '04-22207597', 0, '2025-02-17 09:00:28', '14:00:00'),
(3, '捷克廚房', '04-23130455', 1, '2025-02-18 03:31:28', '14:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `order_deadline` time DEFAULT '11:00:00',
  `is_ordering_active` tinyint(1) DEFAULT 1,
  `selected_restaurant_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `settings`
--

INSERT INTO `settings` (`id`, `order_deadline`, `is_ordering_active`, `selected_restaurant_id`, `order_date`) VALUES
(1, '09:30:00', 1, 3, NULL);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `closed_orders`
--
ALTER TABLE `closed_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- 資料表索引 `daily_restaurants`
--
ALTER TABLE `daily_restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- 資料表索引 `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- 資料表索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- 資料表索引 `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- 資料表索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- 資料表索引 `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selected_restaurant_id` (`selected_restaurant_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `closed_orders`
--
ALTER TABLE `closed_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `daily_restaurants`
--
ALTER TABLE `daily_restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `closed_orders`
--
ALTER TABLE `closed_orders`
  ADD CONSTRAINT `closed_orders_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);

--
-- 資料表的限制式 `daily_restaurants`
--
ALTER TABLE `daily_restaurants`
  ADD CONSTRAINT `daily_restaurants_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);

--
-- 資料表的限制式 `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);

--
-- 資料表的限制式 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`);

--
-- 資料表的限制式 `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- 資料表的限制式 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`);

--
-- 資料表的限制式 `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`selected_restaurant_id`) REFERENCES `restaurants` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
