<?php
namespace tests\ScoreDB\Dao;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\DaoTrait;
use WScore\ScoreDB\Query;

/**
 * Class User
 *
 * @package tests\DbAccess\Dao
 *          
 * @method User status( $status=1 )
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

    /**
     * @param Query $query
     * @param int $status
     */
    public function scopeStatus( $query, $status=1)
    {
        $this->where( $query->status->eq( $status ) );
    }
}