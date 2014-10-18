<?php
namespace WScore\ScoreDB\Relation;

/**
 * Created by PhpStorm.
 * User: Asao Kamei
 * Date: 2014/10/18
 * Time: 10:32
 */
interface RelationInterface
{
    public function get();
    
    public function link( $target );
}