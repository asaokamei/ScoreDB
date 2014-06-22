<?php
namespace tests\DbAccess\Dao;

use WScore\DbAccess\Dao;
use WScore\DbAccess\Hooks;

class User extends Dao
{
    public $table = 'test_WScore';
    
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
     * @return User
     */
    public static function forge()
    {
        $self = new self();
        $self->setHook( new Hooks() );
        return $self;
    }
}