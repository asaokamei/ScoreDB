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
        if( $this->isFetched ) {
            $modified = $this->_getModified();
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
}