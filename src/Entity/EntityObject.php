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
class EntityObject
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
        if( !empty($this->data) ) {
            $this->isFetched = true;
            $this->original_data = $this->data;
        }
    }

    // +----------------------------------------------------------------------+
    //  database access
    // +----------------------------------------------------------------------+
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
     * @param string $key
     * @return mixed
     */
    public function __get( $key )
    {
        return $this->__isset( $key ) ? $this->data[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws \InvalidArgumentException
     */
    public function __set( $key, $value )
    {
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