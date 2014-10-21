<?php
namespace tests\Relation;

use tests\Relation\Models\Blog;
use tests\Relation\Models\Tag;
use WScore\ScoreDB\Entity\ActiveRecord;

trait testHasJoinTrait
{
    use testToolsTrait;

    abstract function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false);
    abstract function assertTrue($condition, $message = '');
    abstract function assertSame($expected, $actual, $message = '');

    /**
     * @test
     */
    function linkAndGetRelatedBlogAndTags()
    {
        /*
         * setup
         */
        /** @var ActiveRecord $blog1 */
        /** @var ActiveRecord $blog2 */
        /** @var ActiveRecord $tag1 */
        $blog1 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $blog2 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $tag1  = Tag::create(  $this->makeTagDataAsArray('test') )->save();
        // link them.
        $blog1->tags->link( $tag1 );
        $blog2->tags->link( $tag1 );
        /*
         * get blogs from tag using HasJoin.
         */
        /** @var ActiveRecord[] $blogs */
        $blogs = $tag1->blogs->get();
        $this->assertTrue( is_array( $blogs ) );
        $this->assertEquals( '2', count( $blogs ) );

        $this->assertEquals( $blog1->getKey(), $blogs[0]->getKey() );
        $this->assertEquals( $blog1->title, $blogs[0]->title );
        $this->assertEquals( $blog1->content, $blogs[0]->content );

        $this->assertEquals( $blog2->getKey(), $blogs[1]->getKey() );
        $this->assertEquals( $blog2->title, $blogs[1]->title );
        $this->assertEquals( $blog2->content, $blogs[1]->content );

        /** @var ActiveRecord[] $tags */
        $tags = $blog1->tags->get();
        $this->assertEquals( 'WScore\ScoreDB\Entity\ActiveRecord', get_class($tags[0] ) );
        $this->assertEquals( $tag1->getKey(), $tags[0]->getKey() );
    }

    /**
     * @test
     */
    function linkTagsAtOnce()
    {
        /*
         * setup
         */
        /** @var ActiveRecord $blog1 */
        /** @var ActiveRecord $tag1 */
        /** @var ActiveRecord $tag2 */
        /** @var ActiveRecord $tag3 */
        $blog1 = Blog::create( $this->makeBlogDataAsArray() )->save();
        $tag1  = Tag::create(  $this->makeTagDataAsArray('test') )->save();
        $tag2  = Tag::create(  $this->makeTagDataAsArray('more') )->save();
        $tag3  = Tag::create(  $this->makeTagDataAsArray('bad')  )->save();
        // link them, only to $tag3 only
        $blog1->tags->link( $tag3 );

        /*
         * test for $tag3.
         */
        /** @var ActiveRecord $blog */
        /** @var ActiveRecord[] $tags */
        $blog = Blog::findOrFail( $blog1->getKey() );
        $tags = $blog->tags->get();

        $this->assertTrue( is_array( $tags ) );
        $this->assertEquals( '1', count( $tags ) );

        $this->assertEquals( $tag3->getKey(), $tags[0]->getKey() );
        $this->assertEquals( $tag3->tag, $tags[0]->tag );

        // link them.
        $blog1->tags->link( [$tag1, $tag2] );
        /*
         * get tags from blog using HasJoin
         * this will replace $tag3, i.e. only $tag{1|2} are related.
         */
        /** @var ActiveRecord $blog */
        /** @var ActiveRecord[] $tags */
        $blog = Blog::findOrFail( $blog1->getKey() );
        $tags = $blog->tags->get();

        $this->assertTrue( is_array( $tags ) );
        $this->assertEquals( '2', count( $tags ) );

        $this->assertEquals( $tag1->getKey(), $tags[0]->getKey() );
        $this->assertEquals( $tag1->tag, $tags[0]->tag );

        $this->assertEquals( $tag2->getKey(), $tags[1]->getKey() );
        $this->assertEquals( $tag2->tag, $tags[1]->tag );
    }

}