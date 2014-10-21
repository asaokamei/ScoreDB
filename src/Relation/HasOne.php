<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Entity\EntityAbstract;

/**
 * User: Asao Kamei
 * Date: 2014/10/18
 * Time: 10:35
 */
class HasOne extends AbstractRelation
{
    /**
     * @var Dao
     */
    protected $sourceDao;

    /**
     * @var string
     */
    protected $sourceCol;

    /**
     * @var EntityAbstract
     */
    protected $entity;

    /**
     * @var string
     */
    protected $targetDao;

    /**
     * @var string
     */
    protected $targetCol;

    /**
     * @var EntityAbstract
     */
    protected $target;

    /**
     * @param Dao            $sourceDao
     * @param Dao|string     $targetDao
     * @param string         $sourceCol
     */
    public function __construct( $sourceDao, $targetDao, $sourceCol )
    {
        $this->sourceDao = $sourceDao;
        $this->sourceCol = $sourceCol ?: $sourceDao->getKeyName();
        $this->targetDao = $targetDao;
        $this->targetCol = $this->sourceCol;
    }

    /**
     * @return EntityAbstract
     */
    public function get()
    {
        /** @var Dao $targetName */
        $targetName   = $this->targetDao;
        $sourceKey    = $this->entity->_getRaw( $this->sourceCol );
        $this->target = $targetName::query()->load( $sourceKey, $this->targetCol );
        if ( $this->target ) {
            $this->target = $this->target[ 0 ];
        }
        return $this->target;
    }

    /**
     * @param EntityAbstract $target
     * @return EntityAbstract
     */
    public function link( $target )
    {
        $sourceCol = $this->sourceCol;
        if ( !$key = $target->_getRaw( $this->targetCol ) ) {
            throw new \RuntimeException( 'lazy relation not supported' );
        }
        $this->entity->$sourceCol = $key;
        $this->target             = $target;
        return $this->target;
    }

    /**
     * @param EntityAbstract $target
     * @return $this
     */
    public function unlink( $target = null )
    {
        $sourceCol                = $this->sourceCol;
        $this->entity->$sourceCol = null;

        return $this;
    }
}