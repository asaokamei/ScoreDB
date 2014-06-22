<?php
namespace tests\DbAccess\Dao;

use WScore\DbAccess\Dao;

class User extends Dao
{
    public $table = 'test_WScore';
    
    public $keyName = 'user_id';
    
    
}