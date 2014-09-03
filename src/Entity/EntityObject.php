<?php
namespace WScore\ScoreDB\Entity;

use WScore\ScoreDB\Dao;

/**
 * Class EntityObject
 * @package WScore\ScoreDB\Entity
 *
 * A generic entity object class.
 *
 */
class EntityObject extends EntityAbstract implements \ArrayAccess
{
    use ArrayAccessTrait;
}