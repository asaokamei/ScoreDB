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
     * set true to use the value set in $useFilteredData.
     *
     * @var bool
     */
    protected $useFilteredFlag = false;
    protected $filteredData = null;

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
        $limit = $this->hooks( 'selecting', $limit );
        $data = $this->callParent( 'select', $limit );
        $data = $this->hooks( 'selected', $data );
        return $data;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->hooks( 'counting' );
        $count = $this->callParent( 'count' );
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
        list( $id, $column ) = $this->hooks( 'loading', [ $id, $column ] );
        $data = $this->callParent( 'load', $id, $column );
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
        $by   = $this->hooks( 'saveMethod', $data );
        if( !$by ) {
            throw new InvalidArgumentException( 'save method not defined. ' );
        }
        $data = $this->hooks( 'saving', $data );
        $stmt = $this->callParent( $by, $data);
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
        $id = $this->callParent( 'insert', $data );
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
        $stmt = $this->callParent( 'update', $data );
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
        list( $id, $column ) = $this->hooks( 'deleting', [ $id, $column ] );
        $stmt = $this->callParent( 'delete', $id, $column );
        $stmt = $this->hooks( 'deleted', $stmt );
        return $stmt;
    }

    /**
     * @param string $method
     * @return mixed
     */
    protected function callParent( $method )
    {
        if( $this->useFilteredFlag ) {
            return $this->filteredData;
        }
        $args = func_get_args();
        array_shift( $args );
        $result = call_user_func_array( ['parent',$method], $args );
        return $result;
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
     * @param string $event
     * @param mixed  $data
     * @return mixed|null
     */
    protected function hooks( $event, $data=null )
    {
        if( $this->hooks ) {
            $data = $this->hooks->hook( $event, $data );
            if( $this->hooks->usesFilterData() ) {
                $this->filteredData = $data;
                $this->useFilteredFlag = true;
            }
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