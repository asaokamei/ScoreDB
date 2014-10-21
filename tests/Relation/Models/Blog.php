<?php
namespace tests\Relation\Models;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Query;
use WScore\ScoreDB\Relation\Relation;

/**
 * Class Blog
 *
 * @package tests\Relation\Models
 *
 *
 */
class Blog extends Dao
{
    protected $table = 'dao_blog';

    protected $keyName = 'blog_id';

    protected $timeStamps = [
        'created_at' => [ 'created_at' ],
        'updated_at' => [ 'updated_at' ],
    ];

    protected $fillable = [
        'title', 'content'
    ];

    protected $fetch_class = 'WScore\ScoreDB\Entity\ActiveRecord';

    /**
     * @return \WScore\ScoreDB\Relation\HasOne
     */
    public function getUserRelation()
    {
        return Relation::hasOne( $this, 'tests\Relation\Models\User', 'user_id' );
    }

    /**
     * @return \WScore\ScoreDB\Relation\HasJoin
     */
    public function getBlogsRelation()
    {
        return Relation::hasJoin( $this, 'tests\Relation\Models\Tag', 'tests\Relation\Models\BlogTag' );
    }

}