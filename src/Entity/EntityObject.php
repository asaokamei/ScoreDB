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
     * allow to set/alter values via magic __set method.
     *
     * @var bool
     */
    protected $modsBySet = true;

    /**
     * @param Dao $dao
     */
    public function __construct( $dao )
    {
        $this->dao       = $dao;
        $this->modsBySet = false;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fill( $data )
    {
        $this->data = array_merge( $this->data, $data );
        return $this;
    }

    /**
     * @param $offset
     * @return null
     */
    public function __get( $offset )
    {
        return $this->get( $offset );
    }

    /**
     * @param $offset
     * @return null
     */
    public function get( $offset )
    {
        $found = $this->exists( $offset ) ? $this->data[$offset] : null;
        $found = $this->dao->mutate( $offset, $found );
        return $found;
    }

    /**
     * Whether a offset exists
     * @param mixed $offset
     * @return boolean
     */
    public function exists( $offset )
    {
        return isset( $this->data[$offset] );
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @return $this
     */
    public function set( $offset, $value )
    {
        $this->data[$offset] = $this->dao->muteBack( $offset, $value );
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  for ArrayAccess. restricted access via array access.
    // +----------------------------------------------------------------------+
    /**
     * Whether a offset exists
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return $this->exists( $offset );
    }

    /**
     * Offset to retrieve
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        return $this->get( $offset );
    }

    /**
     * sets value to offset, only if the offset is not in the property list.
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    public function offsetSet( $offset, $value )
    {
        if( !$this->modsBySet ) {
            throw new \InvalidArgumentException( "Cannot modify property in Entity object" );
        }
        $this->set( $offset, $value );
    }

    /**
     * @param mixed $offset
     * @throws \InvalidArgumentException
     * @return void
     */
    public function offsetUnset( $offset )
    {
        if( !$this->modsBySet ) {
            throw new \InvalidArgumentException( "Cannot modify property in Entity object" );
        }
        if( isset( $this->data[$offset]) ) unset( $this->data[$offset] );
    }

    // +----------------------------------------------------------------------+
}