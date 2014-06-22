<?php
return '
        CREATE TABLE test_WScore (
            id SERIAL,
            name CHAR(30),
            age  int,
            bdate date,
            no_null text NOT NULL,
            PRIMARY KEY (id)
        );
        ';