<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\Tag;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Entity\ActiveRecord;

require_once( __DIR__ . '/../autoloader.php' );

class HasJoin_MySql_Test extends \PHPUnit\Framework\TestCase
{
    use testHasJoinTrait;

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

    function test0()
    {
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasJoin', get_class(Blog::query()->getTagsRelation()) );
    }

}
