<?php
namespace tests\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Relation\Relation;

class Tag extends Dao
{
    protected $returnLastId = false;

    protected $table = 'dao_tag';

    protected $keyName = 'tag_id';

    protected $fillable = [ 'tag' ];

    protected $timeStamps = [
        'created_at' => [ 'created_at' ],
        'updated_at' => [ 'updated_at' ],
    ];

    protected $fetch_class = 'WScore\ScoreDB\Entity\ActiveRecord';

    /**
     * @return \WScore\ScoreDB\Relation\HasJoin
     */
    public function getBlogsRelation()
    {
        return Relation::hasJoin( $this, 'tests\Relation\Models\Blog', 'tests\Relation\Models\BlogTag' );
    }
}