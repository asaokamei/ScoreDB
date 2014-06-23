<?php
namespace tests\DbAccess\Dao;

use WScore\DbAccess\Dao;
use WScore\DbAccess\Hooks;

/**
 * Class User
 *
 * @package tests\DbAccess\Dao
 *          
 * @method User status( $status=1 )
 */
class User extends Dao
{
    public $table = 'dao_user';
    
    public $keyName = 'user_id';
    
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
     * @param int $status
     */
    public function scopeStatus($status=1)
    {
        $this->where( $this->status->eq( $status ) );
    }
}