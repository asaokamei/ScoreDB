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
     * @return HasOne
     */
    public static function hasOne( $sourceDao, $targetDao, $sourceCol )
    {
        return new HasOne( $sourceDao, $targetDao, $sourceCol );
    }

    /**
     * @param Dao            $sourceDao
     * @param Dao            $targetName
     * @return HasMany
     */
    public static function hasMany( $sourceDao, $targetName )
    {
        return new HasMany( $sourceDao, $targetName );
    }

    /**
     * @param Dao            $sourceDao
     * @param Dao            $targetName
     * @param null|string    $joinDao
     * @return HasJoin
     */
    public static function hasJoin( $sourceDao, $targetName, $joinDao = null )
    {
        return new HasJoin( $sourceDao, $targetName, $joinDao );
    }
}