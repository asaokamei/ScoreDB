<?php
namespace WScore\ScoreDB;

use WScore\ScoreDB\Entity\ActiveRecord;
use WScore\ScoreDB\Entity\EntityObject;
use WScore\ScoreDB\Hook\Hooks;

/**
 * Class Dao
 * @package WScore\ScoreDB
 *
 * A Data Access Object.
 *
 */
class Dao extends Query
{
    /**
     * @var string    name of table.
     */
    protected $table;

    /**
     * @var string    name of (primary) key.
     */
    protected $keyName;

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
     * date format used in the database system.
     *
     * @var string
     */
    protected $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * for mutating date values to/from DateTime objects.
     *
     * @var array
     */
    protected $dates = array();

    /**
     * specify the keys that are fillable.
     *
     * @var array
     */
    protected $fillable = array();

    /**
     * class name used as fetched object.
     *
     * @var null|string
     */
    protected $fetch_class = null;

    // +----------------------------------------------------------------------+
    //  construction and object management
    // +----------------------------------------------------------------------+
    /**
     * sets table and keyName from class name if they are not set.
     *
     * @param Hooks $hook
     */
    public function __construct( $hook=null )
    {
        if( $hook ) {
            $hook->hookEvent('onConstructingHook',  'WScore\ScoreDB\Dao\TableAndKeyName' );
            $hook->hookEvent('onCreateStampFilter', 'WScore\ScoreDB\Dao\TimeStamp' );
            $hook->hookEvent('onUpdateStampFilter', 'WScore\ScoreDB\Dao\TimeStamp' );
            $hook->setScope($this);
            $hook->setMutant($this);
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
     * @param string|int $key
     * @return array|\PdoStatement
     */
    public static function find($key)
    {
        return static::query()->key($key)->select();
    }

    /**
     * @param string|int $key
     * @return array|\PdoStatement
     * @throws \InvalidArgumentException
     */
    public static function findOrFail($key)
    {
        if( $found = static::find($key) ) return $found;
        throw new \InvalidArgumentException('Key Not Found');
    }

    /**
     * @param string|int $key
     * @param array $data
     * @return \PdoStatement
     */
    public static function modify($key, $data)
    {
        return static::query()->key($key)->update($data);
    }

    /**
     * @param $data
     * @return bool|int
     */
    public static function inject($data)
    {
        return static::query()->insert($data);
    }

    /**
     * @param array $data
     * @return bool|int
     */
    public static function create($data=array())
    {
        $query = static::query();
        $entity = $query->entity($data);
        return $entity;
    }

    /**
     * @param array $data
     * @return EntityObject|ActiveRecord
     */
    public function entity($data=array())
    {
        $class  = $this->fetch_class;
        /** @var EntityObject $entity */
        $entity = new $class($this);
        $entity->fill($data);
        return $data;
    }

    /**
     * overwrite this method to set fetch mode.
     *
     * @param \PdoStatement $stm
     * @return bool
     */
    protected function setFetchClass( $stm )
    {
        return $stm->setFetchMode( \PDO::FETCH_CLASS, $this->fetch_class, [$this] );
    }

    /**
     * magic for scope methods.
     *
     * @param $method
     * @param $args
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call( $method, $args )
    {
        if( $this->hooks && $this->hooks->scope( $method, $this, $args ) ) {
            return $this;
        }
        throw new \BadMethodCallException( 'no such method: '.$method );
    }

    // +----------------------------------------------------------------------+
    //  get/set values.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * @return array
     */
    public function getTimeStamps()
    {
        return $this->timeStamps;
    }

    /**
     * mutate from a string to an object.
     *
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function mutate( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            return new \DateTime($value);
        }
        return $this->hooks->mutate( $key, $value, 'set' );
    }

    /**
     * mutate back to a string from an object.
     *
     * @param string $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return string
     */
    public function muteBack( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            return $this->muteBackDateTime($key, $value);
        }
        if( is_object($value) && method_exists( $value, '__toString') ) {
            return (string) $value;
        }
        return $this->hooks->mutate( $key, $value, 'get' );
    }

    /**
     * @param string $key
     * @param \DateTime|mixed $value
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function muteBackDateTime( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            if( is_string($value) ) {
                $date = new \DateTime($value);
            } elseif( $value instanceof \DateTime ) {
                $date = $value;
            } else {
                throw new \InvalidArgumentException();
            }
            return $date->format($this->dateTimeFormat);
        }
        return $value;
    }

    /**
     * filter data which has only fillable keys.
     *
     * @param array $data
     * @return array
     */
    public function filterFillable( $data )
    {
        foreach( $data as $key => $value ) {
            if( !in_array( $key, $this->fillable ) ) {
                unset( $data[$key] );
            }
        }
        return $data;
    }

    // +----------------------------------------------------------------------+
}