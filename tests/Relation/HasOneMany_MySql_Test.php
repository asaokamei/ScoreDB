<?php
namespace tests\Relation;

use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class HasOneMany_MySql_Test extends \PHPUnit\Framework\TestCase
{
    use testHasOneManyTrait;

    var $dbType = 'mysql';

    public static function setUpBeforeClass() : void
    {
        self::loadClasses();
        DB::restart();
    }

    protected function setUp() : void
    {
        $this->prepareTest( $this->dbType );
    }
}