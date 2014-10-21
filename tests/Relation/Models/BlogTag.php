<?php
namespace tests\Relation\Models;

use WScore\ScoreDB\Dao;

class BlogTag extends Dao
{
    protected $table = 'dao_blog_tag';

    protected $timeStamps = [
        'created_at' => [ 'created_at' ],
    ];
}