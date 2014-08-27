<?php
namespace tests\Entity\Dao;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\TimeStampTrait;
use WScore\ScoreDB\Query;

/**
 * Class User
 *
 * @package tests\ScoreDB\Dao
 *          
 * @method UserDao status( $status=1 )
 */
class UserDao extends Dao
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

    protected $fetch_class = 'tests\Entity\Dao\User';

    /**
     * @param Query $query
     * @param int $status
     */
    public function scopeStatus( $query, $status=1)
    {
        $this->where( $query->status->eq( $status ) );
    }
}