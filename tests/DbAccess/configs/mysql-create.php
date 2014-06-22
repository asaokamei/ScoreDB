<?php
return '
        CREATE TABLE test_WScore (
            id int NOT NULL AUTO_INCREMENT,
            name CHAR(30),
            age  int,
            bdate date,
            no_null text NOT NULL,
            PRIMARY KEY (id)
        );
        ';