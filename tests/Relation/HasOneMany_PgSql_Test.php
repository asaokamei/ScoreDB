<?php
namespace tests\Relation;

use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class HasOneMany_PgSql_Test extends \PHPUnit\Framework\TestCase
{
    use testHasOneManyTrait;

    var $dbType = 'pgsql';

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