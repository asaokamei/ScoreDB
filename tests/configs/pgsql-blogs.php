<?php
return '
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