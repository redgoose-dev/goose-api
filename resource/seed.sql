-- table `app`
CREATE TABLE `goose_app` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- app srl
    `code` TEXT NOT NULL UNIQUE, -- app code
    `name` TEXT NULL, -- app name
    `description` TEXT NULL, -- app description
    `created_at` TEXT NOT NULL -- created date
);

-- table `article`
CREATE TABLE `goose_article` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- article srl
    `app_srl` INTEGER NULL, -- app srl
    `nest_srl` INTEGER NULL, -- nest srl
    `category_srl` INTEGER NULL, -- category srl
    `title` TEXT NULL, -- title
    `content` TEXT NULL, -- markdown content
    `hit` INTEGER NOT NULL DEFAULT 0, -- hit count
    `star` INTEGER NOT NULL DEFAULT 0, -- star count
    `json` TEXT NULL DEFAULT '{}', -- json data
    `mode` TEXT NOT NULL DEFAULT 'ready', -- ready,public,private
    `regdate` TEXT NULL, -- custom created date
    `created_at` TEXT NULL, -- created date
    `updated_at` TEXT NULL, -- updated date
    FOREIGN KEY (`app_srl`) REFERENCES `goose_app`(`srl`)
);

-- table `checklist`
CREATE TABLE `goose_checklist` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- checklist srl
    `content` TEXT NULL, -- markdown content
    `percent` INTEGER NOT NULL DEFAULT 0, -- progress
    `created_at` TEXT NOT NULL -- created date
);

-- table `json`
CREATE TABLE `goose_json` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- json srl
    `category_srl` INTEGER NULL, -- category srl
    `name` TEXT NOT NULL, -- json name
    `description` TEXT NULL, -- json description
    `json` TEXT NOT NULL DEFAULT '{}', -- json data
    `path` TEXT NULL, -- json path
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`category_srl`) REFERENCES `goose_category`(`srl`)
);

-- table `nest`
CREATE TABLE `goose_nest` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- nest srl
    `app_srl` INTEGER NULL, -- app srl
    `code` TEXT NOT NULL UNIQUE, -- unique nest code
    `name` TEXT NULL, -- name
    `description` TEXT NULL, -- description
    `json` TEXT NULL DEFAULT '{}', -- json data
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`app_srl`) REFERENCES `goose_app`(`srl`)
);

-- table `category`
CREATE TABLE `goose_category` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- category srl
    `turn` INTEGER NOT NULL DEFAULT 0, -- category name
    `name` TEXT NOT NULL, -- category description
    `module` TEXT NOT NULL, -- nest,json
    `module_srl` INTEGER NOT NULL DEFAULT 0, -- module srl
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_nest`(`srl`, `nest`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_json`(`srl`, `json`)
);

-- table `file`
CREATE TABLE `goose_file` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- file srl
    `name` TEXT NOT NULL, -- file name
    `path` TEXT NOT NULL, -- file path
    `mime` TEXT NOT NULL, -- file mime type
    `size` INTEGER NOT NULL DEFAULT 0, -- file size
    `json` TEXT NULL DEFAULT '{}', -- file json data
    `module` TEXT NOT NULL DEFAULT 'article', -- article,json,checklist
    `module_srl` INTEGER NOT NULL, -- module srl
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_article`(`srl`, `article`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_json`(`srl`, `json`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_checklist`(`srl`, `checklist`)
);

-- table `comment`
CREATE TABLE `goose_comment` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- comment srl
    `content` TEXT NOT NULL, -- markdown content
    `module` TEXT NOT NULL DEFAULT 'article', -- article
    `module_srl` INTEGER NOT NULL, -- module srl
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `goose_article`(`srl`, `article`)
);

-- table `provider`
CREATE TABLE `goose_provider` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- provider srl
    `code` TEXT NOT NULL UNIQUE, -- unique provider code
    `name` TEXT NOT NULL UNIQUE, -- provider name
    `user_id` TEXT NOT NULL,
    `user_name` TEXT NULL,
    `user_avatar` TEXT NULL,
    `user_email` TEXT NULL,
    `created_at` TEXT NOT NULL -- created date
);

-- table `token`
CREATE TABLE `goose_token` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- token srl
    `provider_srl` INTEGER NOT NULL, -- provider srl
    `access` TEXT NOT NULL UNIQUE, -- access token
    `expired` INTEGER NOT NULL, -- expired timestamp
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`provider_srl`) REFERENCES `goose_provider`(`srl`)
);
