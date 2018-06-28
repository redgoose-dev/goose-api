-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 28, 2018 at 05:02 PM
-- Server version: 5.6.38
-- PHP Version: 7.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `goose`
--

-- --------------------------------------------------------

--
-- Table structure for table `goose_app`
--

CREATE TABLE `goose_app` (
  `srl` int(11) NOT NULL,
  `id` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_article`
--

CREATE TABLE `goose_article` (
  `srl` bigint(11) NOT NULL,
  `app_srl` int(11) DEFAULT NULL,
  `nest_srl` int(11) DEFAULT NULL,
  `category_srl` int(11) DEFAULT NULL,
  `user_srl` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext,
  `hit` int(11) DEFAULT NULL,
  `json` text,
  `ip` varchar(15) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `modate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_category`
--

CREATE TABLE `goose_category` (
  `srl` int(11) NOT NULL,
  `nest_srl` int(11) DEFAULT NULL,
  `turn` int(11) DEFAULT NULL,
  `name` varchar(42) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_file`
--

CREATE TABLE `goose_file` (
  `srl` int(11) NOT NULL,
  `article_srl` bigint(11) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `loc` varchar(255) DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  `size` bigint(11) DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  `ready` int(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_json`
--

CREATE TABLE `goose_json` (
  `srl` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `json` mediumtext,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_nest`
--

CREATE TABLE `goose_nest` (
  `srl` int(11) NOT NULL,
  `app_srl` int(11) DEFAULT NULL,
  `id` varchar(30) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `json` text,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goose_user`
--

CREATE TABLE `goose_user` (
  `srl` int(11) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `pw` varchar(100) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `goose_app`
--
ALTER TABLE `goose_app`
  ADD PRIMARY KEY (`srl`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `goose_article`
--
ALTER TABLE `goose_article`
  ADD PRIMARY KEY (`srl`);

--
-- Indexes for table `goose_category`
--
ALTER TABLE `goose_category`
  ADD PRIMARY KEY (`srl`);

--
-- Indexes for table `goose_file`
--
ALTER TABLE `goose_file`
  ADD PRIMARY KEY (`srl`);

--
-- Indexes for table `goose_json`
--
ALTER TABLE `goose_json`
  ADD PRIMARY KEY (`srl`);

--
-- Indexes for table `goose_nest`
--
ALTER TABLE `goose_nest`
  ADD PRIMARY KEY (`srl`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `goose_user`
--
ALTER TABLE `goose_user`
  ADD PRIMARY KEY (`srl`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `goose_app`
--
ALTER TABLE `goose_app`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `goose_article`
--
ALTER TABLE `goose_article`
  MODIFY `srl` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `goose_category`
--
ALTER TABLE `goose_category`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `goose_file`
--
ALTER TABLE `goose_file`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `goose_json`
--
ALTER TABLE `goose_json`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `goose_nest`
--
ALTER TABLE `goose_nest`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `goose_user`
--
ALTER TABLE `goose_user`
  MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
