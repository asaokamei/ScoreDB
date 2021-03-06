<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Entity\EntityAbstract;

/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/10/18
 * Time: 11:16
 */
class HasMany extends AbstractRelation
{
    /**
     * @var EntityAbstract[]
     */
    protected $target = [ ];

    /**
     * @param Dao            $sourceDao
     * @param Dao|string     $targetDao
     */
    public function __construct( $sourceDao, $targetDao )
    {
        $this->sourceDao = $sourceDao;
        $this->sourceCol = $sourceDao->getKeyName();
        $this->targetDao = $targetDao;
        $this->targetCol = $this->sourceCol;
    }

    /**
     * @return EntityAbstract[]
     */
    public function get()
    {
        /** @var Dao $targetName */
        $sourceKey    = $this->entity->_getRaw( $this->sourceCol );
        $this->target = $this->load( $this->targetDao, $sourceKey, $this->targetCol );
        return $this->target;
    }

    /**
     * @param EntityAbstract|EntityAbstract[] $target
     * @return $this|RelationInterface
     */
    public function link( $target )
    {
        if ( $target instanceof EntityAbstract ) {
            $target = [ $target ];
        }
        if ( !$sourceKey = $this->entity->_getRaw( $this->sourceCol ) ) {
            throw new \RuntimeException( 'lazy relation not supported' );
        }
        $targetCol = $this->targetCol;
        foreach ( $target as $tgt ) {
            $tgt->$targetCol = $sourceKey;
            $this->target[ ] = $tgt;
        }
        return $this;
    }

    /**
     * @param EntityAbstract $target
     * @return $this|RelationInterface
     */
    public function unlink( $target = null )
    {
        $targetCol          = $this->targetCol;
        $target->$targetCol = null;

        return $this;
    }
}