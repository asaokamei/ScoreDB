<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\User;
use WScore\ScoreDB\Entity\ActiveRecord;

trait testHasOneManyTrait
{
    use testToolsTrait;

    abstract function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false);
    abstract function assertTrue($condition, $message = '');
    abstract function assertSame($expected, $actual, $message = '');

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

    /**
     * @test
     */
    function linkSavedEntitiesUsingHasManyRelationObject()
    {
        /*
         * set up
         */
        /** @var ActiveRecord $user0 */
        /** @var ActiveRecord $blog1 */
        /** @var ActiveRecord $blog2 */
        $user0 = User::create( $this->makeUserDataAsArray() )->save();
        $blog1 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $blog2 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $user_id = $user0->getKey();
        /*
         * link them!
         */
        $user0->blogs->link( $blog1 );
        $user0->blogs->link( $blog2 );
        /*
         * save the blogs with user_id
         */
        $blog1->save();
        $blog2->save();

        /*
         * get them!
         */
        /** @var ActiveRecord $user */
        /** @var ActiveRecord[] $blogs */
        $user = User::findOrFail( $user_id );
        $blogs = $user->blogs->get();

        $this->assertTrue( is_array( $blogs ) );
        $this->assertEquals( '2', count( $blogs ) );

        $this->assertEquals( $blog1->getKey(), $blogs[0]->getKey() );
        $this->assertEquals( $blog1->title, $blogs[0]->title );
        $this->assertEquals( $blog1->content, $blogs[0]->content );

        $this->assertEquals( $blog2->getKey(), $blogs[1]->getKey() );
        $this->assertEquals( $blog2->title, $blogs[1]->title );
        $this->assertEquals( $blog2->content, $blogs[1]->content );
    }

    /**
     * @test
     */
    function linkSavedEntitiesUsingHasOneRelationObject()
    {
        /*
         * set up
         */
        /** @var ActiveRecord $user0 */
        /** @var ActiveRecord $blog1 */
        /** @var ActiveRecord $blog2 */
        $user0 = User::create( $this->makeUserDataAsArray() )->save();
        $blog1 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $blog2 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $user_id = $user0->getKey();
        /*
         * link them!
         */
        $blog1->user->link( $user0 );
        $blog2->user->link( $user0 );
        /*
         * save the blogs with user_id
         */
        $blog1->save();
        $blog2->save();

        /*
         * get them!
         */
        /** @var ActiveRecord $user */
        /** @var ActiveRecord[] $blogs */
        $user = User::findOrFail( $user_id );
        $blogs = $user->blogs->get();

        $this->assertTrue( is_array( $blogs ) );
        $this->assertEquals( '2', count( $blogs ) );

        $this->assertEquals( $blog1->getKey(), $blogs[0]->getKey() );
        $this->assertEquals( $blog1->title, $blogs[0]->title );
        $this->assertEquals( $blog1->content, $blogs[0]->content );

        $this->assertEquals( $blog2->getKey(), $blogs[1]->getKey() );
        $this->assertEquals( $blog2->title, $blogs[1]->title );
        $this->assertEquals( $blog2->content, $blogs[1]->content );
    }

}