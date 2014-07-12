<?php
namespace WScore\DbAccess;

class Dao extends Query
{
    use DaoTrait;

    /**
     * time stamps config.
     *
     * $timeStamps = array(
     *    type => [ column-name, [ column-name, datetime-format ] ],
     * );
     * where
     * - types are created_at or updated_at.
     * - list the column-name, or array of column-name with datetime-format.
     *
     * @var array
     */
    protected $timeStamps = array(
        'created_at' => [ 'created_at' ],
        'updated_at' => [ 'updated_at' ],
    );

    /**
     * @return Dao
     */
    public static function query()
    {
        $self = new self();
        $self->setHook( $self );
        return $self;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic( $method, $args )
    {
        $query = self::query();
        return call_user_func_array( [$query,$method], $args );
    }
}