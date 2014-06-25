<?php
namespace WScore\DbAccess;

use DateTime;
use InvalidArgumentException;
use PDOStatement;

class Dao extends Query
{
    /**
     * @var DateTime
     */
    public static $now;

    /**
     * time stamps config.
     * [ type => [ column, format ], type => column ]
     *
     * @var array
     */
    protected $timeStamps = array(
        'created_at' => ['created_at' => 'Y-m-d H:i:s' ],
        'updated_at' => ['updated_at' => 'Y-m-d H:i:s' ],
    );

    /**
     * sets table and keyName from class name if they are not set. 
     * 
     * @param Hooks $hook
     */
    public function __construct( $hook=null )
    {
        if( $hook ) $hook->setHook( $this );
        $this->hooks( 'constructing' );

        if( !$this->table ) {
            $this->table = get_class($this);
            if( false!==strpos($this->table, '\\') ) {
                $this->table = substr( $this->table, strrpos($this->table,'\\')+1 );
            }
        }
        if( !$this->keyName ) {
            $this->keyName = $this->table . '_id';
        }
        $this->hooks( 'constructed' );
    }

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
        if( $at = $this->timeStamps['created_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        if( $at = $this->timeStamps['updated_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onUpdateStampFilter( $data )
    {
        if( $at = $this->timeStamps['updated_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        return $data;
    }

    /**
     * @param array $data
     * @param array $filters
     * @return array
     */
    protected function onTimeStampFilter( $data, $filters ) 
    {
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
     * @return array
     */
    public function select($limit=null)
    {
        $this->hooks( 'selecting', $limit );
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