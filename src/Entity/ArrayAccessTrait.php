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
    abstract public function __isset($key);
    abstract public function __get($key);
    abstract public function __set( $key, $value);
    abstract public function __unset($key);

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
        return $this->__isset( $key );
    }

    /**
     * Offset to retrieve
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet( $key )
    {
        $found = $this->__get( $key );
        if( is_string($found) ) {
            $found = htmlspecialchars($found, ENT_QUOTES, 'UTF-8' );
        }
        return $found;
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
        $this->__set( $key, $value );
    }

    /**
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return void
     */
    public function offsetUnset( $key )
    {
        $this->__unset($key);
    }

}