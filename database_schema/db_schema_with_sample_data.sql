-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2017 at 11:33 AM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `expense`
--

-- --------------------------------------------------------

--
-- Table structure for table `cash_manager_app_access_level`
--

CREATE TABLE `cash_manager_app_access_level` (
  `cash_manager_app_access_level_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `approval_required` tinyint(1) NOT NULL DEFAULT '0',
  `cashbox_access` tinyint(1) NOT NULL DEFAULT '0',
  `permission_type` int(3) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `cash_manager_app_access_level`
--

INSERT INTO `cash_manager_app_access_level` (`cash_manager_app_access_level_id`, `user_group_id`, `approval_required`, `cashbox_access`, `permission_type`) VALUES
(1, 1, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cash_manager_app_to_options`
--

CREATE TABLE `cash_manager_app_to_options` (
  `cash_manager_app_to_options_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL DEFAULT '1',
  `flow_type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description_required` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cash_manager_app_to_options`
--

INSERT INTO `cash_manager_app_to_options` (`cash_manager_app_to_options_id`, `app_id`, `flow_type_id`, `name`, `description_required`, `status`) VALUES
(1, 1, 2, 'Auto', 0, 1),
(2, 1, 2, 'Rickshaw', 0, 1),
(3, 1, 1, 'Shop', 1, 1),
(4, 1, 6, 'Miscellaneous', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cash_manager_app_to_options_variant`
--

CREATE TABLE `cash_manager_app_to_options_variant` (
  `cash_manager_app_to_options_variant_id` int(11) NOT NULL,
  `cash_manager_app_to_options_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_transactions_record`
--

CREATE TABLE `cash_transactions_record` (
  `cash_transaction_id` int(11) NOT NULL,
  `cash_transaction_master_id` int(11) NOT NULL,
  `flow_type` int(3) NOT NULL,
  `transaction_type` int(3) NOT NULL,
  `flow_type_id` int(5) NOT NULL,
  `transaction_from` int(11) NOT NULL,
  `transaction_to` int(11) NOT NULL,
  `referrence_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `remark` text NOT NULL,
  `transaction_date` date NOT NULL,
  `is_primary_transaction` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `real_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_transaction_approval`
--

CREATE TABLE `cash_transaction_approval` (
  `cash_transaction_approval_id` int(11) NOT NULL,
  `pocket_history_id` int(11) NOT NULL,
  `transaction_from` int(11) NOT NULL,
  `transaction_to` int(11) NOT NULL,
  `transaction_type` int(3) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `data` text NOT NULL,
  `approval_status` int(3) NOT NULL DEFAULT '1',
  `approved_by` int(11) NOT NULL,
  `reject_reason` text NOT NULL,
  `comment` text NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_transaction_master_record`
--

CREATE TABLE `cash_transaction_master_record` (
  `cash_transaction_master_id` int(11) NOT NULL,
  `cash_transaction_from_type` int(3) NOT NULL DEFAULT '0',
  `referrence_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_transaction_types`
--

CREATE TABLE `cash_transaction_types` (
  `cash_transaction_type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cash_transaction_types`
--

INSERT INTO `cash_transaction_types` (`cash_transaction_type_id`, `name`) VALUES
(1, 'Internal Pocket Transfer'),
(2, 'Cashbox to Internal Pocket Transfer'),
(3, 'Internal Pocket to Cashbox Transfer'),
(4, 'Internal To External Pocket Transfer'),
(5, 'Internal Pocket Expense'),
(6, 'System to Internal Pocket Transfer'),
(7, 'Internal Pocket to System Transfer'),
(8, 'Internal To External Pocket Transfer Correction'),
(9, 'Internal Pocket Expense Correction');

-- --------------------------------------------------------

--
-- Table structure for table `distributor`
--

CREATE TABLE `distributor` (
  `distributor_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `address_1` varchar(1024) NOT NULL,
  `area` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `telephone` varchar(15) NOT NULL,
  `alt_telephone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `pocket_status` int(3) NOT NULL DEFAULT '0',
  `pocket` decimal(11,2) NOT NULL,
  `sort_order` int(5) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `distributor`
--

INSERT INTO `distributor` (`distributor_id`, `name`, `company`, `address_1`, `area`, `city`, `telephone`, `alt_telephone`, `email`, `contact_name`, `contact_number`, `pocket_status`, `pocket`, `sort_order`) VALUES
(1, 'Demo', 'Demo Pvt. Ltd.', 'Demo House', 'Demo Area', 'Demo City', '1234567890', '1234567890', 'demo@demo.demo', 'Demo Demo', '9870000000', 1, '0.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `distributor_pocket_history`
--

CREATE TABLE `distributor_pocket_history` (
  `distributor_pocket_history_id` int(11) NOT NULL,
  `distributor_id` int(11) NOT NULL,
  `cash_transactions_id` int(11) NOT NULL,
  `transaction_type` int(3) NOT NULL,
  `flow_type_id` int(11) NOT NULL,
  `transaction_from` int(11) NOT NULL,
  `transaction_to` int(11) NOT NULL,
  `previous_amount` decimal(11,2) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance_amount` decimal(11,2) NOT NULL,
  `mop` int(3) NOT NULL,
  `inventory_log_id` int(11) NOT NULL,
  `cash_outflow_id` int(11) NOT NULL,
  `approval_status` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `flow_type`
--

CREATE TABLE `flow_type` (
  `flow_type_id` int(11) NOT NULL,
  `flow_type` int(3) NOT NULL,
  `team` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `flow_type`
--

INSERT INTO `flow_type` (`flow_type_id`, `flow_type`, `team`, `name`, `sort_order`) VALUES
(1, 0, 1, 'Inventory Purchase', 0),
(2, 0, 1, 'Logistics [Delivery Cost]', 1),
(3, 0, 1, 'Logistics [Source Cost]', 2),
(4, 0, 1, 'Packaging', 3),
(5, 0, 1, 'Printing', 4),
(6, 0, 1, 'Miscellaneous', 5),
(7, 0, 2, 'Referral Wallet', 6),
(8, 0, 2, 'Coupon', 7),
(9, 0, 2, 'Gifts', 8),
(10, 0, 2, 'Promotion', 9),
(11, 0, 2, 'Miscellaneous', 10),
(12, 0, 3, 'Miscellaneous', 11),
(13, 1, 0, 'Sales Collection', 12),
(14, 1, 0, 'Capital Addition', 13),
(15, 0, 1, 'Assigned', 14),
(16, 0, 0, 'Cashbox Increment', 15),
(17, 0, 1, 'Deposit to Casbox', 16),
(18, 1, 0, 'Deposited to Cashbox', 17),
(19, 0, 0, 'Inventory Deleted', 18),
(20, 0, 1, 'External Pocket clearance', 19),
(21, 0, 0, 'Inventory Returned', 20),
(22, 0, 0, 'Withdraw from Cashbox', 21),
(23, 1, 1, 'Wallet Used for Delivery Payment', 22),
(24, 1, 1, 'Delivery Payment Due', 23),
(25, 0, 1, 'Extra Amount Collected at Delivery', 24),
(26, 0, 1, 'Pending Delivery Amount Clearance', 25),
(27, 0, 1, 'Refund/Return', 26),
(28, 0, 0, 'Error Correction', 27),
(29, 1, 0, 'Customer Wallet Credited by Admin', 28),
(30, 0, 0, 'Initial Setup', 29);

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `setting_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `group` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `serialized` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`setting_id`, `store_id`, `group`, `key`, `value`, `serialized`) VALUES
(11374, 0, 'cash_management', 'cash_box', '0', 0),
(11375, 0, 'cash_management', 'cash_box_id', '1000000', 0),
(11376, 0, 'cash_management', 'total_cash', '0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(40) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notification_auto_refresh` int(11) NOT NULL DEFAULT '0',
  `wallet_access` int(5) NOT NULL DEFAULT '0',
  `cash_access` int(3) NOT NULL DEFAULT '0',
  `pocket` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_group_id`, `username`, `password`, `firstname`, `lastname`, `email`, `telephone`, `code`, `ip`, `status`, `date_added`, `notification_auto_refresh`, `wallet_access`, `cash_access`, `pocket`) VALUES
(1, 1, 'demo', 'fe01ce2a7fbac8fafaed7c982a04e229', 'Demo', 'Demo', 'demo@demo.demo', '9870000000', '', '127.0.0.1', 1, '2016-09-15 06:37:45', 0, 1, 1, '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `user_app_data`
--

CREATE TABLE `user_app_data` (
  `user_app_id` int(11) NOT NULL,
  `app_id` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_app_data`
--

INSERT INTO `user_app_data` (`user_app_id`, `app_id`, `user_id`, `token`, `status`, `date_added`, `date_modified`) VALUES
(1, '1', 1, '314e3af338542e28ccaceb25b5aab9c6529f4159e82d3ebf108042a42026938e', 0, '2017-03-07 19:49:00', '2017-03-08 01:04:30'),
(2, '1', 1, 'd39011d9ebe9ed45c3d9744848d0f7e557ba0b6feba9dcb58900850c6665e455', 0, '2017-03-08 01:04:30', '2017-03-08 01:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_expenses`
--

CREATE TABLE `user_expenses` (
  `user_expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cash_transactions_id` int(11) NOT NULL,
  `transaction_type` int(3) NOT NULL,
  `cash_manager_app_to_options_id` int(11) NOT NULL,
  `cash_manager_app_to_options_variant_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `transaction_date` date NOT NULL,
  `added_by` int(11) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `user_group_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `permission` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Top Administrator', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_pocket_history`
--

CREATE TABLE `user_pocket_history` (
  `user_pocket_history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cash_transactions_id` int(11) NOT NULL,
  `transaction_type` int(3) NOT NULL,
  `previous_amount` decimal(11,2) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance_amount` decimal(11,2) NOT NULL,
  `approval_status` tinyint(1) NOT NULL DEFAULT '0',
  `added_by` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cash_manager_app_access_level`
--
ALTER TABLE `cash_manager_app_access_level`
  ADD PRIMARY KEY (`cash_manager_app_access_level_id`);

--
-- Indexes for table `cash_manager_app_to_options`
--
ALTER TABLE `cash_manager_app_to_options`
  ADD PRIMARY KEY (`cash_manager_app_to_options_id`);

--
-- Indexes for table `cash_manager_app_to_options_variant`
--
ALTER TABLE `cash_manager_app_to_options_variant`
  ADD PRIMARY KEY (`cash_manager_app_to_options_variant_id`);

--
-- Indexes for table `cash_transactions_record`
--
ALTER TABLE `cash_transactions_record`
  ADD PRIMARY KEY (`cash_transaction_id`);

--
-- Indexes for table `cash_transaction_approval`
--
ALTER TABLE `cash_transaction_approval`
  ADD PRIMARY KEY (`cash_transaction_approval_id`);

--
-- Indexes for table `cash_transaction_master_record`
--
ALTER TABLE `cash_transaction_master_record`
  ADD PRIMARY KEY (`cash_transaction_master_id`);

--
-- Indexes for table `cash_transaction_types`
--
ALTER TABLE `cash_transaction_types`
  ADD PRIMARY KEY (`cash_transaction_type_id`);

--
-- Indexes for table `distributor`
--
ALTER TABLE `distributor`
  ADD PRIMARY KEY (`distributor_id`);

--
-- Indexes for table `distributor_pocket_history`
--
ALTER TABLE `distributor_pocket_history`
  ADD PRIMARY KEY (`distributor_pocket_history_id`);

--
-- Indexes for table `flow_type`
--
ALTER TABLE `flow_type`
  ADD PRIMARY KEY (`flow_type_id`);

--
-- Indexes for table `setting`
--
ALTER TABLE `setting`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_app_data`
--
ALTER TABLE `user_app_data`
  ADD PRIMARY KEY (`user_app_id`),
  ADD KEY `user_id` (`user_id`,`status`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `token` (`token`(255),`status`);

--
-- Indexes for table `user_expenses`
--
ALTER TABLE `user_expenses`
  ADD PRIMARY KEY (`user_expense_id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`user_group_id`);

--
-- Indexes for table `user_pocket_history`
--
ALTER TABLE `user_pocket_history`
  ADD PRIMARY KEY (`user_pocket_history_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cash_manager_app_access_level`
--
ALTER TABLE `cash_manager_app_access_level`
  MODIFY `cash_manager_app_access_level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `cash_manager_app_to_options`
--
ALTER TABLE `cash_manager_app_to_options`
  MODIFY `cash_manager_app_to_options_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `cash_manager_app_to_options_variant`
--
ALTER TABLE `cash_manager_app_to_options_variant`
  MODIFY `cash_manager_app_to_options_variant_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cash_transactions_record`
--
ALTER TABLE `cash_transactions_record`
  MODIFY `cash_transaction_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cash_transaction_approval`
--
ALTER TABLE `cash_transaction_approval`
  MODIFY `cash_transaction_approval_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cash_transaction_master_record`
--
ALTER TABLE `cash_transaction_master_record`
  MODIFY `cash_transaction_master_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cash_transaction_types`
--
ALTER TABLE `cash_transaction_types`
  MODIFY `cash_transaction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `distributor`
--
ALTER TABLE `distributor`
  MODIFY `distributor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `distributor_pocket_history`
--
ALTER TABLE `distributor_pocket_history`
  MODIFY `distributor_pocket_history_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `flow_type`
--
ALTER TABLE `flow_type`
  MODIFY `flow_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `setting`
--
ALTER TABLE `setting`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11377;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_app_data`
--
ALTER TABLE `user_app_data`
  MODIFY `user_app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_expenses`
--
ALTER TABLE `user_expenses`
  MODIFY `user_expense_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_pocket_history`
--
ALTER TABLE `user_pocket_history`
  MODIFY `user_pocket_history_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
