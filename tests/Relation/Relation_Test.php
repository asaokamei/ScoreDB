<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\User;
use WScore\ScoreDB\DB;

require_once( __DIR__ . '/../autoloader.php' );

class Relation_Test extends \PHPUnit_Framework_TestCase
{
    use RelationTrait;

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
        $this->assertEquals( 'tests\Relation\Models\User', get_class(User::query()) );
        $this->assertEquals( 'tests\Relation\Models\Blog', get_class(Blog::query()) );
    }

    /**
     * @test
     */
    function getRelationFromUserToBlog()
    {
        $user = User::query();
        $blog = Blog::query();
        $userBlogs = $user->relate( 'blogs' );
        $blogUser  = $blog->relate( 'user' );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasMany', get_class($userBlogs) );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasOne',  get_class($blogUser) );
    }
}