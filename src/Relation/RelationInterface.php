<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Entity\EntityAbstract;
use WScore\ScoreSql\Sql\Where;

/**
 * Created by PhpStorm.
 * User: Asao Kamei
 * Date: 2014/10/18
 * Time: 10:32
 */
interface RelationInterface
{
    /**
     * @param EntityAbstract $entity
     * @return RelationInterface
     */
    public function entity( $entity );

    /**
     * @return EntityAbstract|EntityAbstract[]
     */
    public function get();

    /**
     * @param EntityAbstract|EntityAbstract[] $target
     * @return RelationInterface
     */
    public function link( $target );

    /**
     * @param EntityAbstract $target
     * @return RelationInterface
     */
    public function unlink( $target=null );

    /**
     * @param string|\Closure $order
     * @return RelationInterface
     */
    public function orderBy( $order );

    /**
     * @param Where $where
     * @return RelationInterface
     */
    public function where( $where );
}