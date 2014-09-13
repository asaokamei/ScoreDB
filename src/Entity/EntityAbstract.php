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
    protected $_data = array();

    /**
     * @var string
     */
    protected $_keyName;
    
    /**
     * @var array
     */
    protected $_original_data = array();

    /**
     * @var Dao
     */
    protected $_dao;

    /**
     * check if this entity object is fetched from db.
     * the $this->data is filled before constructor is called.
     *
     * @var bool
     */
    protected $_isFetched = false;

    // +----------------------------------------------------------------------+
    //  constructors and managing values
    // +----------------------------------------------------------------------+
    /**
     * @param Dao $dao
     */
    public function __construct( $dao )
    {
        $this->_dao = $dao;
        $this->_keyName = $dao->getKeyName();
        if( !empty($this->_data) ) {
            $this->_isFetched = true;
            $this->_original_data = $this->_data;
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
        return $this->__get($this->_keyName);
    }

    /**
     * check if the entity object is fetched from database.
     *
     * @return bool
     */
    public function isFetched()
    {
        return $this->_isFetched;
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
        $data = $this->_dao->filterFillable($data);
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
        if( $this->_dao ) {
            $value = $this->_dao->mutate( $key, $value );
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set( $key, $value )
    {
        if( $this->_dao ) {
            $value = $this->_dao->muteBack( $key, $value );
        }
        $this->_data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset( $key )
    {
        return isset( $this->_data[$key] );
    }

    /**
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __unset( $key )
    {
        if( isset( $this->_data[$key]) ) unset( $this->_data[$key] );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function _getRaw( $key=null )
    {
        if( !$key ) return $this->_data;
        return $this->__isset( $key ) ? $this->_data[$key] : null;
    }

    /**
     * @return array
     */
    public function _getModified()
    {
        $modified = array();
        foreach ( $this->_data as $key => $value ) {
            if ( !array_key_exists( $key, $this->_original_data ) || $value !== $this->_original_data[ $key ] ) {
                $modified[ $key ] = $value;
            }
        }
        return $modified;
    }

    // +----------------------------------------------------------------------+
}