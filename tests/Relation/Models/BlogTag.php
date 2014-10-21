<?php
namespace tests\Relation;

use WScore\ScoreDB\Dao;

class BlogTag extends Dao
{
    protected $table = 'dao_blog_tag';

    protected $timeStamps = [
        'created_at' => [ 'created_at' ],
    ];
}