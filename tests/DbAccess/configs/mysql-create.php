<?php
return '
        CREATE TABLE test_WScore (
            user_id int NOT NULL AUTO_INCREMENT,
            name CHAR(30),
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
        ';