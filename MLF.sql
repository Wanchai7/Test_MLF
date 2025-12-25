-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 03:26 AM
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
-- Database: `if0_40753895_mlf`
--

-- --------------------------------------------------------

--
-- Table structure for table `coping_methods`
--

CREATE TABLE `coping_methods` (
  `id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coping_methods`
--

INSERT INTO `coping_methods` (`id`, `mood_id`, `method_name`) VALUES
(1, 1, 'ออกไปฉลอง / สังสรรค์ (Social)'),
(2, 1, 'ให้รางวัลตัวเอง / ช้อปปิ้ง (Reward)'),
(3, 1, 'แชร์ความสุขให้คนอื่น (Share)'),
(4, 2, 'อยากอยู่คนเดียวเงียบๆ (Alone)'),
(5, 2, 'หาที่ร้องไห้ / ระบาย (Cry)'),
(6, 2, 'หาเพื่อนคุย / ปรึกษา (Talk)'),
(7, 3, 'ผ่อนคลาย / นวด / สปา (Relax)'),
(8, 3, 'หาของกินอร่อยๆ (Eat)'),
(9, 3, 'ระบายอารมณ์ (Release)'),
(10, 4, 'นอนพัก / งีบหลับ (Sleep)'),
(11, 4, 'นั่งโง่ๆ มองวิว (Chill)'),
(12, 4, 'จิบกาแฟ / เครื่องดื่ม (Drink)'),
(13, 5, 'ออกกำลังกาย / วิ่ง (Exercise)'),
(14, 5, 'ทำกิจกรรมผจญภัย (Adventure)'),
(15, 5, 'ทำงาน / สร้างสรรค์ผลงาน (Work)');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moods`
--

CREATE TABLE `moods` (
  `id` int(11) NOT NULL,
  `mood_name` varchar(50) NOT NULL,
  `icon_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `moods`
--

INSERT INTO `moods` (`id`, `mood_name`, `icon_url`) VALUES
(1, '😊 มีความสุข (Happy)', NULL),
(2, '😢 เศร้า / เหงา (Sad)', NULL),
(3, '🤯 เครียด (Stressed)', NULL),
(4, '😴 เหนื่อยล้า (Tired)', NULL),
(5, '🔥 ตื่นเต้น / มีพลัง (Energetic)', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT 1,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `owner_id`, `name`, `description`, `address`, `image_url`, `created_at`) VALUES
(1, 2, 'Siam Paragon', 'ศูนย์รวมความบันเทิงและช้อปปิ้งระดับโลก', 'ปทุมวัน, กรุงเทพ', 'https://images.unsplash.com/photo-1560931296-654876c11b08?w=500', '2025-12-24 08:06:15'),
(2, 2, 'ICONSIAM', 'แลนด์มาร์คริมแม่น้ำเจ้าพระยา วิวสวยบรรยากาศดี', 'คลองสาน, กรุงเทพ', 'https://images.unsplash.com/photo-1579618036035-1d07b461476b?w=500', '2025-12-24 08:06:15'),
(3, 2, 'Dream World', 'สวนสนุกดินแดนในฝัน เครื่องเล่นครบครัน', 'ธัญบุรี, ปทุมธานี', 'https://images.unsplash.com/photo-1628178663854-46c592928156?w=500', '2025-12-24 08:06:15'),
(4, 2, 'Safari World', 'สวนสัตว์เปิดและโชว์การแสดงสัตว์', 'คลองสามวา, กรุงเทพ', 'https://images.unsplash.com/photo-1534567153574-2b12153a87f0?w=500', '2025-12-24 08:06:15'),
(5, 2, 'Asiatique The Riverfront', 'แหล่งท่องเที่ยวริมน้ำ ชิงช้าสวรรค์ยักษ์', 'บางคอแหลม, กรุงเทพ', 'https://images.unsplash.com/photo-1563725585090-67c85854b799?w=500', '2025-12-24 08:06:15'),
(6, 2, 'Chocolate Ville', 'ร้านอาหารบรรยากาศยุโรป ถ่ายรูปสวย', 'คันนายาว, กรุงเทพ', 'https://images.unsplash.com/photo-1559305616-3a99fb7e3077?w=500', '2025-12-24 08:06:15'),
(7, 2, 'Central World', 'ลานหน้าห้างกว้างขวาง จัดกิจกรรมบ่อย', 'ราชประสงค์, กรุงเทพ', 'https://images.unsplash.com/photo-1565538563363-2395a896b461?w=500', '2025-12-24 08:06:15'),
(8, 2, 'Jodd Fairs Rama 9', 'ตลาดนัดกลางคืน ของกินเพียบ ดนตรีสด', 'พระราม 9, กรุงเทพ', 'https://images.unsplash.com/photo-1533900298318-6b8da08a523e?w=500', '2025-12-24 08:06:15'),
(9, 2, 'M-District (EmQuartier)', 'ห้างหรูพร้อมสวนลอยฟ้า Water Garden', 'สุขุมวิท, กรุงเทพ', 'https://images.unsplash.com/photo-1519567755591-333906f61727?w=500', '2025-12-24 08:06:15'),
(10, 2, 'Sea Life Bangkok', 'พิพิธภัณฑ์สัตว์น้ำใจกลางเมือง', 'สยาม, กรุงเทพ', 'https://images.unsplash.com/photo-1535591273668-578e31182c4f?w=500', '2025-12-24 08:06:15'),
(11, 2, 'สวนเบญจกิติ', 'สวนป่าใจกลางเมือง เหมาะแก่การเดินทอดน่อง', 'คลองเตย, กรุงเทพ', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=500', '2025-12-24 08:06:15'),
(12, 2, 'สวนรถไฟ (วชิรเบญจทัศ)', 'พื้นที่สีเขียวขนาดใหญ่ ปั่นจักรยานชิลๆ', 'จตุจักร, กรุงเทพ', 'https://images.unsplash.com/photo-1444927702895-352b2741541d?w=500', '2025-12-24 08:06:15'),
(13, 2, 'บางกระเจ้า', 'ปอดของกรุงเทพ พื้นที่สีเขียวโอบล้อมด้วยแม่น้ำ', 'พระประแดง, สมุทรปราการ', 'https://images.unsplash.com/photo-1493489246101-3162657e289f?w=500', '2025-12-24 08:06:15'),
(14, 2, 'อุทยาน 100 ปี จุฬาฯ', 'สวนสาธารณะดีไซน์สวย นั่งพักผ่อนหย่อนใจ', 'ปทุมวัน, กรุงเทพ', 'https://images.unsplash.com/photo-1558905542-a81d45903c73?w=500', '2025-12-24 08:06:15'),
(15, 2, 'สวนหลวง ร.9', 'สวนพฤกษศาสตร์ขนาดใหญ่ ดอกไม้สวยงาม', 'ประเวศ, กรุงเทพ', 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=500', '2025-12-24 08:06:15'),
(16, 2, 'หาดบางแสน', 'ทะเลใกล้กรุง นั่งมองคลื่นให้หายเศร้า', 'ชลบุรี', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500', '2025-12-24 08:06:15'),
(17, 2, 'พุทธมณฑล', 'สถานที่ทางศาสนา สงบเงียบ ร่มรื่น', 'นครปฐม', 'https://images.unsplash.com/photo-1599831936302-3932e684073f?w=500', '2025-12-24 08:06:15'),
(18, 2, 'หอศิลป์ (BACC)', 'เสพงานศิลป์เงียบๆ ปล่อยใจไปกับภาพวาด', 'ปทุมวัน, กรุงเทพ', 'https://images.unsplash.com/photo-1545989253-02cc26577f88?w=500', '2025-12-24 08:06:15'),
(19, 2, 'ห้องสมุด Neilson Hays', 'ห้องสมุดสถาปัตยกรรมสวยงาม เงียบสงบ', 'บางรัก, กรุงเทพ', 'https://images.unsplash.com/photo-1507842217158-a359ce938b5b?w=500', '2025-12-24 08:06:15'),
(20, 2, 'วัดอรุณราชวราราม', 'ชมความงามริมแม่น้ำ ไหว้พระทำจิตใจให้สงบ', 'บางกอกใหญ่, กรุงเทพ', 'https://images.unsplash.com/photo-1528181304800-259b08848526?w=500', '2025-12-24 08:06:15'),
(21, 2, 'The Smash Room', 'ห้องระบายอารมณ์ ทำลายข้าวของให้สะใจ', 'ทองหล่อ, กรุงเทพ', 'https://images.unsplash.com/photo-1589578228447-e1a4e481c6c8?w=500', '2025-12-24 08:06:15'),
(22, 2, 'Fairtex Muay Thai Gym', 'ต่อยมวยระบายความโกรธ เรียกเหงื่อ', 'บางพลี, สมุทรปราการ', 'https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?w=500', '2025-12-24 08:06:15'),
(23, 2, 'Bounce Thailand', 'สวนแทรมโพลีน กระโดดให้ลืมความเครียด', 'รัชดา, กรุงเทพ', 'https://images.unsplash.com/photo-1522008775451-9e735f0d9a66?w=500', '2025-12-24 08:06:15'),
(24, 2, 'Flow House Bangkok', 'โต้คลื่นจำลอง สนุกสุดเหวี่ยง', 'สุขุมวิท 26, กรุงเทพ', 'https://images.unsplash.com/photo-1502680390469-be75c86b636f?w=500', '2025-12-24 08:06:15'),
(25, 2, 'Peppermint Bike Park', 'สนามปั่นจักรยานวิบาก', 'เลียบด่วนรามอินทรา, กรุงเทพ', 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?w=500', '2025-12-24 08:06:15'),
(26, 2, 'สนามยิงปืน ร.1 รอ.', 'ฝึกสมาธิด้วยการยิงปืน', 'วิภาวดี, กรุงเทพ', 'https://images.unsplash.com/photo-1595590424283-b8f17842773f?w=500', '2025-12-24 08:06:15'),
(27, 2, 'Rock Domain Climbing Gym', 'ปีนหน้าผาจำลอง ท้าทายความสามารถ', 'บางนา, กรุงเทพ', 'https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?w=500', '2025-12-24 08:06:15'),
(28, 2, 'EasyKart Bangkok', 'ขับโกคาร์ทประลองความเร็ว', 'RCA, กรุงเทพ', 'https://images.unsplash.com/photo-1574755106297-393222d4f29a?w=500', '2025-12-24 08:06:15'),
(29, 2, 'Laser Hung', 'ยิงปืนเลเซอร์กับเพื่อน', 'สุขุมวิท, กรุงเทพ', 'https://images.unsplash.com/photo-1555597673-b21d5c935865?w=500', '2025-12-24 08:06:15'),
(30, 2, 'Sky Walk นราธิวาส', 'เดินมองวิวมุมสูง สูดอากาศ', 'สาทร, กรุงเทพ', 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=500', '2025-12-24 08:06:15'),
(31, 2, 'Let\'s Relax Onsen', 'ออนเซ็นสไตล์ญี่ปุ่น แช่น้ำร้อนผ่อนคลาย', 'ทองหล่อ, กรุงเทพ', 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=500', '2025-12-24 08:06:15'),
(32, 2, 'Yunomori Onsen & Spa', 'สปาและออนเซ็นครบวงจร', 'สุขุมวิท 26, กรุงเทพ', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=500', '2025-12-24 08:06:15'),
(33, 2, 'Caturday Cat Cafe', 'คาเฟ่แมว นั่งเล่นกับน้องแมวฮีลใจ', 'ราชเทวี, กรุงเทพ', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=500', '2025-12-24 08:06:15'),
(34, 2, 'The Commons Thonglor', 'คอมมูนิตี้มอลล์ นั่งชิล หาของกิน', 'ทองหล่อ, กรุงเทพ', 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=500', '2025-12-24 08:06:15'),
(35, 2, 'TCDC', 'ศูนย์การเรียนรู้ริมแม่น้ำ เงียบสงบ แอร์เย็น', 'บางรัก, กรุงเทพ', 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=500', '2025-12-24 08:06:15'),
(36, 2, 'Open House Central Embassy', 'Co-living space หนังสือเยอะ วิวสวย', 'เพลินจิต, กรุงเทพ', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=500', '2025-12-24 08:06:15'),
(37, 2, 'Velaa Sindhorn Village', 'แหล่งรวมร้านอาหาร บรรยากาศร่มรื่น', 'หลังสวน, กรุงเทพ', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500', '2025-12-24 08:06:15'),
(38, 2, 'Health Land Spa', 'นวดแผนไทย คลายเส้นหายเมื่อย', 'เอกมัย, กรุงเทพ', 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=500', '2025-12-24 08:06:15'),
(39, 2, 'Roots Coffee', 'ร้านกาแฟหอมๆ นั่งจิบเบาๆ', 'สาทร, กรุงเทพ', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=500', '2025-12-24 08:06:15'),
(40, 2, 'Starbucks Reserve Chao Phraya', 'จิบกาแฟริมแม่น้ำ ชั้น 7 ไอคอนสยาม', 'คลองสาน, กรุงเทพ', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=500', '2025-12-24 08:06:15'),
(41, 2, 'ตลาดน้อย', 'เดินเล่นถ่ายรูปย่านเมืองเก่า', 'สัมพันธวงศ์, กรุงเทพ', 'https://images.unsplash.com/photo-1588661788771-460395782782?w=500', '2025-12-24 08:06:15'),
(42, 2, 'เยาวราช (China Town)', 'ตะลุยกินสตรีทฟู้ดตอนกลางคืน', 'เยาวราช, กรุงเทพ', 'https://images.unsplash.com/photo-1587884175390-34421b0e3635?w=500', '2025-12-24 08:06:15'),
(43, 2, 'เมกา บางนา', 'เดินห้างตากแอร์ อิเกีย', 'บางนา, กรุงเทพ', 'https://images.unsplash.com/photo-1560252829-804f1a72b38f?w=500', '2025-12-24 08:06:15'),
(44, 2, 'สวนสยาม (Siam Amazing Park)', 'สวนน้ำและสวนสนุก', 'คันนายาว, กรุงเทพ', 'https://images.unsplash.com/photo-1513889961551-628c1871c137?w=500', '2025-12-24 08:06:15'),
(45, 2, 'Dog In Town', 'คาเฟ่หมาแสนรู้', 'เอกมัย, กรุงเทพ', 'https://images.unsplash.com/photo-1534361960057-19889db9621e?w=500', '2025-12-24 08:06:15'),
(46, 2, 'Blu-O Rhythm & Bowl', 'โยนโบว์ลิ่ง ร้องคาราโอเกะ', 'สยามพารากอน, กรุงเทพ', 'https://images.unsplash.com/photo-1522263884227-68b3f1146743?w=500', '2025-12-24 08:06:15'),
(47, 2, 'วัดพระแก้ว', 'ไหว้พระคู่บ้านคู่เมือง', 'พระนคร, กรุงเทพ', 'https://images.unsplash.com/photo-1596898132646-77826359d32d?w=500', '2025-12-24 08:06:15'),
(48, 2, 'ท้องฟ้าจำลอง', 'นอนดูดาว แอร์เย็นๆ', 'เอกมัย, กรุงเทพ', 'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=500', '2025-12-24 08:06:15'),
(50, 2, 'Little Zoo Cafe', 'คาเฟ่สัตว์แปลก แรคคูน จิ้งจอก', 'อ่อนนุช, กรุงเทพ', 'https://images.unsplash.com/photo-1501630834273-4b5604d2ee31?w=500', '2025-12-24 08:06:15'),
(51, 2, 'Fly Club', 'ปาตี๋ขี้ยา', 'https://maps.app.goo.gl/WU9jq2AHFT8Ce5NGA', 'https://th.bing.com/th/id/OIP.AoeorAE86G0Ob42tEeJVRAHaEK?w=326&h=183&c=7&r=0&o=5&dpr=1.3&pid=1.7', '2025-12-24 08:52:33');

-- --------------------------------------------------------

--
-- Table structure for table `place_moods`
--

CREATE TABLE `place_moods` (
  `place_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `place_moods`
--

INSERT INTO `place_moods` (`place_id`, `mood_id`) VALUES
(1, 1),
(1, 5),
(2, 1),
(3, 1),
(3, 5),
(4, 1),
(4, 5),
(5, 1),
(6, 1),
(6, 4),
(7, 1),
(7, 5),
(8, 1),
(8, 5),
(9, 1),
(10, 1),
(11, 2),
(11, 3),
(11, 4),
(12, 2),
(12, 3),
(13, 2),
(13, 3),
(14, 2),
(14, 4),
(15, 2),
(16, 2),
(16, 3),
(17, 2),
(18, 2),
(19, 2),
(19, 4),
(20, 2),
(21, 3),
(22, 3),
(22, 5),
(23, 5),
(24, 5),
(25, 5),
(26, 3),
(27, 5),
(28, 3),
(28, 5),
(29, 5),
(30, 2),
(31, 3),
(31, 4),
(32, 3),
(32, 4),
(33, 1),
(33, 3),
(34, 1),
(34, 4),
(35, 2),
(35, 4),
(36, 4),
(37, 4),
(38, 3),
(38, 4),
(39, 3),
(39, 4),
(40, 2),
(40, 4),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(44, 5),
(45, 1),
(45, 3),
(46, 3),
(46, 5),
(47, 2),
(48, 2),
(48, 4),
(50, 1),
(51, 1);

-- --------------------------------------------------------

--
-- Table structure for table `place_requests`
--

CREATE TABLE `place_requests` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `place_id` int(11) DEFAULT NULL,
  `action_type` enum('ADD','EDIT','DELETE') NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `moods_json` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','owner','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `otp` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `profile_image`, `verification_code`, `verification_expiry`, `is_verified`, `otp`) VALUES
(6, 'owner_new', 'owner_fix@mood.com', '$2y$10$e8oaybQEY.ZZYGwbCy.20OS.wB3tp3KmcPdoClDhppBkSGtXOLAUy', 'owner', '2025-12-24 13:58:08', 'user_6_1766585976.webp', NULL, NULL, 1, NULL),
(7, 'admin_new', 'admin_fix@mood.com', '$2y$10$e8oaybQEY.ZZYGwbCy.20OS.wB3tp3KmcPdoClDhppBkSGtXOLAUy', 'admin', '2025-12-24 13:58:08', NULL, NULL, NULL, 1, NULL),
(13, 'wanchai', 'wanchai@gmail.com', '$2y$10$6Yve5.3PDNzsi6gmXwFZ2usBLGxEIa9sT610riUb0UcPL/O1J5Wsa', 'user', '2025-12-25 02:09:21', NULL, NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_requests`
--

CREATE TABLE `user_requests` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coping_methods`
--
ALTER TABLE `coping_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mood_id` (`mood_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`place_id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `moods`
--
ALTER TABLE `moods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `place_moods`
--
ALTER TABLE `place_moods`
  ADD PRIMARY KEY (`place_id`,`mood_id`),
  ADD KEY `mood_id` (`mood_id`);

--
-- Indexes for table `place_requests`
--
ALTER TABLE `place_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_history`
--
ALTER TABLE `user_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `user_requests`
--
ALTER TABLE `user_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `target_user_id` (`target_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coping_methods`
--
ALTER TABLE `coping_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `moods`
--
ALTER TABLE `moods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `place_requests`
--
ALTER TABLE `place_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_history`
--
ALTER TABLE `user_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_requests`
--
ALTER TABLE `user_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coping_methods`
--
ALTER TABLE `coping_methods`
  ADD CONSTRAINT `coping_methods_ibfk_1` FOREIGN KEY (`mood_id`) REFERENCES `moods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `place_moods`
--
ALTER TABLE `place_moods`
  ADD CONSTRAINT `place_moods_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `place_moods_ibfk_2` FOREIGN KEY (`mood_id`) REFERENCES `moods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `place_requests`
--
ALTER TABLE `place_requests`
  ADD CONSTRAINT `place_requests_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_history`
--
ALTER TABLE `user_history`
  ADD CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_history_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_requests`
--
ALTER TABLE `user_requests`
  ADD CONSTRAINT `user_requests_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_requests_ibfk_2` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
