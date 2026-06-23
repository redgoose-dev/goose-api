PRAGMA foreign_keys = ON;

-- table `app`
CREATE TABLE `app` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `code` TEXT NOT NULL UNIQUE, -- code
  `name` TEXT NOT NULL, -- name
  `description` TEXT NULL, -- description
  `created_at` TEXT NOT NULL -- created date
);

-- table `article`
CREATE TABLE `article` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `nest_srl` INTEGER NULL, -- nest srl
  `category_srl` INTEGER NULL, -- category srl
  `title` TEXT NULL, -- title
  `content` TEXT NULL, -- markdown content
  `hit` INTEGER NOT NULL DEFAULT 0 CHECK (`hit` >= 0), -- hit count
  `star` INTEGER NOT NULL DEFAULT 0 CHECK (`star` >= 0), -- star count
  `json` TEXT NULL DEFAULT '{}', -- json data
  `mode` TEXT NOT NULL CHECK(mode IN ('ready', 'public', 'private')) DEFAULT 'ready', -- mode
  `regdate` TEXT NULL, -- custom created date
  `created_at` TEXT NULL, -- created date
  `updated_at` TEXT NULL, -- updated date
  FOREIGN KEY (`nest_srl`) REFERENCES `nest`(`srl`) ON DELETE SET NULL,
  FOREIGN KEY (`category_srl`) REFERENCES `category`(`srl`) ON DELETE SET NULL
);
CREATE INDEX idx_article_nest_srl ON article(nest_srl);
CREATE INDEX idx_article_category_srl ON article(category_srl);
CREATE INDEX idx_article_public_regdate_nest ON article(regdate DESC, srl DESC, nest_srl) WHERE mode = 'public';
CREATE INDEX idx_article_public_nest_regdate ON article(nest_srl, regdate DESC, srl DESC) WHERE mode = 'public';

-- table `checklist`
CREATE TABLE `checklist` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `content` TEXT NULL, -- markdown content
  `percent` INTEGER NOT NULL DEFAULT 0 CHECK (`percent` BETWEEN 0 AND 100), -- progress
  `created_at` TEXT NOT NULL, -- created date
  `updated_at` TEXT NOT NULL -- updated date
);

-- table `json`
CREATE TABLE `json` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `category_srl` INTEGER NULL, -- category srl
  `name` TEXT NOT NULL, -- name
  `description` TEXT NULL, -- description
  `json` TEXT NOT NULL DEFAULT '{}', -- json data
  `created_at` TEXT NOT NULL, -- created date
  `updated_at` TEXT NOT NULL, -- updated date
  FOREIGN KEY (`category_srl`) REFERENCES `category`(`srl`) ON DELETE SET NULL
);
CREATE INDEX idx_json_category_srl ON json(category_srl);

-- table `nest`
CREATE TABLE `nest` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `app_srl` INTEGER NULL, -- app srl
  `code` TEXT NOT NULL UNIQUE, -- unique nest code
  `name` TEXT NULL, -- name
  `description` TEXT NULL, -- description
  `json` TEXT NULL DEFAULT '{}', -- json data
  `created_at` TEXT NOT NULL, -- created date
  FOREIGN KEY (`app_srl`) REFERENCES `app`(`srl`) ON DELETE SET NULL
);
CREATE INDEX idx_nest_app_srl ON nest(app_srl);

-- table `category`
CREATE TABLE `category` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `name` TEXT NOT NULL, -- description
  `turn` INTEGER NOT NULL DEFAULT 0, -- category name
  `module` TEXT NOT NULL CHECK (`module` IN ('nest', 'json')), -- module table name
  `module_srl` INTEGER NULL, -- module srl
  `created_at` TEXT NOT NULL, -- created date
  UNIQUE (`module`, `module_srl`, `name`)
);
CREATE INDEX idx_category_module_srl_turn ON category(module, module_srl, turn);

-- table `file`
CREATE TABLE `file` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `code` TEXT NOT NULL UNIQUE, -- unique file code
  `name` TEXT NOT NULL, -- file name
  `path` TEXT NOT NULL, -- file path
  `mime` TEXT NOT NULL, -- file mime type
  `size` INTEGER NOT NULL DEFAULT 0 CHECK (`size` >= 0), -- file size
  `json` TEXT NULL DEFAULT '{}', -- file json data
  `module` TEXT NOT NULL CHECK (`module` IN ('article', 'checklist', 'comment', 'json')), -- module table name
  `module_srl` INTEGER NOT NULL, -- module srl
  `created_at` TEXT NOT NULL -- created date
);
CREATE INDEX idx_file_module_srl ON file(module, module_srl);
CREATE INDEX idx_file_mime ON file(mime);

-- table `comment`
CREATE TABLE `comment` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `content` TEXT NOT NULL, -- markdown content
  `module` TEXT NOT NULL CHECK (`module` = 'article'), -- article
  `module_srl` INTEGER NOT NULL, -- module srl
  `created_at` TEXT NOT NULL, -- created date
  `updated_at` TEXT NOT NULL -- updated date
);
CREATE INDEX idx_comment_module_srl ON comment(module, module_srl);

-- table `tag`
CREATE TABLE `tag` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `name` TEXT NOT NULL UNIQUE -- tag name
);
-- table `map_tag`
CREATE TABLE `map_tag` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `tag_srl` INTEGER NOT NULL REFERENCES `tag`(`srl`) ON DELETE CASCADE, -- tag srl
  `module` TEXT NOT NULL CHECK (`module` IN ('article', 'json', 'checklist')), -- module table name
  `module_srl` INTEGER NOT NULL, -- module srl
  UNIQUE (`tag_srl`, `module`, `module_srl`)
);
CREATE INDEX idx_map_tag_module_srl ON map_tag(module, module_srl);

-- table `provider`
CREATE TABLE `provider` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `code` TEXT NOT NULL, -- provider name
  `user_id` TEXT NOT NULL, -- user id
  `user_name` TEXT NULL, -- username
  `user_avatar` TEXT NULL, -- user avatar
  `user_email` TEXT NULL, -- user email
  `user_password` TEXT NULL, -- user password (for code=password)
  `created_at` TEXT NOT NULL, -- created date
  UNIQUE (`code`, `user_id`)
);
CREATE INDEX idx_provider_user_id ON provider(user_id);

-- table `token`
CREATE TABLE `token` (
  `srl` INTEGER PRIMARY KEY, -- srl
  `provider_srl` INTEGER NOT NULL REFERENCES `provider`(`srl`) ON DELETE CASCADE, -- provider srl
  `access` TEXT NOT NULL UNIQUE, -- access token
  `expires` INTEGER CHECK (`expires` >= 0), -- expires_in timestamp
  `refresh` TEXT NULL, -- refresh token
  `description` TEXT NULL, -- description
  `created_at` TEXT NOT NULL -- created date
);
CREATE INDEX idx_token_provider_srl ON token(provider_srl);
CREATE INDEX idx_token_expires ON token(expires);
