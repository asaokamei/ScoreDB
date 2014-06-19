<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use WScore\DbAccess\Sql\Query;
use WScore\DbAccess\Sql\Builder;

/**
 * Class DbAccess
 * @package WScore\DbAccess
 */
class DbAccess
{
    const DEFAULT_KEY = 'default';

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var Builder[]
     */
    protected $builder = [];

    /**
     * @var ExtendedPdo[]
     */
    protected $pdo = [];

    protected $configs = [];

    protected $name = self::DEFAULT_KEY;

    /**
     * @param string|array $name
     * @param array|null   $config
     */
    public function config( $name, $config=null )
    {
        if( is_array($name) ) {
            $config = $name;
            $name   = self::DEFAULT_KEY;
        }
        $this->configs[$name] = $config;
    }

    /**
     * @param $name
     * @return DbAccess
     */
    public function connect( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        $this->getPdo( $name );
        return $this;
    }

    /**
     * @param $table
     * @param null $alias
     * @return DbSql
     */
    public function query( $table, $alias=null )
    {
        $query = Dba::buildQuery();
        $query->setup( $this->getPdo(), $this->getBuilder() );
        $query->table( $table );
        if( $alias ) $query->alias($alias);
        return $query;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return ExtendedPdo
     */
    protected function getPdo( $name=null )
    {
        if( !$name ) $name = $this->name;
        if( $pdo = $this->get( $this->pdo, $name ) ) {
            return $pdo;
        }
        if( !$config = $this->get( $this->configs, $name ) ) {
            throw new \InvalidArgumentException('no such Pdo config: '.$name);
        }
        $dsn       = $this->get( $config, 'dsn' );
        $user      = $this->get( $config, 'user' );
        $pass      = $this->get( $config, 'pass' );
        $option    = $this->get( $config, 'option', [] );
        $attribute = $this->get( $config, 'attribute', [] );
        $pdo = Dba::buildPdo( $dsn, $user, $pass, $option, $attribute );
        $this->pdo[$name] = $pdo;
        $this->builder[$name] = $builder = Dba::buildBuilder();
        $builder->setDbType( $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) );
        return $pdo;
    }

    /**
     * @param string $name
     * @return Builder
     */
    protected function getBuilder( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        return $this->get( $this->builder, $name, null );
    }

    /**
     * @param array  $array
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    protected function get( $array, $name, $default=null )
    {
        return array_key_exists( $name, $array ) ? $array[$name] : $default;
    }
}