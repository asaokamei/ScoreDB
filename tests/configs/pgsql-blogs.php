<?php
return '
        DROP TABLE IF EXISTS dao_user;
        DROP TABLE IF EXISTS dao_blog;
        DROP TABLE IF EXISTS dao_tag;
        DROP TABLE IF EXISTS dao_blog_tag;

        CREATE TABLE dao_user (
            user_id SERIAL,
            name VARCHAR(30),
            gender int,
            status int,
            age  int,
            bday date,
            no_null text NOT NULL,
            open_date   date,
            created_at  timestamp,
            updated_at  timestamp,
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
          tag_id SERIAL,
          tag TEXT,
          created_at TIMESTAMP,
          updated_at TIMESTAMP,
          PRIMARY KEY (tag_id)
        );
        
        CREATE TABLE dao_blog_tag (
          tag_id INT NOT NULL ,
          blog_id INT NOT NULL,
          created_at TIMESTAMP,
          PRIMARY KEY (tag_id, blog_id)
        )
        ';