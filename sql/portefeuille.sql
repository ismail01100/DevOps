-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 05:22 PM
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
-- Database: `db`
--

-- --------------------------------------------------------

--
-- Table structure for table `portefeuille`
--

CREATE TABLE `portefeuille` (
  `CodePortefeuille` int(11) NOT NULL,
  `CodeUtilisateur` int(11) DEFAULT NULL,
  `Salaire` float DEFAULT NULL,
  `Solde` float DEFAULT NULL,
  `LastResetDate` date DEFAULT NULL,
  `SavingPourcentage` float DEFAULT NULL,
  `TotalIncome` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `portefeuille`
--
ALTER TABLE `portefeuille`
  ADD PRIMARY KEY (`CodePortefeuille`),
  ADD KEY `CodeUtilisateur` (`CodeUtilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `portefeuille`
--
ALTER TABLE `portefeuille`
  MODIFY `CodePortefeuille` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `portefeuille`
--
ALTER TABLE `portefeuille`
  ADD CONSTRAINT `portefeuille_ibfk_1` FOREIGN KEY (`CodeUtilisateur`) REFERENCES `users` (`CodeUtilisateur`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
