<?php
namespace WScore\ScoreDB;

use WScore\ScoreDB\Hook\Hooks;

class Dao extends Query
{
    use Dao\TimeStampTrait;

    use Dao\TableAndKeyNameTrait;

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
     * overwrite this method to set fetch mode.
     *
     * @param \PdoStatement $stm
     * @return bool
     */
    protected function setFetchMode( $stm )
    {
        if( !$this->fetch_class ) {
            return $stm->setFetchMode( \PDO::FETCH_ASSOC );
        }
        return $this->setFetchClass($stm);
    }

    /**
     * @param \PdoStatement $stm
     * @return bool
     */
    protected function setFetchClass( $stm )
    {
        return $stm->setFetchMode( \PDO::FETCH_CLASS, $this->fetch_class, $this );
    }

    // +----------------------------------------------------------------------+
    //  get/set values.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function mutate( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            return new \DateTimeImmutable($value);
        }
        return $this->hooks->mutate( $key, $value, 'set' );
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return string
     */
    public function muteBack( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            if( is_string($value) ) {
                $value = new \DateTime($value);
            } elseif( !$value instanceof \DateTime ) {
                throw new \InvalidArgumentException();
            }
            return $value->format($this->dateTimeFormat);
        }
        return $this->hooks->mutate( $key, $value, 'get' );
    }

    /**
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