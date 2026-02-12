-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Feb 12, 2026 at 06:47 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `color`, `created_at`, `updated_at`) VALUES
(1, 'عصائر طازجة', NULL, '#FFA500', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(2, 'عالم القصب', NULL, '#32CD32', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(3, 'عصائر شرقية', NULL, '#DC143C', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(4, 'الأيس كريم', NULL, '#FF69B4', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(5, 'الميلك شيك', NULL, '#FFB6C1', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(6, 'الآيسكريم فلو', NULL, '#87CEFA', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(7, 'عالم المانجو', NULL, '#FFA500', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(8, 'مشروبات الطاقة', NULL, '#FF0000', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(9, 'الكريب والوافل', NULL, '#D2691E', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(10, 'Soda', NULL, '#00CED1', '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(11, 'كشري الحلو', NULL, '#DAA520', '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(12, 'الفيمتو والمخلوطات', NULL, '#8B008B', '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(13, 'المشروبات الساخنة', NULL, '#A0522D', '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(14, 'الحلويات والمكملات', NULL, '#FF69B4', '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(15, 'خصومات', 'خصومات', '#8B5CF6', '2025-10-10 21:31:41', '2025-10-10 21:31:41'),
(16, 'عالم الاوريو', '', '#8B5CF6', '2025-12-21 23:17:58', '2025-12-21 23:17:58'),
(17, 'اخرى', '', '#F59E0B', '2025-12-21 23:20:39', '2025-12-21 23:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT '0.00',
  `hiring_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `password`, `role`, `phone`, `salary`, `hiring_date`, `active`, `created_at`, `updated_at`) VALUES
(1, 'سيد نجاح', 'sayed@gmail.com', '123456', 'صالة', '01125833982', 7000.00, '2025-06-01', 1, '2025-10-10 21:53:57', '2025-10-10 21:53:57'),
(2, 'جوده', 'joda@gmail.com', '123456', 'صالة', '0125486793', 5000.00, '2025-07-01', 1, '2025-10-10 21:57:34', '2025-10-10 21:57:34'),
(3, 'ahmed saeed', 'ahmedelsaeedam@gmail.com', '0123456', 'cashier', '01123401552', 200000.00, '2025-11-20', 1, '2025-10-12 21:27:36', '2025-10-12 21:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `purchase_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sale_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hasSizes` tinyint(1) DEFAULT '0',
  `price` decimal(10,2) DEFAULT '0.00',
  `s_price` decimal(10,2) DEFAULT '0.00',
  `m_price` decimal(10,2) DEFAULT '0.00',
  `l_price` decimal(10,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  `barcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `hasSizes`, `price`, `s_price`, `m_price`, `l_price`, `stock`, `barcode`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 'مانجو', 1, 0.00, 35.00, 45.00, 55.00, 0, '1', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(2, 'برتقال', 1, 0.00, 30.00, 40.00, 50.00, 0, '2', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(3, 'ليمون', 1, 0.00, 30.00, 40.00, 50.00, 0, '3', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(4, 'تفاح', 1, 0.00, 35.00, 45.00, 55.00, 0, '4', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(5, 'جوافة', 1, 0.00, 35.00, 45.00, 55.00, 0, '5', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(6, 'فراولة', 1, 0.00, 35.00, 45.00, 55.00, 0, '6', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(7, 'رمان', 1, 0.00, 35.00, 45.00, 55.00, 0, '7', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(8, 'كيوي', 1, 0.00, 35.00, 45.00, 55.00, 0, '8', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(9, 'كوكتيل', 1, 0.00, 40.00, 50.00, 60.00, 0, '9', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(10, 'ليمون نعناع', 1, 0.00, 35.00, 45.00, 55.00, 0, '10', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(11, 'تفاح أحمر', 1, 0.00, 35.00, 45.00, 55.00, 0, '11', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(12, 'تفاح أخضر', 1, 0.00, 35.00, 45.00, 55.00, 0, '12', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(13, 'حليب بالموز', 1, 0.00, 40.00, 50.00, 60.00, 0, '13', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(14, 'حليب بالتفاح', 1, 0.00, 40.00, 50.00, 60.00, 0, '14', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(15, 'حليب بالعسل', 1, 0.00, 40.00, 50.00, 60.00, 0, '15', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(16, 'حليب بالرمان', 1, 0.00, 40.00, 50.00, 60.00, 0, '16', 1, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(17, 'قصب سادة', 1, 0.00, 15.00, 20.00, 25.00, 0, '17', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(18, 'قصب بالبرتقال', 1, 0.00, 20.00, 25.00, 30.00, 0, '18', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(19, 'قصب بالليمون', 1, 0.00, 20.00, 25.00, 30.00, 0, '19', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(20, 'قصب بالرمان', 1, 0.00, 20.00, 25.00, 30.00, 0, '20', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(21, 'قصب بالفراولة', 1, 0.00, 25.00, 30.00, 35.00, 0, '21', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(22, 'قصب بالكيوي', 1, 0.00, 25.00, 30.00, 35.00, 0, '22', 2, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(23, 'تمر هندي', 1, 0.00, 15.00, 20.00, 25.00, 0, '23', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(24, 'كركديه', 1, 0.00, 15.00, 20.00, 25.00, 0, '24', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(25, 'سوبيا', 1, 0.00, 20.00, 25.00, 30.00, 0, '25', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(26, 'خروب', 1, 0.00, 20.00, 25.00, 30.00, 0, '26', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(27, 'لبن بالبلح', 1, 0.00, 25.00, 30.00, 35.00, 0, '27', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(28, 'لبن بالعسل', 1, 0.00, 25.00, 30.00, 35.00, 0, '28', 3, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(29, 'كورة', 0, 20.00, 0.00, 0.00, 0.00, 0, '29', 4, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(30, 'كورتين', 0, 30.00, 0.00, 0.00, 0.00, 0, '30', 4, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(31, 'ثلاث كور', 0, 40.00, 0.00, 0.00, 0.00, 0, '31', 4, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(32, 'شيك فراولة', 1, 0.00, 45.00, 50.00, 60.00, 0, '32', 5, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(33, 'شيك موز', 1, 0.00, 45.00, 50.00, 60.00, 0, '33', 5, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(34, 'شيك مانجو', 1, 0.00, 45.00, 50.00, 60.00, 0, '34', 5, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(35, 'شيك كيوي', 1, 0.00, 45.00, 50.00, 60.00, 0, '35', 5, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(36, 'شيك شوكولاتة', 1, 0.00, 45.00, 50.00, 60.00, 0, '36', 5, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(37, 'كولا فلو', 0, 45.00, 0.00, 0.00, 0.00, 0, '37', 6, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(38, 'بيبسي فلو', 0, 45.00, 0.00, 0.00, 0.00, 0, '38', 6, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(39, 'فيمتو فلو', 0, 45.00, 0.00, 0.00, 0.00, 0, '39', 6, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(40, 'ريد بول فلو', 0, 50.00, 0.00, 0.00, 0.00, 0, '40', 6, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(41, 'بلوبيري فلو', 0, 50.00, 0.00, 0.00, 0.00, 0, '41', 6, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(42, 'مانجو عادي', 1, 0.00, 35.00, 40.00, 50.00, 0, '42', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(43, 'مانجو ممتاز', 1, 0.00, 40.00, 45.00, 55.00, 0, '43', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(44, 'مانجو كيوي', 1, 0.00, 45.00, 50.00, 60.00, 0, '44', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(45, 'مانجو فراولة', 1, 0.00, 45.00, 50.00, 60.00, 0, '45', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(46, 'مانجو أناناس', 1, 0.00, 45.00, 50.00, 60.00, 0, '46', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(47, 'مانجو مانجو', 1, 0.00, 50.00, 55.00, 65.00, 0, '47', 7, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(48, 'ريد بول', 0, 60.00, 0.00, 0.00, 0.00, 0, '48', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(49, 'بريل', 0, 60.00, 0.00, 0.00, 0.00, 0, '49', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(50, 'بيبسي', 0, 60.00, 0.00, 0.00, 0.00, 0, '50', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(51, 'فيروز', 0, 60.00, 0.00, 0.00, 0.00, 0, '51', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(52, 'ميراندا', 0, 70.00, 0.00, 0.00, 0.00, 0, '52', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(53, 'حمضيات طاقة', 0, 60.00, 0.00, 0.00, 0.00, 0, '53', 8, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(54, 'وافل سادة', 0, 35.00, 0.00, 0.00, 0.00, 0, '54', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(55, 'وافل نوتيلا', 0, 40.00, 0.00, 0.00, 0.00, 0, '55', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(56, 'وافل نوتيلا موز', 0, 45.00, 0.00, 0.00, 0.00, 0, '56', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(57, 'كريب سادة', 0, 35.00, 0.00, 0.00, 0.00, 0, '57', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(58, 'كريب نوتيلا', 0, 40.00, 0.00, 0.00, 0.00, 0, '58', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(59, 'كريب نوتيلا موز', 0, 45.00, 0.00, 0.00, 0.00, 0, '59', 9, '2025-10-10 20:59:42', '2025-10-10 20:59:42'),
(60, 'سودا ليمون نعناع', 0, 40.00, 0.00, 0.00, 0.00, 0, '60', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(61, 'سودا كيوي', 0, 40.00, 0.00, 0.00, 0.00, 0, '61', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(62, 'سودا بلوبيري', 0, 40.00, 0.00, 0.00, 0.00, 0, '62', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(63, 'سودا فيمتو', 0, 40.00, 0.00, 0.00, 0.00, 0, '63', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(64, 'سودا أناناس', 0, 40.00, 0.00, 0.00, 0.00, 0, '64', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(65, 'سودا ريد بول', 0, 45.00, 0.00, 0.00, 0.00, 0, '65', 10, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(66, 'كشري حلو صغير', 0, 25.00, 0.00, 0.00, 0.00, 0, '66', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(67, 'كشري حلو وسط', 0, 35.00, 0.00, 0.00, 0.00, 0, '67', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(68, 'كشري حلو كبير', 0, 45.00, 0.00, 0.00, 0.00, 0, '68', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(69, 'كشري حلو ملكي', 0, 55.00, 0.00, 0.00, 0.00, 0, '69', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(70, 'كشري حلو كراميل', 0, 50.00, 0.00, 0.00, 0.00, 0, '70', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(71, 'كشري حلو شيكولاتة', 0, 50.00, 0.00, 0.00, 0.00, 0, '71', 11, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(72, 'فيمتو', 1, 0.00, 30.00, 40.00, 50.00, 0, '72', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(73, 'فيمتو كوكتيل', 1, 0.00, 35.00, 45.00, 55.00, 0, '73', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(74, 'فيمتو أناناس', 1, 0.00, 35.00, 45.00, 55.00, 0, '74', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(75, 'فيمتو كيوي', 1, 0.00, 35.00, 45.00, 55.00, 0, '75', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(76, 'مخلوط مانجو فراولة', 1, 0.00, 40.00, 50.00, 60.00, 0, '76', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(77, 'مخلوط مانجو رمان', 1, 0.00, 40.00, 50.00, 60.00, 0, '77', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(78, 'مخلوط مانجو كيوي', 1, 0.00, 40.00, 50.00, 60.00, 0, '78', 12, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(79, 'شاي', 0, 15.00, 0.00, 0.00, 0.00, 0, '79', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(80, 'قهوة سادة', 0, 20.00, 0.00, 0.00, 0.00, 0, '80', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(81, 'قهوة بالحليب', 0, 25.00, 0.00, 0.00, 0.00, 0, '81', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(82, 'نسكافيه', 0, 25.00, 0.00, 0.00, 0.00, 0, '82', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(83, 'لاتيه', 0, 30.00, 0.00, 0.00, 0.00, 0, '83', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(84, 'كابتشينو', 0, 30.00, 0.00, 0.00, 0.00, 0, '84', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(85, 'موكا', 0, 35.00, 0.00, 0.00, 0.00, 0, '85', 13, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(86, 'أرز باللبن', 0, 25.00, 0.00, 0.00, 0.00, 0, '86', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(87, 'أرز باللبن بالمكسرات', 0, 35.00, 0.00, 0.00, 0.00, 0, '87', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(88, 'مهلبية', 0, 25.00, 0.00, 0.00, 0.00, 0, '88', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(89, 'مهلبية بالمكسرات', 0, 35.00, 0.00, 0.00, 0.00, 0, '89', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(90, 'بودنج شوكولاتة', 0, 30.00, 0.00, 0.00, 0.00, 0, '90', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(91, 'كريم كراميل', 0, 30.00, 0.00, 0.00, 0.00, 0, '91', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(92, 'حلا الفواكه', 0, 40.00, 0.00, 0.00, 0.00, 0, '92', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(93, 'أيس كريم شوكولاتة', 0, 35.00, 0.00, 0.00, 0.00, 0, '93', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(94, 'أيس كريم مانجو', 0, 35.00, 0.00, 0.00, 0.00, 0, '94', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(95, 'أيس كريم فانيليا', 0, 35.00, 0.00, 0.00, 0.00, 0, '95', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(96, 'كيك شوكولاتة', 0, 45.00, 0.00, 0.00, 0.00, 0, '96', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(97, 'كيك فانيليا', 0, 45.00, 0.00, 0.00, 0.00, 0, '97', 14, '2025-10-10 20:59:43', '2025-10-10 20:59:43'),
(98, 'خصم بسيط', 0, -10.00, 0.00, 0.00, 0.00, 100, '983095745', 15, '2025-10-10 21:32:19', '2025-10-10 21:32:19'),
(99, 'اخرى', 0, 0.00, 0.00, 0.00, 0.00, 15, '', 16, '2025-12-21 23:20:15', '2025-12-21 23:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` int NOT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` int NOT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `employee_id` int DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `kitchen_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_invoices`
--

INSERT INTO `sales_invoices` (`id`, `invoice_number`, `date`, `time`, `employee_id`, `total`, `kitchen_note`, `created_at`, `updated_at`) VALUES
(1, 'INV-20251010-3977', '2025-10-10', '01:04:33', 2, 95.00, 'وىوةى', '2025-10-10 22:04:34', '2025-10-10 22:04:34'),
(2, 'INV-20251012-0564', '2025-10-12', '01:00:00', 3, 80.00, 'تالبفيقسثس', '2025-10-12 22:00:00', '2025-10-12 22:00:00'),
(3, 'INV-20251220-5797', '2025-12-20', '01:59:15', 2, 135.00, 'ملح', '2025-12-20 23:59:15', '2025-12-20 23:59:15'),
(4, 'INV-20251221-8063', '2025-12-21', '02:20:18', 3, 145.00, '', '2025-12-21 00:20:18', '2025-12-21 00:20:18'),
(5, 'INV-20251221-8983', '2025-12-21', '02:22:38', 2, 200.00, 'hot', '2025-12-21 00:22:39', '2025-12-21 00:22:39'),
(6, 'INV-20251221-1790', '2025-12-21', '00:40:21', 2, 95.00, 'suger', '2025-12-21 22:40:21', '2025-12-21 22:40:21'),
(7, 'INV-20251221-0109', '2025-12-21', '01:13:30', 2, 110.00, 'HJGJHGJHG', '2025-12-21 23:13:30', '2025-12-21 23:13:30'),
(8, 'INV-20260103-4045', '2026-01-03', '15:13:24', 1, 100.00, 'NBMNBMN', '2026-01-03 13:13:24', '2026-01-03 13:13:24'),
(9, 'INV-20260103-1806', '2026-01-03', '15:20:31', 2, 40.00, '', '2026-01-03 13:20:31', '2026-01-03 13:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoice_items`
--

CREATE TABLE `sales_invoice_items` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL DEFAULT '1',
  `barcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_invoice_items`
--

INSERT INTO `sales_invoice_items` (`id`, `invoice_id`, `product_id`, `product_name`, `price`, `quantity`, `barcode`, `created_at`) VALUES
(1, 1, NULL, 'شيك فراولة', 45.00, 1, '32', '2025-10-10 22:04:34'),
(2, 1, NULL, 'شيك موز', 50.00, 1, '33', '2025-10-10 22:04:34'),
(3, 2, NULL, 'وافل نوتيلا', 40.00, 2, '55', '2025-10-12 22:00:00'),
(4, 3, NULL, 'تفاح', 45.00, 1, '4', '2025-12-20 23:59:15'),
(5, 3, NULL, 'رمان', 55.00, 1, '7', '2025-12-20 23:59:15'),
(6, 3, NULL, 'كيوي', 35.00, 1, '8', '2025-12-20 23:59:15'),
(7, 4, NULL, 'مانجو', 45.00, 1, '1', '2025-12-21 00:20:18'),
(8, 4, NULL, 'تفاح', 45.00, 1, '4', '2025-12-21 00:20:18'),
(9, 4, NULL, 'فراولة', 55.00, 1, '6', '2025-12-21 00:20:18'),
(10, 5, NULL, 'مانجو', 45.00, 1, '1', '2025-12-21 00:22:39'),
(11, 5, NULL, 'تفاح', 55.00, 1, '4', '2025-12-21 00:22:39'),
(12, 5, NULL, 'جوافة', 45.00, 1, '5', '2025-12-21 00:22:39'),
(13, 5, NULL, 'فراولة', 55.00, 1, '6', '2025-12-21 00:22:39'),
(14, 6, NULL, 'ليمون', 40.00, 1, '3', '2025-12-21 22:40:21'),
(15, 6, NULL, 'مانجو', 55.00, 1, '1', '2025-12-21 22:40:21'),
(16, 7, NULL, 'حليب بالموز', 60.00, 1, '13', '2025-12-21 23:13:30'),
(17, 7, NULL, 'حليب بالرمان', 50.00, 1, '16', '2025-12-21 23:13:30'),
(18, 8, NULL, 'مانجو', 45.00, 1, '1', '2026-01-03 13:13:24'),
(19, 8, NULL, 'تفاح', 55.00, 1, '4', '2026-01-03 13:13:24'),
(20, 9, NULL, 'برتقال', 40.00, 1, '2', '2026-01-03 13:20:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_items_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD CONSTRAINT `sales_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
