<?php
namespace WScore\DbAccess;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use WScore\SqlBuilder\Factory;
use WScore\SqlBuilder\Query;

/**
 * Class DbAccess
 * @package WScore\DbAccess
 */
class DbAccess
{
    const DEFAULT_KEY = 'default';

    /**
     * @var ConnectionLocator[]
     */
    protected $configs = [];

    /**
     * @var string
     */
    protected $name = self::DEFAULT_KEY;

    /**
     * @var int
     */
    protected $counter = 1;

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
        if( !isset( $this->configs[$name] )) {
            $this->configs[$name] = new ConnectionLocator();
        }
        if( strtolower( $this->get( $config, 'for' ) == 'write' ) ) {
            $this->configs[$name]->setWrite(
                'db'.$this->counter++,
                $this->buildPdo( $config )
            );
        } else {
            $this->configs[$name]->setDefault( $this->buildPdo( $config ) );
        }
    }

    /**
     * @param $name
     * @return DbAccess
     */
    public function connect( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        $locator = $this->configs[ $name ];
        $locator->getRead( $name );
        return $this;
    }

    /**
     * @param $name
     * @return DbAccess
     */
    public function connectWrite( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        $locator = $this->configs[ $name ];
        $locator->getWrite( $name );
        return $this;
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

    /**
     * @param array $config
     * @return callable
     */
    protected function buildPdo( $config )
    {
        $dsn       = $this->get( $config, 'dsn' );
        $user      = $this->get( $config, 'user' );
        $pass      = $this->get( $config, 'pass' );
        $option    = $this->get( $config, 'option', [] );
        $attribute = $this->get( $config, 'attribute', [] );
        return function() use( $dsn, $user, $pass, $option, $attribute ) {
            return new ExtendedPdo(
                $dsn, $user, $pass, $option, $attribute );
        };
    }

}