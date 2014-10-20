<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\User;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Entity\ActiveRecord;

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
    function getRelationObjectsFromRelateMethod()
    {
        $user = User::query();
        $blog = Blog::query();
        $userBlogs = $user->relate( 'blogs' );
        $blogUser  = $blog->relate( 'user' );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasMany', get_class($userBlogs) );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasOne',  get_class($blogUser) );
    }

    /**
     * @test
     */
    function getRelatedEntityUsingHasOneAndHasManyRelations()
    {
        /*
         * set up
         */
        /** @var ActiveRecord $user */
        $user = User::create( $this->makeUserDataAsArray() )->save();
        $this->assertEquals( '1', $user->getKey() );
        // save a blog1
        $blog1 = Blog::create( $this->makeBlogDataAsArray() );
        $blog1->user_id = $user->getKey();
        $blog1->save();
        // save a blog2
        $blog2 = Blog::create( $this->makeBlogDataAsArray() );
        $blog2->user_id = $user->getKey();
        $blog2->save();

        /*
         * get Blogs from user's blogs relation.
         */
        $userBlogs = User::query()->relate( 'blogs' );
        $userBlogsList = $userBlogs->entity( $user )->get();
        $this->assertTrue( is_array( $userBlogsList ) );
        $this->assertEquals( '2', count( $userBlogsList ) );

        $this->assertEquals( $blog1->getKey(), $userBlogsList[0]->getKey() );
        $this->assertEquals( $blog1->title, $userBlogsList[0]->title );
        $this->assertEquals( $blog1->content, $userBlogsList[0]->content );

        $this->assertEquals( $blog2->getKey(), $userBlogsList[1]->getKey() );
        $this->assertEquals( $blog2->title, $userBlogsList[1]->title );
        $this->assertEquals( $blog2->content, $userBlogsList[1]->content );

        /*
         * get User from blog's user relation.
         */
        $blogUsers = Blog::query()->relate('user')->entity($blog1);
        $blogUser1 = $blogUsers->get();
        $this->assertEquals( 'WScore\ScoreDB\Entity\ActiveRecord', get_class( $blogUser1 ) );
        $this->assertEquals( $user->getKey(), $blogUser1->getKey() );

        $blogUser2 = $blogUsers->get();
        $this->assertEquals( 'WScore\ScoreDB\Entity\ActiveRecord', get_class( $blogUser2 ) );
        $this->assertEquals( $user->getKey(), $blogUser2->getKey() );
    }

    /**
     * @test
     */
    function getRelationObjectUsingActiveRecordField()
    {
        /** @var ActiveRecord $user */
        $user = User::create( $this->makeUserDataAsArray() );
        $blog = Blog::create( $this->makeBlogDataAsArray() );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasMany', get_class($user->blogs) );
        $this->assertEquals( 'WScore\ScoreDB\Relation\HasOne',  get_class($blog->user) );
        $this->assertSame( $user->blogs, $user->blogs );
    }
}