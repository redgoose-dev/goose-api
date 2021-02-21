set SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- table `apps`
create table `goose_apps` (
  `srl` tinyint(11) not null,
  `user_srl` smallint(11) default null,
  `id` varchar(20) default null,
  `name` varchar(30) default null,
  `description` varchar(100) default null,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_apps` add primary key (`srl`), add unique key `id` (`id`);
alter table `goose_apps` modify `srl` tinyint(11) not null auto_increment;


-- table `articles`
create table `goose_articles` (
  `srl` int(11) not null,
  `app_srl` tinyint(11) default null,
  `nest_srl` smallint(11) default null,
  `category_srl` int(11) default null,
  `user_srl` smallint(11) default null,
  `title` varchar(255) default null,
  `content` longtext,
  `hit` int(11) default 0,
  `star` int(11) default 0,
  `json` text,
  `ip` varchar(15) default null,
  `type` varchar(20) default null,
  `regdate` datetime default null,
  `modate` datetime default null,
  `order` date default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_articles` add primary key (`srl`);
alter table `goose_articles` modify `srl` int(11) not null auto_increment;


-- table `categories`
create table `goose_categories` (
  `srl` int(11) not null,
  `nest_srl` smallint(11) default null,
  `user_srl` smallint(11) default null,
  `turn` int(11) default 0,
  `name` varchar(42) default null,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_categories` add primary key (`srl`);
alter table `goose_categories` modify `srl` int(11) not null auto_increment;


-- table `files`
create table `goose_files` (
  `srl` int(11) not null,
  `target_srl` int(11) default null,
  `user_srl` smallint(11) default null,
  `name` varchar(120) default null,
  `path` varchar(255) default null,
  `type` varchar(40) default null,
  `size` bigint(11) default 0,
  `regdate` datetime default null,
  `module` varchar(20) default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_files` add primary key (`srl`);
alter table `goose_files` modify `srl` int(11) not null auto_increment;


-- table `json`
create table `goose_json` (
  `srl` smallint(11) not null,
  `user_srl` smallint(11) default null,
  `name` varchar(50) default null,
  `description` varchar(100) default null,
  `json` mediumtext,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_json` add primary key (`srl`);
alter table `goose_json` modify `srl` smallint(11) not null auto_increment;


-- table `nests`
create table `goose_nests` (
  `srl` smallint(11) not null,
  `app_srl` tinyint(11) default null,
  `user_srl` smallint(11) default null,
  `id` varchar(20) default null,
  `name` varchar(40) default null,
  `description` varchar(100) default null,
  `json` text,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_nests` add primary key (`srl`), add unique key `id` (`id`);
alter table `goose_nests` modify `srl` smallint(11) not null auto_increment;


-- table `users`
create table `goose_users` (
  `srl` smallint(11) not null,
  `email` varchar(60) default null,
  `name` varchar(30) default null,
  `password` varchar(100) default null,
  `admin` tinyint(1) default 1 not null,
  `json` text,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_users` add primary key (`srl`), add unique key `email` (`email`);
alter table `goose_users` modify `srl` smallint(11) not null auto_increment;


-- table `tokens`
create table `goose_tokens` (
  `srl` smallint(11) not null,
  `token` varchar(100) default null,
  `expired` int(11) default null,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_tokens` add primary key (`srl`), add unique key `token` (`token`);
alter table `goose_tokens` modify `srl` smallint(11) not null auto_increment;


-- table `comments`
create table `goose_comments` (
  `srl` int(11) not null,
  `article_srl` int(11) default null,
  `user_srl` smallint(11) default null,
  `content` text,
  `regdate` datetime default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_comments` add primary key (`srl`);
alter table `goose_comments` modify `srl` int(11) not null auto_increment;


-- table `checklist`
create table `goose_checklist` (
  `srl` int(11) not null,
  `user_srl` smallint(11) default null,
  `content` text,
  `percent` tinyint(3) unsigned default 0,
  `regdate` date default null
) engine=InnoDB default charset=utf8mb4;
alter table `goose_checklist` add primary key (`srl`), add unique key `regdate` (`regdate`);
alter table `goose_checklist` modify `srl` int(11) not null auto_increment;
