<?php
namespace WScore\ScoreDB;

use WScore\ScoreDB\Hook\Hooks;

class Dao extends Query
{
    use Dao\TimeStampTrait;

    use Dao\TableAndKeyNameTrait;

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
     * sets table and keyName from class name if they are not set.
     *
     * @param Hooks $hook
     */
    public function __construct( $hook=null )
    {
        if( $hook ) {
            $hook->setHook($this);
            $this->setHook( $hook );
        }
        $this->hook( 'constructing' );
        $this->hook( 'constructed' );
    }

    /**
     * @return Dao
     */
    public static function query()
    {
        /** @var Dao $self */
        return new static( new Hooks() );
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function mutate( $key, $value )
    {
        return $this->hooks->mutate( $key, $value, 'set' );
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function muteBack( $key, $value )
    {
        return $this->hooks->mutate( $key, $value, 'get' );
    }
}