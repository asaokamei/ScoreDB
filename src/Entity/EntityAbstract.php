<?php
namespace WScore\ScoreDB\Entity;

use WScore\ScoreDB\Dao;

/**
 * Class EntityObject
 * @package WScore\ScoreDB\Entity
 *
 * A generic entity object class.
 *
 */
abstract class EntityAbstract
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $original_data = array();

    /**
     * @var Dao
     */
    protected $dao;

    /**
     * check if this entity object is fetched from db.
     * the $this->data is filled before constructor is called.
     *
     * @var bool
     */
    protected $isFetched = false;

    // +----------------------------------------------------------------------+
    //  constructors and managing values
    // +----------------------------------------------------------------------+
    /**
     * @param Dao $dao
     */
    public function __construct( $dao )
    {
        $this->dao = $dao;
        if( !empty($this->data) ) {
            $this->isFetched = true;
            $this->original_data = $this->data;
        }
    }

    // +----------------------------------------------------------------------+
    //  database access
    // +----------------------------------------------------------------------+
    /**
     * @return mixed
     */
    public function getKey()
    {
        $key = $this->dao->getKeyName();
        return $this->__get($key);
    }

    /**
     * check if the entity object is fetched from database.
     *
     * @return bool
     */
    public function isFetched()
    {
        return $this->isFetched;
    }

    // +----------------------------------------------------------------------+
    //  property accessor
    // +----------------------------------------------------------------------+
    /**
     * @param array $data
     * @return $this
     */
    public function fill( $data )
    {
        $data = $this->dao->filterFillable($data);
        foreach( $data as $key => $value ) {
            $this->__set( $key, $value );
        }
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get( $key )
    {
        $value = $this->_getRaw($key);
        if( $this->dao ) {
            $value = $this->dao->mutate( $key, $value );
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set( $key, $value )
    {
        if( $this->dao ) {
            $value = $this->dao->muteBack( $key, $value );
        }
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset( $key )
    {
        return isset( $this->data[$key] );
    }

    /**
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __unset( $key )
    {
        if( isset( $this->data[$key]) ) unset( $this->data[$key] );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function _getRaw( $key )
    {
        return $this->__isset( $key ) ? $this->data[$key] : null;
    }

    /**
     * @return array
     */
    public function _getModified()
    {
        $modified = array();
        foreach ( $this->data as $key => $value ) {
            if ( !array_key_exists( $key, $this->original_data ) || $value !== $this->original_data[ $key ] ) {
                $modified[ $key ] = $value;
            }
        }
        return $modified;
    }

    // +----------------------------------------------------------------------+
}