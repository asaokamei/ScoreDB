<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Entity\EntityAbstract;

/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/10/18
 * Time: 12:41
 */
class HasJoin implements RelationInterface
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
     * @var string|Dao
     */
    protected $joinDao;

    /**
     * @var string
     */
    protected $joinSourceCol;

    /**
     * @var string
     */
    protected $joinTargetCol;

    /**
     * @var string
     */
    protected $targetDao;

    /**
     * @var string
     */
    protected $targetCol;

    /**
     * @var EntityAbstract[]
     */
    protected $target = [ ];

    /**
     * @param Dao            $sourceDao
     * @param Dao|string     $targetName
     * @param EntityAbstract $entity
     * @param null|string    $joinDao
     */
    public function __construct( $sourceDao, $targetName, $entity, $joinDao = null )
    {
        $this->sourceDao     = $sourceDao;
        $this->sourceCol     = $sourceDao->getKeyName();
        $this->targetDao     = $targetName;
        $this->targetCol     = $this->sourceCol;
        $this->entity        = $entity;
        $this->joinDao       = $joinDao ?: function ( $targetName ) use ( $sourceDao ) {
            /** @var Dao $targetName */
            $list = [ $targetName::query()->getTable, $sourceDao::query()->getTable() ];
            sort( $list );
            return implode( '_', $list );
        };
        $this->joinSourceCol = $this->sourceCol;
        $this->joinTargetCol = $this->targetCol;
    }

    /**
     * @return EntityAbstract[]
     */
    public function get()
    {
        // key to search for...  
        $sourceKey = $this->entity->_getRaw( $this->sourceCol );
        if ( !$sourceKey ) return $this->target;
        $joinList = $this->getJoinDao()->load( $sourceKey, $this->joinSourceCol );

        // get the target's key list. 
        $joinSourceCol = $this->joinSourceCol;
        $targetKeys    = [ ];
        foreach ( $joinList as $j ) {
            $targetKeys[ ] = $j[ $joinSourceCol ];
        }
        // get the targets
        $targetDao    = $this->targetDao;
        $this->target = $targetDao::query()->load( $this->targetCol, $targetKeys );
        return $this->target;
    }

    /**
     * @return Dao
     */
    protected function getJoinDao()
    {
        $joinDao = $this->joinDao;
        if ( class_exists( $joinDao ) && $joinDao instanceof Dao ) {
            return $joinDao::query();
        }
        return $this->sourceDao->query()->table( $joinDao );
    }

    /**
     * relates the target(s).
     * if the target is a single record, it adds to the relation.
     * if the target is an array of records, it replaces the relation.
     *
     * @param EntityAbstract|EntityAbstract[] $target
     * @return $this|RelationInterface
     */
    public function link( $target )
    {
        if ( $target instanceof EntityAbstract ) {
            $this->addLink( $target );
        }
        else {
            $this->clean();
            foreach ( $target as $t ) {
                $this->addLink( $t );
            }
        }
        return $this;
    }

    /**
     * removes the relation to the target.
     *
     * @param EntityAbstract $target
     * @return $this|RelationInterface
     */
    public function unlink( $target = null )
    {
        $sourceKey = $this->entity->_getRaw( $this->sourceCol );
        $targetKey = $target->_getRaw( $this->targetCol );
        $this->getJoinDao()->where(
            $this->sourceDao->given( $this->joinSourceCol )->is( $sourceKey )
                ->given( $this->joinTargetCol )->is( $targetKey )
        )->delete();

        return $this;
    }

    /**
     * clean up the relation (delete all the related record in join table).
     */
    protected function clean()
    {
        $sourceKey = $this->entity->_getRaw( $this->sourceCol );
        $this->getJoinDao()->where(
            $this->sourceDao->given( $this->joinSourceCol )->is( $sourceKey )
        )->delete();
    }

    /**
     * @param EntityAbstract $target
     * @return bool
     */
    protected function addLink( $target )
    {
        // key to search for...  
        $sourceKey = $this->entity->_getRaw( $this->sourceCol );
        $targetKey = $target->_getRaw( $this->targetCol );
        $this->getJoinDao()->where(
            $this->sourceDao->given( $this->joinSourceCol )->is( $sourceKey )
        )->inject( [
            $this->joinSourceCol => $sourceKey,
            $this->joinTargetCol => $targetKey,
        ] );
        $this->target[ ] = $target;
    }
}