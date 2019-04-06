-- Shared Media Tagger Database v1.1

CREATE TABLE IF NOT EXISTS "block" (
  'pageid'  INTEGER PRIMARY KEY,
  'title'   TEXT,
  'thumb'   TEXT,
  'ns'      INTEGER,
  'updated' TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "category" ( -- CREATE TABLE IF NOT EXISTS "topic" (
  'id'            INTEGER PRIMARY KEY,
  'name'          TEXT,
  'curated'       BOOLEAN NOT NULL DEFAULT '0',
  'pageid'        INTEGER,
  'files'         INTEGER,
  'subcats'       INTEGER,
  'local_files'   INTEGER          DEFAULT '0',
  'curated_files' INTEGER          DEFAULT '0',
  'missing'       INTEGER          DEFAULT '0',
  'hidden'        INTEGER          DEFAULT '0',
  'updated'       TEXT             DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT cu UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS "category2media" ( -- CREATE TABLE IF NOT EXISTS "topic2media" (
  'id'           INTEGER PRIMARY KEY,
  'category_id'  INTEGER,   -- 'topic_id'  INTEGER,
  'media_pageid' INTEGER,
  'updated'      TEXT DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT tmu UNIQUE (category_id, media_pageid) -- CONSTRAINT tmu UNIQUE (topic_id, media_pageid)
);

CREATE TABLE IF NOT EXISTS "media" (
  'pageid'              INTEGER PRIMARY KEY,
  'curated'             BOOLEAN NOT NULL DEFAULT '0',
  'title'               TEXT,
  'url'                 TEXT,
  'descriptionurl'      TEXT,
  'descriptionshorturl' TEXT,
  'imagedescription'    TEXT,
  'artist'              TEXT,
  'datetimeoriginal'    TEXT,
  'licenseuri'          TEXT,
  'licensename'         TEXT,
  'licenseshortname'    TEXT,
  'usageterms'          TEXT,
  'attributionrequired' TEXT,
  'restrictions'        TEXT,
  'size'                INTEGER,
  'width'               INTEGER,
  'height'              INTEGER,
  'sha1'                TEXT,
  'mime'                TEXT,
  'thumburl'            TEXT,
  'thumbwidth'          INTEGER,
  'thumbheight'         INTEGER,
  'thumbmime'           TEXT,
  'user'                TEXT,
  'userid'              INTEGER,
  'duration'            REAL,
  'timestamp'           TEXT,
  'updated'             TEXT             DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "site" (
  'id'       INTEGER PRIMARY KEY,
  'name'     TEXT,
  'about'    TEXT,
  'header'   TEXT,
  'footer'   TEXT,
  'curation' BOOLEAN NOT NULL DEFAULT '0',
  'updated'  TEXT             DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT su UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS "tag" (
  'id'           INTEGER PRIMARY KEY,
  'position'     INTEGER,
  'score'        INTEGER,
  'name'         TEXT,
  'display_name' TEXT,
  'updated'      TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "tagging" (
  'id'           INTEGER PRIMARY KEY,
  'user_id'      INTEGER,
  'tag_id'       INTEGER,
  'media_pageid' INTEGER,
  'updated'      TEXT DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT utu UNIQUE (user_id, tag_id, media_pageid)
);

CREATE TABLE IF NOT EXISTS "user" (
  'id'         INTEGER PRIMARY KEY,
  'ip'         TEXT,
  'host'       TEXT,
  'user_agent' TEXT,
  'last'       TEXT,
  'updated'    TEXT DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uc UNIQUE (ip, host, user_agent)
);
