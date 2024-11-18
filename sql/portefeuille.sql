-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 07:14 PM
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
-- Table structure for table `charges`
--

CREATE TABLE `charges` (
  `CodeCharge` int(11) NOT NULL,
  `CodePortefeuille` int(11) DEFAULT NULL,
  `NomCharge` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Montant` float NOT NULL,
  `DateCharge` date NOT NULL,
  `Variable` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `CodeUtilisateur` int(11) NOT NULL,
  `Fullname` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
  ADD PRIMARY KEY (`CodeCharge`),
  ADD KEY `CodePortefeuille` (`CodePortefeuille`);

--
-- Indexes for table `portefeuille`
--
ALTER TABLE `portefeuille`
  ADD PRIMARY KEY (`CodePortefeuille`),
  ADD KEY `CodeUtilisateur` (`CodeUtilisateur`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`CodeUtilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
  MODIFY `CodeCharge` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `portefeuille`
--
ALTER TABLE `portefeuille`
  MODIFY `CodePortefeuille` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `CodeUtilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `charges`
--
ALTER TABLE `charges`
  ADD CONSTRAINT `charges_ibfk_1` FOREIGN KEY (`CodePortefeuille`) REFERENCES `portefeuille` (`CodePortefeuille`);

--
-- Constraints for table `portefeuille`
--
ALTER TABLE `portefeuille`
  ADD CONSTRAINT `portefeuille_ibfk_1` FOREIGN KEY (`CodeUtilisateur`) REFERENCES `users` (`CodeUtilisateur`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
