<?php
namespace WScore\ScoreDB\Relation;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Entity\EntityAbstract;

/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/10/18
 * Time: 15:38
 */
class Relation
{
    /**
     * @param Dao            $sourceDao
     * @param Dao            $targetDao
     * @param string         $sourceCol
     * @param EntityAbstract $entity
     * @return HasOne
     */
    public static function hasOne( $sourceDao, $targetDao, $sourceCol, $entity )
    {
        return new HasOne( $sourceDao, $targetDao, $sourceCol, $entity );
    }

    /**
     * @param Dao            $sourceDao
     * @param Dao            $targetName
     * @param EntityAbstract $entity
     * @return HasMany
     */
    public static function hasMany( $sourceDao, $targetName, $entity )
    {
        return new HasMany( $sourceDao, $targetName, $entity );
    }

    /**
     * @param Dao            $sourceDao
     * @param Dao            $targetName
     * @param EntityAbstract $entity
     * @param null|string    $joinDao
     * @return HasJoin
     */
    public static function hasJoin( $sourceDao, $targetName, $entity, $joinDao = null )
    {
        return new HasJoin( $sourceDao, $targetName, $entity, $joinDao );
    }
}