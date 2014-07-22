<?php
namespace WScore\ScoreDB;

use Aura\Sql\ExtendedPdo;
use WScore\ScoreSql\DB as SqlDB;

/**
 * Class Dba
 * @package WScore\ScoreDB
 *
 * for DataBase Access.
 *
 */
class Dba extends SqlDB
{
    /**
     * @var DbAccess
     */
    protected static $dba;

    /**
     * @return DbAccess
     */
    public static function getDba()
    {
        if( !static::$dba ) {
            static::$dba = new DbAccess();
        }
        return static::$dba;
    }

    /**
     * 
     */
    public static function restart()
    {
        static::$dba = null;
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function connect($name=null)
    {
        return static::getDba()->connect($name);
    }

    /**
     * @param string $name
     * @return ExtendedPdo
     */
    public static function connectWrite($name=null)
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
        $query->table( $table, $alias )->keyName( $key );
        return $query;
    }
}