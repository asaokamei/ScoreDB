<?php
namespace WScore\ScoreDB\Entity;

use WScore\ScoreDB\Dao;

/**
 * Class ActiveRecord
 * @package WScore\ScoreDB\Entity
 *
 * A generic Active Record type entity object.
 *
 * set fetch mode to PDO::FETCH_CLASS in PDOStatement when
 * retrieving data as EntityObject.
 *
 */
class ActiveRecord extends EntityObject
{
    /**
     * @var bool   set to true to disable db access (save and delete).
     */
    protected $immuneDbAccess = false;

    // +----------------------------------------------------------------------+
    //  database access
    // +----------------------------------------------------------------------+
    /**
     * saves to database.
     * updates if fetched, inserted if it's a new entity.
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function save()
    {
        if( $this->isImmune() ) {
            throw new \BadMethodCallException();
        }
        $dao = $this->_dao();
        if( $this->isFetched() ) {
            $modified = $this->_getModified();
            $dao::modify( $this->getKey(), $modified );
        } else {
            $id = $dao::inject( $this->_data );
            $this->__set( $this->_keyName, $id );
            $this->_isFetched = true;
        }
        $this->_original_data = $this->_data;
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
        if( $this->isFetched() ) {
            $this->_dao()->delete( $this->getKey() );
        }
        return $this;
    }

    /**
     * disable save/delete to database.
     *
     * @param bool $immune
     * @return $this
     */
    public function immune($immune=true)
    {
        $this->immuneDbAccess = $immune;
        return $this;
    }

    /**
     * check if the entity object is immunized.
     *
     * @return bool
     */
    public function isImmune()
    {
        return $this->immuneDbAccess;
    }

    // +----------------------------------------------------------------------+
}