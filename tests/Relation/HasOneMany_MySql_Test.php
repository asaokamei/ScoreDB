<?php
namespace tests\Relation;

use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class HasOneMany_MySql_Test extends \PHPUnit_Framework_TestCase
{
    use testHasOneManyTrait;

    var $dbType = 'mysql';

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