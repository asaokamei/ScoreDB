<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Entity\EntityAbstract;
use WScore\ScoreDB\Query;
use WScore\ScoreSql\Sql\Where;

abstract class AbstractRelation implements RelationInterface
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
    protected $entity;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var Where
     */
    protected $where;

    /**
     * @var \Closure
     */
    protected $onQueryCallBack;

    /**
     * @param EntityAbstract $entity
     * @return RelationInterface
     */
    public function entity( $entity )
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param string|\Closure $order
     * @return RelationInterface
     */
    public function orderBy( $order )
    {
        $this->orderBy = $order;
        return $this;
    }

    /**
     * @param Where $where
     * @return RelationInterface
     */
    public function where( $where )
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param string $table
     * @return Query
     */
    protected function getQuery( $table )
    {
        return DB::query( $table );
    }

    public function onQuery( $callback )
    {
        $this->onQueryCallBack = $callback;
        return $this;
    }

    /**
     * @param Dao       $dao
     * @param int|array $keys
     * @param string    $column
     * @return array|mixed
     */
    protected function load( $dao, $keys, $column )
    {
        $query = $dao::query();
        if( $this->orderBy ) $query->order( $this->orderBy );
        if( $this->where   ) $query->where( $this->where );
        if( $this->onQueryCallBack ) {
            $q = $this->onQueryCallBack;
            $q( $query );
        }
        return $query->load( $keys, $column );
    }

    /**
     * @param $column
     * @return $this
     */
    public function setTargetCol( $column )
    {
        $this->targetCol = $column;
        return $this;
    }

}