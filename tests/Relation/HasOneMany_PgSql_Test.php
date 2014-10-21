<?php
namespace tests\Relation;

use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class HasOneMany_PgSql_Test extends \PHPUnit_Framework_TestCase
{
    use testHasOneManyTrait;

    var $dbType = 'pgsql';

    static function setupBeforeClass()
    {
        self::loadClasses();
        DB::restart();
    }

    function setup()
    {
        $this->prepareTest( $this->dbType );
    }
}