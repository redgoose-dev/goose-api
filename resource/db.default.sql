SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- table `apps`
CREATE TABLE `goose_apps` (
  `srl` tinyint(11) NOT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `id` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_apps` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `goose_apps` MODIFY `srl` tinyint(11) NOT NULL AUTO_INCREMENT;


-- table `articles`
CREATE TABLE `goose_articles` (
  `srl` int(11) NOT NULL,
  `app_srl` tinyint(11) DEFAULT NULL,
  `nest_srl` smallint(11) DEFAULT NULL,
  `category_srl` int(11) DEFAULT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext,
  `hit` int(11) DEFAULT 0,
  `star` int(11) DEFAULT 0,
  `json` text,
  `ip` varchar(15) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `regdate` datetime DEFAULT NULL,
  `modate` datetime DEFAULT NULL,
  `order` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_articles` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_articles` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `categories`
CREATE TABLE `goose_categories` (
  `srl` int(11) NOT NULL,
  `nest_srl` smallint(11) DEFAULT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `turn` int(11) DEFAULT 0,
  `name` varchar(42) DEFAULT NULL,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_categories` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_categories` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `files`
CREATE TABLE `goose_files` (
  `srl` int(11) NOT NULL,
  `article_srl` int(11) DEFAULT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `loc` varchar(255) DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  `size` bigint(11) DEFAULT 0,
  `regdate` datetime DEFAULT NULL,
  `ready` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_files` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_files` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;


-- table `json`
CREATE TABLE `goose_json` (
  `srl` smallint(11) NOT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `json` mediumtext,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_json` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_json` MODIFY `srl` smallint(11) NOT NULL AUTO_INCREMENT;


-- table `nests`
CREATE TABLE `goose_nests` (
  `srl` smallint(11) NOT NULL,
  `app_srl` tinyint(11) DEFAULT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `id` varchar(20) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `json` text,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_nests` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `goose_nests` MODIFY `srl` smallint(11) NOT NULL AUTO_INCREMENT;


-- table `users`
CREATE TABLE `goose_users` (
  `srl` smallint(11) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT 1 NOT NULL,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_users` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `goose_users` MODIFY `srl` smallint(11) NOT NULL AUTO_INCREMENT;


-- table `tokens`
CREATE TABLE `goose_tokens` (
  `srl` smallint(11) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `expired` int(11) DEFAULT NULL,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_tokens` ADD PRIMARY KEY (`srl`), ADD UNIQUE KEY `token` (`token`);
ALTER TABLE `goose_tokens` MODIFY `srl` smallint(11) NOT NULL AUTO_INCREMENT;


-- table `comments`
CREATE TABLE `goose_comments` (
  `srl` int(11) NOT NULL,
  `article_srl` int(11) DEFAULT NULL,
  `user_srl` smallint(11) DEFAULT NULL,
  `content` text,
  `regdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `goose_comments` ADD PRIMARY KEY (`srl`);
ALTER TABLE `goose_comments` MODIFY `srl` int(11) NOT NULL AUTO_INCREMENT;
