<?php
return <<<'BLOG_SQL'

DROP TABLE IF EXISTS dao_user;
DROP TABLE IF EXISTS dao_blog;
DROP TABLE IF EXISTS dao_tag;
DROP TABLE IF EXISTS dao_blog_tag;

CREATE TABLE dao_user (
    user_id SERIAL,
    name VARCHAR(30),
    gender INT,
    status INT,
    age  INT,
    bday DATE,
    no_null TEXT NOT NULL,
    open_date   DATE,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    PRIMARY KEY (user_id)
);

CREATE TABLE dao_blog (
  blog_id SERIAL,
  user_id INT,
  status INT,
  title TEXT,
  content TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY ( blog_id )
);

CREATE TABLE dao_tag (
  tag_id VARCHAR(64),
  tag TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (tag_id)
);

CREATE TABLE dao_blog_tag (
  tag_id VARCHAR(64),
  blog_id INT NOT NULL,
  created_at TIMESTAMP,
  PRIMARY KEY (tag_id, blog_id)
)

BLOG_SQL
;