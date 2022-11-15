set SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- table `apps`
create table `goose_apps` (
  `srl` tinyint(4) unsigned not null auto_increment,
  `user_srl` smallint(6) unsigned default null,
  `id` varchar(24) default null,
  `name` varchar(30) default null,
  `description` varchar(100) default null,
  `regdate` datetime default null,
  primary key (`srl`),
  unique key `id` (`id`),
  key `apps_side_srl` (`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `articles`
create table `goose_articles` (
  `srl` int(11) unsigned not null auto_increment,
  `app_srl` tinyint(4) unsigned default null,
  `nest_srl` smallint(6) unsigned default null,
  `category_srl` mediumint(9) unsigned default null,
  `user_srl` smallint(6) unsigned default null,
  `title` varchar(255) default null,
  `content` longtext,
  `hit` int(11) unsigned default 0,
  `star` int(11) unsigned default 0,
  `json` longtext,
  `ip` varchar(15) default null,
  `type` varchar(20) default null,
  `regdate` datetime default null,
  `modate` datetime default null,
  `order` date default null,
  primary key (`srl`),
  key `articles_order_asc` (`order`),
  key `articles_order_desc` (`order` desc),
  key `articles_side_srl` (`app_srl`,`nest_srl`,`category_srl`,`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `categories`
create table `goose_categories` (
  `srl` mediumint(9) unsigned not null auto_increment,
  `target_srl` int(11) unsigned default null,
  `user_srl` smallint(6) unsigned default null,
  `turn` smallint(6) unsigned default 0,
  `name` varchar(40) default null,
  `module` varchar(16) not null,
  `regdate` datetime default null,
  primary key (`srl`),
  key `categories_module` (`module`),
  key `categories_side_srl` (`target_srl`,`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `checklist`
create table `goose_checklist` (
  `srl` int(11) unsigned not null auto_increment,
  `user_srl` smallint(6) unsigned default null,
  `content` text,
  `percent` tinyint(3) unsigned default 0,
  `regdate` datetime default null,
  primary key (`srl`),
  unique key `regdate` (`regdate`),
  key `checklist_side_srl` (`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `comments`
create table `goose_comments` (
  `srl` int(11) unsigned not null auto_increment,
  `article_srl` int(11) unsigned default null,
  `user_srl` smallint(6) unsigned default null,
  `content` text,
  `regdate` datetime default null,
  primary key (`srl`),
  key `comments_side_srl` (`article_srl`,`user_srl`),
  key `comments_regdate` (`regdate` desc)
) engine=InnoDB default charset=utf8mb4;


-- table `files`
create table `goose_files` (
  `srl` int(11) unsigned not null auto_increment,
  `target_srl` int(11) unsigned default null,
  `user_srl` smallint(6) unsigned default null,
  `name` varchar(120) default null,
  `path` varchar(255) default null,
  `type` varchar(40) default null,
  `size` bigint(11) unsigned default 0,
  `module` varchar(16) not null,
  `regdate` datetime default null,
  primary key (`srl`),
  key `files_module` (`module`),
  key `files_side_srl` (`target_srl`,`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `json`
create table `goose_json` (
  `srl` int(11) unsigned not null auto_increment,
  `user_srl` smallint(6) unsigned default null,
  `category_srl` mediumint(9) unsigned default null,
  `name` varchar(50) not null,
  `description` varchar(100) default null,
  `json` longtext not null,
  `path` varchar(255) default null,
  `regdate` datetime default null,
  primary key (`srl`),
  key `json_side_srl` (`user_srl`,`category_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `nests`
create table `goose_nests` (
  `srl` smallint(6) unsigned not null auto_increment,
  `app_srl` tinyint(4) unsigned default null,
  `user_srl` smallint(6) unsigned default null,
  `id` varchar(24) not null,
  `name` varchar(40) default null,
  `description` varchar(100) default null,
  `json` text,
  `regdate` datetime default null,
  primary key (`srl`),
  unique key `id` (`id`),
  key `nests_side_srl` (`app_srl`,`user_srl`)
) engine=InnoDB default charset=utf8mb4;


-- table `tokens`
create table `goose_tokens` (
  `srl` smallint(6) unsigned not null auto_increment,
  `token` varchar(100) not null,
  `expired` int(11) unsigned default null,
  `regdate` datetime default null,
  primary key (`srl`),
  unique key `token` (`token`)
) engine=InnoDB default charset=utf8mb4;


-- table `users`
create table `goose_users` (
  `srl` smallint(5) unsigned not null auto_increment,
  `email` varchar(60) not null,
  `name` varchar(30) not null,
  `password` varchar(100) not null,
  `admin` tinyint(3) unsigned not null default 1,
  `json` text default null,
  `regdate` datetime default null,
  primary key (`srl`),
  unique key `users_email` (`email`),
  key `users_admin` (`admin`)
) engine=InnoDB default charset=utf8mb4;
