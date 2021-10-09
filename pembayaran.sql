-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2021 at 10:14 AM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pembayaran`
--

-- --------------------------------------------------------

--
-- Table structure for table `log_pembayaran`
--

CREATE TABLE `log_pembayaran` (
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `tanggal_pembayaran` date NOT NULL DEFAULT current_timestamp(),
  `nominal_pembayaran` int(255) NOT NULL,
  `SystemTraceAuditNumber` varchar(6) NOT NULL,
  `RetrievalReferenceNumber` varchar(12) NOT NULL,
  `Message` text NOT NULL,
  `ResponseMessage` text NOT NULL,
  `PaymentStatus` tinyint(1) NOT NULL DEFAULT 0,
  `issuer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporary`
--

CREATE TABLE `temporary` (
  `RRN` varchar(255) NOT NULL,
  `CVV` varchar(255) NOT NULL,
  `exp` varchar(255) NOT NULL,
  `cnumber` varchar(255) NOT NULL,
  `Issuer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_pembayaran`
--
ALTER TABLE `log_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_pembayaran` (`id_pembayaran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_pembayaran`
--
ALTER TABLE `log_pembayaran`
  MODIFY `id_pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
