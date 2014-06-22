<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use WScore\SqlBuilder\Factory;
use WScore\SqlBuilder\Sql\Where;

/**
 * Class Dba
 * @package WScore\DbAccess
 *
 * for DataBase Access.
 *
 */
class Dba
{
    /**
     * @var DbAccess
     */
    protected static $dba;

    /**
     * @return DbAccess
     */
    protected static function getDba()
    {
        if( !static::$dba ) {
            static::$dba = new DbAccess();
        }
        return static::$dba;
    }

    /**
     * 
     */
    public static function reset()
    {
        static::$dba = null;
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function db($name=null)
    {
        return static::getDba()->connect($name);
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function dbWrite($name=null)
    {
        return static::getDba()->connectWrite($name);
    }

    /**
     * @param string|array|callable $name
     * @param array|callable|null   $config
     */
    public static function config( $name, $config=null )
    {
        static::getDba()->config( $name, $config );
    }

    /**
     * @param string $table
     * @param string $key
     * @param string $alias
     * @return Query
     */
    public static function query( $table, $key=null, $alias=null )
    {
        $query = new Query();
        $query->table( $table, $alias )->setKeyName( $key );
        return $query;
    }
}