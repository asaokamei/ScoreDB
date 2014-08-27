<?php
return '
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
        ';