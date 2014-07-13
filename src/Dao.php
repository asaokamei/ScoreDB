<?php
namespace WScore\ScoreDB;

use WScore\ScoreDB\Hook\Hooks;

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

        if( !$this->table ) {
            $this->table = get_class($this);
            if( false!==strpos($this->table, '\\') ) {
                $this->table = substr( $this->table, strrpos($this->table,'\\')+1 );
            }
        }
        if( !$this->keyName ) {
            $this->keyName = $this->table . '_id';
        }
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