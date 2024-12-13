-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 04:14 AM
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
-- Database: `loan_repayment_simulator`
--

-- --------------------------------------------------------

--
-- Table structure for table `additional_payments`
--

CREATE TABLE `additional_payments` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `payment_ID` bigint(20) NOT NULL,
  `date_additional_payment` date NOT NULL,
  `amount_additional_payments` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `additional_payments`
--

INSERT INTO `additional_payments` (`ID_user`, `DB_set`, `payment_ID`, `date_additional_payment`, `amount_additional_payments`) VALUES
(1, 1, 1, '2024-11-04', 4050);

-- --------------------------------------------------------

--
-- Table structure for table `interest_repayments`
--

CREATE TABLE `interest_repayments` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `interest_ID` bigint(20) NOT NULL,
  `date_interest` date NOT NULL,
  `new_val_interest` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_repayments`
--

INSERT INTO `interest_repayments` (`ID_user`, `DB_set`, `interest_ID`, `date_interest`, `new_val_interest`) VALUES
(1, 1, 1, '2024-11-17', 6.39),
(1, 1, 2, '2027-11-16', 7);

-- --------------------------------------------------------

--
-- Table structure for table `starting_loan_values`
--

CREATE TABLE `starting_loan_values` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `start_interest` float NOT NULL,
  `start_principle` float NOT NULL,
  `duration_years` int(11) NOT NULL,
  `payment_interval` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `starting_loan_values`
--

INSERT INTO `starting_loan_values` (`ID_user`, `DB_set`, `start_date`, `start_interest`, `start_principle`, `duration_years`, `payment_interval`) VALUES
(1, 1, '2024-11-06', 5.6, 75000, 30, 'Monthly'),
(1, 2, '2024-12-11', 6.44, 100000, 30, 'Monthly');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `user_ID` bigint(20) NOT NULL,
  `username` varchar(16) NOT NULL,
  `password` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` (`user_ID`, `username`, `password`) VALUES
(1, 'test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additional_payments`
--
ALTER TABLE `additional_payments`
  ADD KEY `fk_additional` (`ID_user`,`DB_set`);

--
-- Indexes for table `interest_repayments`
--
ALTER TABLE `interest_repayments`
  ADD KEY `fk_interest` (`ID_user`,`DB_set`);

--
-- Indexes for table `starting_loan_values`
--
ALTER TABLE `starting_loan_values`
  ADD UNIQUE KEY `unique_user_db_set` (`ID_user`,`DB_set`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `user_ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `additional_payments`
--
ALTER TABLE `additional_payments`
  ADD CONSTRAINT `fk_additional` FOREIGN KEY (`ID_user`,`DB_set`) REFERENCES `starting_loan_values` (`ID_user`, `DB_set`) ON DELETE CASCADE;

--
-- Constraints for table `interest_repayments`
--
ALTER TABLE `interest_repayments`
  ADD CONSTRAINT `fk_interest` FOREIGN KEY (`ID_user`,`DB_set`) REFERENCES `starting_loan_values` (`ID_user`, `DB_set`) ON DELETE CASCADE;

--
-- Constraints for table `starting_loan_values`
--
ALTER TABLE `starting_loan_values`
  ADD CONSTRAINT `starting_loan_values_ibfk_1` FOREIGN KEY (`ID_user`) REFERENCES `user_accounts` (`user_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
