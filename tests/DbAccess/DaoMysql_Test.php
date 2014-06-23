<?php
namespace tests\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class DaoMysql_Test extends Dao_DbType
{
    var $dbType = 'mysql';

    function setup()
    {
        $this->prepareTest( $this->dbType );
    }
}
