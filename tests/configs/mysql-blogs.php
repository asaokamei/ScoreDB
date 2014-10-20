<?php
return '
        DROP TABLE IF EXISTS dao_user;
        DROP TABLE IF EXISTS dao_blog;
        DROP TABLE IF EXISTS dao_tag;
        DROP TABLE IF EXISTS dao_blog_tag;

        CREATE TABLE dao_user (
            user_id int NOT NULL AUTO_INCREMENT,
            name VARCHAR(30),
            gender int,
            status int,
            age  int,
            bday date,
            no_null text NOT NULL,
            open_date   date,
            created_at  datetime,
            updated_at  datetime,
            PRIMARY KEY (user_id)
        );

        CREATE TABLE dao_blog (
          blog_id int NOT NULL AUTO_INCREMENT,
          user_id INT,
          status int,
          title TEXT,
          content TEXT,
          created_at DATETIME,
          updated_at DATETIME,
          PRIMARY KEY ( blog_id )
        );
        
        CREATE TABLE dao_tag (
          tag_id INT NOT NULL AUTO_INCREMENT,
          tag TEXT,
          created_at DATETIME,
          updated_at DATETIME,
          PRIMARY KEY (tag_id)
        );
        
        CREATE TABLE dao_blog_tag (
          tag_id int NOT NULL ,
          blog_id INT NOT NULL,
          created_at DATETIME,
          PRIMARY KEY (tag_id, blog_id)
        )
        ';