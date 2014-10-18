<?php
return '
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