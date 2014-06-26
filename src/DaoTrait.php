<?php
namespace WScore\DbAccess;

use DateTime;
use InvalidArgumentException;
use PDOStatement;

/**
 * Class DaoTrait
 * @package WScore\DbAccess
 *
 * a trait to behave like a DAO.
 * the class must have or extend the QueryInterface.
 */
trait DaoTrait
{
    /**
     * @var DateTime
     */
    public static $now;

    /**
     * @var Hooks
     */
    protected $hooks;

    /**
     * time stamps config.
     * overwrite this property in your DAO class.
     *
     * $timeStamps = array(
     *    type => [ column-name, [ column-name, datetime-format ] ],
     * );
     * where
     * - types are created_at or updated_at.
     * - list the column-name, or array of column-name with datetime-format.
     *
     *
     * @var array
     */
    //protected $timeStamps = array();

    /**
     * @return $this
     */
    public static function forge()
    {
        /** @var Dao $self */
        $self = new static();
        $self->setHook( new Hooks() );
        return $self;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onCreateStampFilter( $data )
    {
        $data = $this->onTimeStampFilter( $data, 'created_at' );
        $data = $this->onTimeStampFilter( $data, 'updated_at' );
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onUpdateStampFilter( $data )
    {
        $data = $this->onTimeStampFilter( $data, 'updated_at' );
        return $data;
    }

    /**
     * @param array $data
     * @param string $type
     * @return array
     */
    protected function onTimeStampFilter( $data, $type )
    {
        if( !isset( $this->timeStamps ) ||
            !is_array( $this->timeStamps ) ) {
            return $data;
        }
        if( !isset( $this->timeStamps[$type] ) ||
            !is_array( $this->timeStamps[$type] ) ) {
            return $data;
        }
        $filters = $this->timeStamps[$type];
        if( !static::$now ) static::$now = new DateTime();
        foreach( $filters as $column => $format ) {
            if( is_numeric( $column ) ) {
                $column = $format;
                $format = 'Y-m-d H:i:s';
            }
            $data[ $column ] = static::$now->format( $format );
        }
        return $data;
    }

    /**
     * @param $method
     * @param $args
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call( $method, $args )
    {
        if( method_exists( $this, $scope = 'scope'.ucfirst($method) ) ) {
            call_user_func_array( [$this, $scope], $args );
            return $this;
        }
        throw new \BadMethodCallException( 'no such method: '.$method );
    }

    // +----------------------------------------------------------------------+
    //  execute sql.
    // +----------------------------------------------------------------------+
    /**
     * @param null|int $limit
     * @throws \BadMethodCallException
     * @return array
     */
    public function select($limit=null)
    {
        $this->hooks( 'selecting', $limit );
        /** @noinspection PhpUndefinedMethodInspection */
        $data = parent::select( $limit );
        $data = $this->hooks( 'selected', $data );
        return $data;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->hooks( 'counting' );
        /** @noinspection PhpUndefinedMethodInspection */
        $count = parent::count();
        $count = $this->hooks( 'counted', $count );
        return $count;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $id   = $this->hooks( 'loading', $id, $column );
        /** @noinspection PhpUndefinedMethodInspection */
        $data = parent::load( $id, $column );
        $data = $this->hooks( 'loaded', $data );
        return $data;
    }

    /**
     * @param $data
     * @throws InvalidArgumentException
     * @return int|PdoStatement
     */
    public function save( $data )
    {
        $by   = $this->hooks( 'saveBy', $data );
        if( !$by ) {
            throw new InvalidArgumentException( 'save method not defined. ' );
        }
        $data = $this->hooks( 'saving', $data );
        $stmt = $this->$by($data);
        $stmt = $this->hooks( 'saved', $stmt );
        return $stmt;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        $data = $this->hooks( 'createStamp', $data );
        $data = $this->hooks( 'inserting', $data );
        /** @noinspection PhpUndefinedMethodInspection */
        $id = parent::insert( $data );
        $id = $this->hooks( 'inserted', $id );
        return $id;
    }

    /**
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        $data = $this->hooks( 'updateStamp', $data );
        $data = $this->hooks( 'updating', $data );
        /** @noinspection PhpUndefinedMethodInspection */
        $stmt = parent::update( $data );
        $stmt = $this->hooks( 'updated', $stmt );
        return $stmt;
    }

    /**
     * @param int $id
     * @param string $column
     * @return string
     */
    public function delete( $id=null, $column=null )
    {
        $id = $this->hooks( 'deleting', $id, $column );
        /** @noinspection PhpUndefinedMethodInspection */
        $stmt = parent::delete( $id, $column );
        $stmt = $this->hooks( 'deleted', $stmt );
        return $stmt;
    }

    // +----------------------------------------------------------------------+
    //  hooks
    // +----------------------------------------------------------------------+
    /**
     * dumb hooks for various events. $data are all string.
     * available events are:
     * - constructing, constructed, newQuery,
     * - selecting, selected, inserting, inserted,
     * - updating, updated, deleting, deleted,
     *
     * @param string       $event
     * @param mixed|null   $data
     * @return mixed|null
     */
    protected function hooks( $event, $data=null )
    {
        if( $this->hooks ) {
            $args = func_get_args();
            $data = call_user_func_array( [$this->hooks, 'hook'], $args );
        }
        return $data;
    }

    /**
     * @param Hooks $hook
     */
    public function setHook( $hook )
    {
        $this->hooks = $hook;
        $this->hooks->setHook( $this );
    }
}