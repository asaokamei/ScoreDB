<?php
namespace WScore\ScoreDB;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;

/**
 * Class DbAccess
 * @package WScore\ScoreDB
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
     * @var Profiler
     */
    protected $profiler;
    
    /**
     * @param string|array $name
     * @param array|callable|null   $config
     */
    public function config( $name, $config=null )
    {
        if( is_array($name) || is_callable( $name ) ) {
            $config = $name;
            $name   = self::DEFAULT_KEY;
        }
        if( !isset( $this->configs[$name] )) {
            $this->configs[$name] = $this->buildConnectionLocator();
        }
        if( is_callable( $config ) ) {
            $callPdo = $config;
        } else {
            $callPdo = $this->buildPdo( $config );
        }
        if( $for = $this->get( $config, 'for' ) ) {

            $for = ucwords( $for );
            $for = 'set'.$for;
            $this->configs[$name]->$for(
                'db'.$this->counter++,
                $callPdo
            );
        } else {
            $this->configs[$name]->setDefault( $callPdo );
        }
    }

    /**
     * 
     */
    public function useProfile()
    {
        $this->profiler = $this->buildProfiler();
        $this->profiler->setActive(true);
    }

    /**
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * @param $name
     * @return ExtendedPdo
     */
    public function connect( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        if( !isset( $this->configs[ $name ] ) ) return null;
        $locator = $this->configs[ $name ];
        $pdo = $locator->getRead();
        if( $this->profiler ) $pdo->setProfiler( $this->profiler );
        return $pdo;
    }

    /**
     * @param $name
     * @return ExtendedPdo
     */
    public function connectWrite( $name=null )
    {
        if( !$name ) $name = self::DEFAULT_KEY;
        if( !isset( $this->configs[ $name ] ) ) return null;
        $locator = $this->configs[ $name ];
        $pdo = $locator->getWrite();
        if( $this->profiler ) $pdo->setProfiler( $this->profiler );
        return $pdo;
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

    /**
     * @return ConnectionLocator
     */
    protected function buildConnectionLocator()
    {
        return new ConnectionLocator();
    }

    /**
     * @return Profiler
     */
    protected function buildProfiler()
    {
        return new Profiler();
    }
}