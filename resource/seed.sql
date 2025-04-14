-- table `app`
CREATE TABLE `app` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- app srl
    `code` TEXT NOT NULL UNIQUE, -- app code
    `name` TEXT NULL, -- app name
    `description` TEXT NULL, -- app description
    `created_at` TEXT NOT NULL -- created date
);

-- table `article`
CREATE TABLE `article` (
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
    FOREIGN KEY (`app_srl`) REFERENCES `app`(`srl`)
);

-- table `checklist`
CREATE TABLE `checklist` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- checklist srl
    `content` TEXT NULL, -- markdown content
    `percent` INTEGER NOT NULL DEFAULT 0, -- progress
    `created_at` TEXT NOT NULL, -- created date
    `updated_at` TEXT NOT NULL -- updated date
);

-- table `json`
CREATE TABLE `json` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- json srl
    `category_srl` INTEGER NULL, -- category srl
    `name` TEXT NOT NULL, -- json name
    `description` TEXT NULL, -- json description
    `json` TEXT NOT NULL DEFAULT '{}', -- json data
    `path` TEXT NULL, -- json path
    `created_at` TEXT NOT NULL, -- created date
    `updated_at` TEXT NOT NULL, -- updated date
    FOREIGN KEY (`category_srl`) REFERENCES `category`(`srl`)
);

-- table `nest`
CREATE TABLE `nest` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- nest srl
    `app_srl` INTEGER NULL, -- app srl
    `code` TEXT NOT NULL UNIQUE, -- unique nest code
    `name` TEXT NULL, -- name
    `description` TEXT NULL, -- description
    `json` TEXT NULL DEFAULT '{}', -- json data
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`app_srl`) REFERENCES `app`(`srl`)
);

-- table `category`
CREATE TABLE `category` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- category srl
    `name` TEXT NOT NULL, -- category description
    `module` TEXT NOT NULL, -- nest,json
    `module_srl` INTEGER NULL, -- module srl
    `turn` INTEGER NOT NULL DEFAULT 0, -- category name
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `nest`(`srl`, `nest`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `json`(`srl`, `json`)
);

-- table `file`
CREATE TABLE `file` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- file srl
    `code` TEXT NOT NULL UNIQUE, -- unique file code
    `name` TEXT NOT NULL, -- file name
    `path` TEXT NOT NULL, -- file path
    `mime` TEXT NOT NULL, -- file mime type
    `size` INTEGER NOT NULL DEFAULT 0, -- file size
    `json` TEXT NULL DEFAULT '{}', -- file json data
    `module` TEXT NOT NULL, -- article,json,checklist
    `module_srl` INTEGER NOT NULL, -- module srl
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `article`(`srl`, `article`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `json`(`srl`, `json`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `checklist`(`srl`, `checklist`)
);

-- table `comment`
CREATE TABLE `comment` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- comment srl
    `content` TEXT NOT NULL, -- markdown content
    `module` TEXT NOT NULL, -- article
    `module_srl` INTEGER NOT NULL, -- module srl
    `created_at` TEXT NOT NULL, -- created date
    `updated_at` TEXT NOT NULL, -- updated date
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `article`(`srl`, `article`)
);

-- table `tag`
CREATE TABLE `tag` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- tag srl
    `name` TEXT NOT NULL -- tag name
);
-- table `map_tag`
CREATE TABLE `map_tag` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- srl
    `tag_srl` INTEGER NOT NULL, -- tag srl
    `module` TEXT NOT NULL, -- article,json,checklist
    `module_srl` INTEGER NOT NULL, -- module srl
    FOREIGN KEY (`tag_srl`) REFERENCES `tag`(`srl`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `article`(`srl`, `article`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `article`(`srl`, `json`),
    FOREIGN KEY (`module_srl`, `module`) REFERENCES `article`(`srl`, `checklist`)
);

-- table `provider`
CREATE TABLE `provider` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- provider srl
    `code` TEXT NOT NULL, -- provider name
    `user_id` TEXT NOT NULL, -- user id
    `user_name` TEXT NULL, -- user name
    `user_avatar` TEXT NULL, -- user avatar
    `user_email` TEXT NULL, -- user email
    `user_password` TEXT NULL, -- user password (for code=password)
    `created_at` TEXT NOT NULL -- created date
);

-- table `token`
CREATE TABLE `token` (
    `srl` INTEGER PRIMARY KEY AUTOINCREMENT, -- token srl
    `provider_srl` INTEGER NOT NULL, -- provider srl
    `access` TEXT NOT NULL UNIQUE, -- access token
    `expires` INTEGER NOT NULL, -- expires_in timestamp
    `refresh` TEXT NULL, -- refresh token
    `created_at` TEXT NOT NULL, -- created date
    FOREIGN KEY (`provider_srl`) REFERENCES `provider`(`srl`)
);
