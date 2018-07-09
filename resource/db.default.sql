SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- table `app`
CREATE TABLE `goose_app` (
  `srl` int(11) NOT NULL,
  `id` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_app` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `goose_app` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `article`
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

ALTER TABLE `goose_article` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_article` MODIFY `srl` bigint(11) NOT NULL AUTO_INCREMENT;


-- table `category`
CREATE TABLE `goose_category` (
  `srl` int(11) NOT NULL,
  `nest_srl` int(11) DEFAULT NULL,
  `turn` int(11) DEFAULT NULL,
  `name` varchar(42) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_category` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_category` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `file`
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

ALTER TABLE `goose_file` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_file` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `json`
CREATE TABLE `goose_json` (
  `srl` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `json` mediumtext,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_json` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_json` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `nest`
CREATE TABLE `goose_nest` (
  `srl` int(11) NOT NULL,
  `app_srl` int(11) DEFAULT NULL,
  `id` varchar(30) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `json` text,
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_nest` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `goose_nest` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `user`
CREATE TABLE `goose_user` (
  `srl` int(11) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `pw` varchar(100) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_user` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `goose_user` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `token`
CREATE TABLE `goose_token` (
  `srl` int(11) NOT NULL,
  `token` varchar(80) DEFAULT NULL,
  `expired` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_token` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `token` (`token`);
ALTER TABLE `goose_token` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;
