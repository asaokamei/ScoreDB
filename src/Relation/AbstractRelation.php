<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Entity\EntityAbstract;

abstract class AbstractRelation implements RelationInterface
{

    /**
     * @param EntityAbstract $entity
     * @return RelationInterface
     */
    public function entity( $entity )
    {
        $this->entity = $entity;
        return $this;
    }

}