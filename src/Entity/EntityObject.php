<?php
namespace WScore\ScoreDB\Entity;

use WScore\ScoreDB\Dao;

/**
 * Class EntityObject
 * @package WScore\ScoreDB\Entity
 *
 * A generic entity object class, with Active Record type feature.
 *
 * set fetch mode to PDO::FETCH_CLASS in PDOStatement when
 * retrieving data as EntityObject.
 *
 */
class EntityObject implements \ArrayAccess
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

    /**
     * @var bool   set to true to disable db access (save and delete).
     */
    protected $immuneDbAccess = false;

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
        return $this->get($key);
    }

    /**
     * @throws \BadMethodCallException
     * @return $this
     */
    public function save()
    {
        if( $this->isImmune() ) {
            throw new \BadMethodCallException();
        }
        if( $this->isFetched ) {
            $modified = $this->getModified();
            $this->dao->key( $this->getKey() );
            $this->dao->update( $modified );
        } else {
            $this->dao->insert( $this->data );
        }
        return $this;
    }

    /**
     * deletes
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function delete()
    {
        if( $this->isImmune() ) {
            throw new \BadMethodCallException();
        }
        if( $this->isFetched ) {
            $this->dao->update( $this->getKey() );
        }
        return $this;
    }

    /**
     * @param bool $immune
     * @return $this
     */
    public function immune($immune=true)
    {
        $this->immuneDbAccess = $immune;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImmune()
    {
        return $this->immuneDbAccess;
    }

    /**
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
            $this->set( $key, $value );
        }
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get( $key )
    {
        return $this->get( $key );
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws \InvalidArgumentException
     */
    public function __set( $key, $value )
    {
        if( !$this->modsBySet ) {
            throw new \InvalidArgumentException( "Cannot modify property in Entity object" );
        }
        $this->set( $key, $value );
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset( $key )
    {
        return $this->exists( $key );
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get( $key )
    {
        $found = $this->dao->mutate( $key, $this->getRaw($key) );
        return $found;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getRaw( $key )
    {
        return $this->exists( $key ) ? $this->data[$key] : null;
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

    /**
     * @param mixed $key
     * @throws \InvalidArgumentException
     * @return void
     */
    public function unsetData( $key )
    {
        if( isset( $this->data[$key]) ) unset( $this->data[$key] );
    }

    /**
     * @return array
     */
    public function getModified()
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
        $this->unsetData($key);
    }

    // +----------------------------------------------------------------------+
}