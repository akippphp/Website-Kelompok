-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 04:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barberking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `layanan` varchar(100) DEFAULT NULL,
  `kapster` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `harga` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `nama`, `phone`, `layanan`, `kapster`, `tanggal`, `jam`, `hari`, `status`, `created_at`, `harga`) VALUES
(32, 'alif fitrah ramadhan', '082260729750', 'Paket Haircut', 'Ogay', '2026-01-08', '10:00:00', NULL, 'Selesai', '2026-01-08 02:56:29', 35000),
(33, 'hamid', '0098008', 'Paket Haircut', 'Madhuk', '2026-01-08', '10:00:00', NULL, 'Selesai', '2026-01-08 02:58:09', 35000),
(36, 'hamid', '0808080', 'Paket Haircut', 'Ogay', '2026-01-09', '09:00:00', NULL, 'Menunggu', '2026-01-09 03:17:16', 35000),
(37, 'kzsdhaskdhkash', '090808080', 'Paket Haircut', 'Ogay', '2026-01-09', '09:00:00', NULL, 'Menunggu', '2026-01-09 03:24:21', 35000);

-- --------------------------------------------------------

--
-- Table structure for table `kapster`
--

CREATE TABLE `kapster` (
  `id` int(11) NOT NULL,
  `nama_kapster` varchar(255) DEFAULT NULL,
  `spesialisasi` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kapster`
--

INSERT INTO `kapster` (`id`, `nama_kapster`, `spesialisasi`, `foto`, `created_at`) VALUES
(7, 'Kilcong', 'Barber dengan pengalaman 5 tahun, ahli potongan fade, taper, dan haircut premium.', '1766053818_Kilcong.jpg', '2025-12-18 10:30:18'),
(8, 'Madhuk', 'Spesialis cukur jenggot rapi, shaving tradisional, dan grooming profesional. ', '1766054353_Madhuk.jpg', '2025-12-18 10:39:13'),
(9, 'Ogay', 'Ahli menata rambut untuk acara formal, casual, dan barber styling klasik.', '1766054400_Ogay.jpg', '2025-12-18 10:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `nama_layanan`, `harga`, `deskripsi`, `foto`) VALUES
(6, 'Paket Haircut', 35000, 'Potongan rambut premium dengan teknik modern & klasik, termasuk cuci dan styling.', '1766053570_644f3965642592ce9b1f40a6_mancave-barbershop-franchise-17.jpg'),
(7, 'Shaving Beard', 25000, 'Cukur jenggot rapi dengan teknik tradisional, handuk hangat, dan essential oil. ', '1766053648_Untitled.jpg'),
(8, 'Paket Styling', 20000, 'Penataan rambut profesional dengan pomade premium & teknik finishing barbershop. ', '1766053730_ssimages.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `password`, `created_at`) VALUES
(2, 'admin', 'admin123@gmail.com', '$2y$10$UucDUJGSPsfsZDq9CnI/Tu358aiQi7a057u79TALCIIN6S2nus2c6', '2025-12-18 03:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('customer','admin') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `created_at`, `updated_at`, `role`) VALUES
(12, 'alif fitrah ramadhan', 'vissel456@gmail.com', '$2y$10$vHb/RDsrye0HGCG0bP8xHeFHSbT12z/2Bohh13B2twGsJNpzzyDCa', '2025-12-18 03:49:59', '2025-12-18 03:49:59', 'customer'),
(14, 'hamid', 'hamid123@gmail.com', '$2y$10$L/3sINPsxjqkGPVLX.YHCOXZ1DZUToupd/j48.oBTkzRsk0qxUNqa', '2025-12-19 03:54:07', '2025-12-19 03:54:07', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kapster`
--
ALTER TABLE `kapster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `kapster`
--
ALTER TABLE `kapster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
