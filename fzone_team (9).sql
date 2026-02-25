-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 25, 2026 at 01:47 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fzone_team`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `nama` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `stok_baik` int DEFAULT '0',
  `stok_rusak` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama`, `stok`, `harga`, `stok_baik`, `stok_rusak`) VALUES
(22, 'kipas', 25, 30000, 2, 2),
(23, 'laptop', 10, 30000, 7, 0),
(24, 'kursi', 3, 53000, 0, 3),
(25, 'meja', 6, 70000, 4, 2),
(26, 'tas', 60, 55000, 40, 20),
(27, 'jendela', 60, 400000, 45, 15),
(28, 'pulpen', 60, 3000, 42, 18),
(29, 'sepatu', 80, 50000, 80, 0),
(30, 'sandal', 50, 15000, 10, 40),
(31, 'kursor', 17, 50000, 17, 0),
(32, 'ID card', 48, 5000, 40, 8),
(33, 'carger', 130, 80000, 80, 50),
(34, 'laptop', 11, 5000000, 5, 6),
(35, 'AC', 30, 300000, 30, 0),
(36, 'penghapus', 50, 5000, 50, 0),
(37, 'baju', 6, 20000, 6, 0),
(38, 'tot bag', 80, 10000, 80, 0),
(39, 'jam dinding', 50, 200000, 50, 0),
(40, 'lampu belajar', 20, 70000, 20, 0),
(41, 'CCTV', 40, 2000000, 40, 0),
(42, 'papan', 18, 200000, 18, 0),
(43, 'pisau', 35, 10000, 35, 0),
(44, 'bangku', 15, 30000, 15, 0),
(45, 'headphone', 20, 50000, 20, 0),
(46, 'kertas F4', 20, 40000, 20, 0),
(47, 'papan', 2, 70000, 2, 0),
(48, 'mukena', 60, 100000, 60, 0),
(49, 'papan', 3, 50000, 3, 0),
(50, 'carger', 5, 50000, 5, 0),
(51, 'celana', 3, 40000, 3, 0),
(52, 'celana', 2, 30000, 2, 0),
(53, 'kaos kaki', 24, 10000, 24, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_user` int NOT NULL,
  `id_barang` int NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `status` enum('keluar','masuk') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_barang`, `tanggal_transaksi`, `status`, `jumlah`) VALUES
(33, 5, 33, '2026-01-23', 'keluar', 0),
(34, 5, 33, '2026-01-23', 'keluar', 0),
(35, 5, 22, '2026-01-23', 'keluar', 0),
(36, 5, 23, '2026-01-24', 'masuk', 0),
(37, 5, 32, '2026-01-24', 'keluar', 0),
(38, 5, 31, '2026-01-24', 'keluar', 0),
(39, 5, 30, '2026-01-24', 'masuk', 0),
(40, 5, 30, '2026-01-24', 'keluar', 0),
(41, 5, 34, '2026-01-24', 'masuk', 0),
(42, 5, 23, '2026-01-24', 'masuk', 0),
(43, 5, 28, '2026-02-02', 'masuk', 0),
(44, 5, 34, '2026-02-02', 'masuk', 0),
(45, 5, 33, '2026-02-02', 'keluar', 0),
(46, 5, 35, '2026-02-02', 'masuk', 0),
(47, 5, 32, '2026-02-02', 'keluar', 0),
(48, 5, 36, '2026-02-02', 'masuk', 0),
(49, 5, 37, '2026-02-02', 'masuk', 0),
(54, 8, 40, '2026-02-03', 'masuk', 0),
(55, 8, 36, '2026-02-03', 'keluar', 0),
(56, 8, 37, '2026-02-03', 'keluar', 0),
(57, 8, 36, '2026-02-03', 'keluar', 0),
(58, 5, 41, '2026-02-03', 'masuk', 0),
(59, 5, 41, '2026-02-03', 'keluar', 0),
(60, 5, 42, '2026-02-04', 'masuk', 0),
(61, 5, 41, '2026-02-04', 'keluar', 0),
(62, 5, 42, '2026-02-06', 'masuk', 0),
(63, 5, 42, '2026-02-06', 'masuk', 0),
(64, 5, 43, '2026-02-06', 'masuk', 30),
(65, 5, 43, '2026-02-06', 'masuk', 5),
(66, 5, 41, '2026-02-07', 'keluar', 5),
(67, 5, 44, '2026-02-07', 'masuk', 40),
(68, 5, 45, '2026-02-23', 'masuk', 30),
(69, 5, 44, '2026-02-23', 'keluar', 20),
(70, 5, 46, '2026-02-23', 'masuk', 20),
(71, 5, 47, '2026-02-23', 'masuk', 2),
(72, 5, 44, '2026-02-23', 'keluar', 2),
(73, 5, 44, '2026-02-23', 'keluar', 3),
(74, 5, 48, '2026-02-23', 'masuk', 60),
(75, 5, 49, '2026-02-24', 'masuk', 3),
(76, 5, 41, '2026-02-24', 'keluar', 5),
(77, 5, 50, '2026-02-24', 'masuk', 5),
(78, 5, 45, '2026-02-25', 'keluar', 10),
(79, 5, 51, '2026-02-24', 'masuk', 3),
(80, 5, 33, '2026-02-25', 'masuk', 30),
(81, 5, 52, '2026-02-25', 'masuk', 2),
(82, 5, 41, '2026-02-25', 'keluar', 5),
(83, 5, 53, '2026-02-25', 'masuk', 21),
(84, 5, 37, '2026-02-25', 'masuk', 6),
(85, 5, 53, '2026-02-25', 'masuk', 3),
(86, 5, 32, '2026-02-25', 'keluar', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gmail` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','petugas') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `gmail`, `password`, `role`) VALUES
(5, 'fitriah', 'fitriahsukri04@gmail.com', '1ce6c77c594888b348a2d7213a060da1', 'admin'),
(8, 'ningsih', 'rahma@gmail.com', '4d5901dfa71f941edebe67b6047f537c', 'petugas');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
