<?php
namespace WScore\ScoreDB\Entity;

use WScore\ScoreDB\Dao;

class EntityObject implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $data = array();

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

    /**
     * allow to set/alter values via magic __set method.
     *
     * @var bool
     */
    protected $modsBySet = true;

    // +----------------------------------------------------------------------+
    //  constructors and managing values
    // +----------------------------------------------------------------------+
    /**
     * @param Dao $dao
     */
    public function __construct( $dao )
    {
        $this->dao       = $dao;
        $this->modsBySet = false;
        if( !empty($this->data) ) {
            $this->isFetched = true;
        }
    }

    /**
     * @param $data
     * @return $this
     */
    public function fill( $data )
    {
        $data = $this->dao->filterFillable($data);
        foreach( $data as $key => $value ) {
            $this->set( $key, $value );
        }
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function __get( $key )
    {
        return $this->get( $key );
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get( $key )
    {
        $found = $this->exists( $key ) ? $this->data[$key] : null;
        $found = $this->dao->mutate( $key, $found );
        return $found;
    }

    /**
     * Whether a offset exists
     * @param mixed $key
     * @return boolean
     */
    public function exists( $key )
    {
        return isset( $this->data[$key] );
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set( $key, $value )
    {
        $this->data[$key] = $this->dao->muteBack( $key, $value );
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  simple Active Record
    // +----------------------------------------------------------------------+
    /**
     * @return mixed
     */
    public function getKey()
    {
        $key = $this->dao->getKeyName();
        return $this->get($key);
    }

    /**
     * @return $this
     */
    public function save()
    {
        if( $this->isFetched ) {
            $this->dao->key( $this->getKey() );
            $this->dao->update( $this->data );
        } else {
            $this->dao->insert( $this->data );
        }
        return $this;
    }

    /**
     * deletes
     *
     * @return $this
     */
    public function delete()
    {
        if( $this->isFetched ) {
            $this->dao->update( $this->getKey() );
        }
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  for ArrayAccess. restricted access via array access.
    // +----------------------------------------------------------------------+
    /**
     * Whether a offset exists
     * @param mixed $key
     * @return boolean
     */
    public function offsetExists( $key )
    {
        return $this->exists( $key );
    }

    /**
     * Offset to retrieve
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet( $key )
    {
        return $this->get( $key );
    }

    /**
     * sets value to offset, only if the offset is not in the property list.
     *
     * @param mixed $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    public function offsetSet( $key, $value )
    {
        if( !$this->modsBySet ) {
            throw new \InvalidArgumentException( "Cannot modify property in Entity object" );
        }
        $this->set( $key, $value );
    }

    /**
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return void
     */
    public function offsetUnset( $key )
    {
        if( !$this->modsBySet ) {
            throw new \InvalidArgumentException( "Cannot modify property in Entity object" );
        }
        if( isset( $this->data[$key]) ) unset( $this->data[$key] );
    }

    // +----------------------------------------------------------------------+
}