<?php
namespace tests\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class DaoPgsql_Test extends Dao_DbType
{
    var $dbType = 'pgsql';

    function setup()
    {
        $this->prepareTest( $this->dbType );
    }
}
