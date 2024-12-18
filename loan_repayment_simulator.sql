-- PHPMyAdmin SQL Dump for MySQL Compatibility
-- Version: MySQL

-- Database: `loan_repayment_simulator`
CREATE DATABASE IF NOT EXISTS `loan_repayment_simulator` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `loan_repayment_simulator`;

-- --------------------------------------------------------

-- Table structure for table `additional_payments`
DROP TABLE IF EXISTS `additional_payments`;
CREATE TABLE `additional_payments` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `payment_ID` bigint(20) NOT NULL,
  `date_additional_payment` date NOT NULL,
  `amount_additional_payments` float NOT NULL,
  PRIMARY KEY (`payment_ID`),
  KEY `fk_additional` (`ID_user`, `DB_set`),
  CONSTRAINT `fk_additional` FOREIGN KEY (`ID_user`, `DB_set`) REFERENCES `starting_loan_values` (`ID_user`, `DB_set`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `additional_payments`
INSERT INTO `additional_payments` (`ID_user`, `DB_set`, `payment_ID`, `date_additional_payment`, `amount_additional_payments`) VALUES
(1, 1, 1, '2024-11-04', 4050),
(1, 1, 2, '2024-12-25', 3434);

-- --------------------------------------------------------

-- Table structure for table `interest_repayments`
DROP TABLE IF EXISTS `interest_repayments`;
CREATE TABLE `interest_repayments` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `interest_ID` bigint(20) NOT NULL,
  `date_interest` date NOT NULL,
  `new_val_interest` float NOT NULL,
  PRIMARY KEY (`interest_ID`),
  KEY `fk_interest` (`ID_user`, `DB_set`),
  CONSTRAINT `fk_interest` FOREIGN KEY (`ID_user`, `DB_set`) REFERENCES `starting_loan_values` (`ID_user`, `DB_set`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `interest_repayments`
INSERT INTO `interest_repayments` (`ID_user`, `DB_set`, `interest_ID`, `date_interest`, `new_val_interest`) VALUES
(1, 1, 1, '2024-11-17', 6.39),
(1, 1, 2, '2027-11-16', 7),
(2, 1, 1, '2024-11-17', 6.38);

-- --------------------------------------------------------

-- Table structure for table `starting_loan_values`
DROP TABLE IF EXISTS `starting_loan_values`;
CREATE TABLE `starting_loan_values` (
  `ID_user` bigint(20) NOT NULL,
  `DB_set` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `start_interest` float NOT NULL,
  `start_principle` float NOT NULL,
  `duration_years` int(11) NOT NULL,
  `payment_interval` varchar(15) NOT NULL,
  PRIMARY KEY (`ID_user`, `DB_set`),
  CONSTRAINT `starting_loan_values_ibfk_1` FOREIGN KEY (`ID_user`) REFERENCES `user_accounts` (`user_ID`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `starting_loan_values`
INSERT INTO `starting_loan_values` (`ID_user`, `DB_set`, `start_date`, `start_interest`, `start_principle`, `duration_years`, `payment_interval`) VALUES
(1, 1, '2024-11-06', 5.6, 75000, 30, 'Monthly'),
(1, 2, '2024-12-11', 6.44, 100000, 30, 'Monthly'),
(2, 1, '2023-10-18', 6.13, 325407, 30, 'Fortnightly');

-- --------------------------------------------------------

-- Table structure for table `user_accounts`
DROP TABLE IF EXISTS `user_accounts`;
CREATE TABLE `user_accounts` (
  `user_ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `password` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `user_accounts`
INSERT INTO `user_accounts` (`user_ID`, `username`, `password`) VALUES
(1, 'test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'),
(2, 'morgan', '3f122fd6ff97ebdcef91e2e1af20b136397fc6442af86be4ebc20114eab8fa91');

-- Indexes for dumped tables
-- Indexes for table `additional_payments`
-- The foreign key index already exists in the table definition

-- Indexes for table `interest_repayments`
-- The foreign key index already exists in the table definition

-- Indexes for table `starting_loan_values`
-- The primary key already exists in the table definition

-- Indexes for table `user_accounts`
-- The primary key already exists in the table definition
