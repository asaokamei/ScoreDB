<?php
namespace tests\ScoreDB;

require_once( __DIR__ . '/../autoloader.php' );

class DaoPgsql_Test extends Dao_DbType
{
    var $dbType = 'pgsql';

    protected function setUp() : void
    {
        $this->prepareTest( $this->dbType );
    }
}
