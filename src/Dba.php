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
    static $dba;

    protected static function getDba()
    {
        if( !static::$dba ) {
            static::$dba = new DbAccess();
        }
        return static::$dba;
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function db($name=null)
    {
        static::getDba()->connect($name);
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function dbWrite($name=null)
    {
        static::getDba()->connectWrite($name);
    }

    /**
     * @param string|array $name
     * @param array|null   $config
     */
    public static function config( $name, $config=null )
    {
        static::getDba()->config( $name, $config );
    }

    /**
     * @param string $table
     * @param string $key
     * @param string $alias
     * @return DbSql
     */
    public static function query( $table, $key=null, $alias=null )
    {
        $query = new DbSql();
        $query->table( $table, $alias )->setKeyName( $key );
        return $query;
    }
}