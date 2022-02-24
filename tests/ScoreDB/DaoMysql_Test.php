<?php
namespace tests\ScoreDB;

require_once( __DIR__ . '/../autoloader.php' );

class DaoMysql_Test extends Dao_DbType
{
    var $dbType = 'mysql';

    protected function setUp() : void
    {
        $this->prepareTest( $this->dbType );
    }
}
