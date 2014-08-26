<?php
namespace WScore\ScoreDB\Entity;

/**
 * Class ArrayAccessTrait
 * @package WScore\ScoreDB\Entity
 *
 * a trait to enable ArrayAccess for EntityObject.
 */
trait ArrayAccessTrait
{
    // +----------------------------------------------------------------------+
    //  to enable ArrayAccess for EntityObject.
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
        $this->unsetData($key);
    }

}