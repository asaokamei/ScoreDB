<?php
namespace WScore\ScoreDB;

use Aura\Sql\ExtendedPdo;
use PdoStatement;
use WScore\ScoreDB\Entity\ActiveRecord;
use WScore\ScoreDB\Entity\EntityObject;
use WScore\ScoreDB\Hook\Events;
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
     * assume it is auto-incremented key for generic dao.
     *
     * @var bool
     */
    protected $returnLastId = true;

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

    protected $fetch_class = 'WScore\ScoreDB\Entity\EntityObject';

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
        $this->setUpHooks( $hook );
        $this->hook( 'constructing' );
        $this->hook( 'constructed' );
    }

    /**
     * @param Hooks $hook
     * @return Dao
     */
    protected function setUpHooks($hook=null)
    {
        $hook = $hook ?: new Hooks();
        $hook->hookEvent( Events::ANY_EVENT, $this );
        $hook->hookEvent( 'onConstructingHook',  'WScore\ScoreDB\Dao\TableAndKeyName' );
        $hook->hookEvent( 'onCreateStampFilter', 'WScore\ScoreDB\Dao\TimeStamp' );
        $hook->hookEvent( 'onUpdateStampFilter', 'WScore\ScoreDB\Dao\TimeStamp' );
        $hook->setScope(  $this);
        $hook->setMutant( $this);
        $hook->setDates(  $this->dates, $this->dateTimeFormat );
        $this->hooks = $hook;
        return $this;
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
    protected function hook( $event, $data=null )
    {
        if( $this->hooks ) {
            $data = $this->hooks->hook( $event, $data, $this );
            if( $this->hooks->usesFilterData() ) {
                $this->filteredData = $data;
                $this->useFilteredFlag = true;
            }
        }
        return $data;
    }

    /**
     * @param ExtendedPdo $pdo
     * @return mixed
     */
    protected function perform( $pdo )
    {
        if( $this->useFilteredFlag ) {
            $this->useFilteredFlag = false;
            return $this->filteredData;
        }
        return parent::perform( $pdo );
    }

    /**
     * overwrite this method to set fetch mode.
     *
     * @param PdoStatement $stm
     * @return bool
     */
    protected function setFetchClass( $stm )
    {
        return $stm->setFetchMode( \PDO::FETCH_CLASS, $this->fetch_class, [$this] );
    }

    // +----------------------------------------------------------------------+
    //  static methods
    // +----------------------------------------------------------------------+
    /**
     * @return Dao|$this
     */
    public static function query()
    {
        /** @var Dao $self */
        return new static();
    }

    /**
     * @param string|int $key
     * @throws \InvalidArgumentException
     * @return array|EntityObject|ActiveRecord
     */
    public static function find($key)
    {
        if( !$key ) {
            throw new \InvalidArgumentException('no such key to find');
        }
        $found = static::query()->key($key)->select();
        if( $found && is_array($found) ) return $found[0];
        return $found;
    }

    /**
     * @param string|int $key
     * @return array|PdoStatement
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
     * @return PdoStatement
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
     * @return EntityObject|ActiveRecord
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
        return $entity;
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
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function mutate( $name, $value )
    {
        return $this->hooks ? $this->hooks->muteInto( $name, $value ) : $value;
    }

    /**
     * mutate back to a string from an object.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return string
     */
    public function muteBack( $name, $value )
    {
        return $this->hooks ? $this->hooks->muteBack( $name, $value ) : $value;
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
    //  DB access methods (overwriting Query's methods).
    // +----------------------------------------------------------------------+
    /**
     * @param null|int $limit
     * @return array|mixed
     */
    public function select($limit=null)
    {
        $limit = $this->hook( 'selecting', $limit );
        $data = parent::select($limit);
        $data = $this->hook( 'selected', $data );
        return $data;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->hook( 'counting' );
        $count = parent::count();
        $count = $this->hook( 'counted', $count );
        return $count;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array|mixed
     */
    public function load( $id, $column=null )
    {
        list( $id, $column ) = $this->hook( 'loading', [ $id, $column ] );
        $this->key($id, $column);
        $data = parent::select();
        $data = $this->hook( 'loaded', $data );
        $this->reset();
        return $data;
    }

    /**
     * @param $data
     * @throws \InvalidArgumentException
     * @return int|PdoStatement
     */
    public function save( $data )
    {
        $by   = $this->hook( 'saveMethod', $data );
        if( !$by ) {
            throw new \InvalidArgumentException( 'save method not defined. ' );
        }
        $data = $this->hook( 'saving', $data );
        $stmt = $this->$by( $data);
        $stmt = $this->hook( 'saved', $stmt );
        return $stmt;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        $data = $this->hook( 'createStamp', $data );
        $data = $this->hook( 'inserting', $data );
        $id = parent::insert($data);
        $id = $this->hook( 'inserted', $id );
        return $id;
    }

    /**
     * @param array $data
     * @return bool|int|PdoStatement
     * @throws \InvalidArgumentException
     */
    public function replace( $data )
    {
        if( !isset( $data[$this->keyName] ) ) {
            throw new \InvalidArgumentException;
        }
        $key = $data[$this->keyName];
        if( $this::find($key) ) {
            $this->key($key)->update($data);
            return $key;
        } else {
            return $this->insert($data);
        }
    }

    /**
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        $data = $this->hook( 'updateStamp', $data );
        $data = $this->hook( 'updating', $data );
        $stmt = parent::update($data);
        $stmt = $this->hook( 'updated', $stmt );
        return $stmt;
    }

    /**
     * @param int $id
     * @param string $column
     * @return PdoStatement
     */
    public function delete( $id=null, $column=null )
    {
        list( $id, $column ) = $this->hook( 'deleting', [ $id, $column ] );
        $stmt = parent::delete($id, $column);
        $stmt = $this->hook( 'deleted', $stmt );
        return $stmt;
    }

    // +----------------------------------------------------------------------+
}