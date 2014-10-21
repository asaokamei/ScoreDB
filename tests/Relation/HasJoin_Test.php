<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class HasJoin_Test extends \PHPUnit_Framework_TestCase
{
    use testToolsTrait;

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

    function test0()
    {
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasJoin', get_class(Blog::query()->getBlogsRelation()) );
    }
}
