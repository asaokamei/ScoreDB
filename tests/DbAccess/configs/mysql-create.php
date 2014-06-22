<?php
return '
        CREATE TABLE test_WScore (
            user_id int NOT NULL AUTO_INCREMENT,
            name CHAR(30),
            age  int,
            bday date,
            no_null text NOT NULL,
            created_at  datetime,
            updated_at  datetime,
            PRIMARY KEY (user_id)
        );
        ';