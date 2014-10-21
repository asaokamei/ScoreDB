<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\Tag;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Entity\ActiveRecord;

require_once( __DIR__ . '/../autoloader.php' );

class HasJoin_PgSql_Test extends \PHPUnit_Framework_TestCase
{
    use testHasJoinTrait;

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

    function test0()
    {
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasJoin', get_class(Blog::query()->getTagsRelation()) );
    }

}
