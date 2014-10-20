<?php
namespace tests\Relation\Models;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Query;
use WScore\ScoreDB\Relation\Relation;

/**
 * Class User
 *
 * @package tests\Relation\Models
 *
 *
 */
class User extends Dao
{
    protected $table = 'dao_user';

    protected $keyName = 'user_id';
    
    protected $timeStamps = [
        'created_at' => [
            'created_at',
            'open_date' => 'Y-m-d'
        ],
        'updated_at' => [
            'updated_at'
        ],
    ];

    protected $fillable = [
        'name', 'gender', 'status', 'age', 'bday', 'no_null', 'open_date'
    ];

    protected $fetch_class = 'WScore\ScoreDB\Entity\ActiveRecord';

    public function getBlogsRelation()
    {
        return Relation::hasMany( $this, 'tests\Relation\Models\Blog' );
    }
}