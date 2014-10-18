<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Entity\EntityAbstract;

/**
 * User: Asao Kamei
 * Date: 2014/10/18
 * Time: 10:35
 */
class HasOne implements RelationInterface
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
    protected $targetName;

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
     * @param Dao|string     $targetName
     * @param string         $sourceCol
     * @param EntityAbstract $entity
     */
    public function __construct( $sourceDao, $targetName, $sourceCol, $entity )
    {
        $this->sourceDao  = $sourceDao;
        $this->sourceCol  = $sourceCol;
        $this->targetName = $targetName;
        $this->targetCol  = $sourceCol;
        $this->entity     = $entity;
    }

    /**
     * @return EntityAbstract
     */
    public function get()
    {
        /** @var Dao $targetName */
        $targetName   = $this->targetName;
        $sourceKey    = $this->entity->_getRaw( $this->sourceCol );
        $this->target = $targetName::fetch( $sourceKey, $this->targetCol );
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
    public function unlink( $target=null )
    {
        $sourceCol = $this->sourceCol;
        $this->entity->$sourceCol = null;
        
        return $this;
    }
}