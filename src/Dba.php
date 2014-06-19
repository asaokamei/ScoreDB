<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use WScore\DbAccess\Sql\Bind;
use WScore\DbAccess\Sql\Builder;
use WScore\DbAccess\Sql\Quote;
use WScore\DbAccess\Sql\Where;

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

    /**
     * @return DbSql
     */
    public static function buildQuery()
    {
        return new DbSql( new Bind() );
    }

    /**
     * @return Builder
     */
    public static function buildBuilder()
    {
        return new Builder( new Quote() );
    }

    /**
     * @param $dsn
     * @param $user
     * @param $pass
     * @param $option
     * @param $attribute
     * @return ExtendedPdo
     */
    public static function buildPdo( $dsn, $user, $pass, $option, $attribute )
    {
        return new ExtendedPdo( $dsn, $user, $pass, $option, $attribute );
    }

    /**
     * @return Where
     */
    public static function buildWhere()
    {
        return new Where();
    }

    /**
     * @param $column
     * @return Where
     */
    public static function where( $column )
    {
        $where = static::buildWhere();
        $where->col( $column );
        return $where;
    }

    /**
     * @return DbAccess
     */
    public static function db()
    {
        if( static::$dba ) return static::$dba;
        static::$dba = new DbAccess();
        return static::$dba;
    }

    /**
     * @param string|array $name
     * @param array|null   $config
     */
    public static function config( $name, $config=null )
    {
        $dba = static::db();
        $dba->config( $name, $config );
    }

    /**
     * @param string      $table
     * @param string|null $alias
     * @return \PdoStatement|DbSql
     */
    public static function query( $table, $alias=null )
    {
        $dba = static::db();
        return $dba->query( $table, $alias );
    }
}