<?php
namespace WScore\DbAccess;

/*

Sample usage with Core

Core::set( 'db.config', 'connection string' );
Core::setPdo( 'db.config' ); // will create 'Pdo' using db.config
Core::setPdo( 'db.config', 'Pdo2' ); // will create Pdo2 using db.config.

config = array(
    'dsn'  => 'db:dbname=dbname; host=host; port=port; charset=utf8',
    'username' => 'user name',
    'password' => 'password',
    'execute'  => 'sql to execute',
    'attributes' => [ attr => val, ... ]
);

OR

config = 'db=database dbname=dbname host=host port=port username=user password=pswd';

*/


class DbConnect
{
    /** @var array   default attributes for PDO driver  */
    public $defaultAttr = array(
        \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    );

    /** @var string    Pdo class name to generate */
    public $pdoClass = '\PDO';

    /** @var string    charset to use. default is utf-8. */
    public $charset = 'utf8';

    /** @var string */
    public $config;

    // +----------------------------------------------------------------------+
    /**
     * @param string $config
     */
    public function __construct( $config=null )
    {
        $this->config = $config;
    }

    /**
     * returns Pdo connection which is pooled by config name.
     *
     * @param $config
     * @throws \RuntimeException
     * @return \Pdo
     */
    public function connect( $config=null )
    {
        if( !isset( $config ) ) $config = $this->config;
        if( !isset( $config ) ) return null;
        if( is_string( $config ) ) {
            $config = $this->prepare( $config );
        }
        if( !isset( $config[ 'dsn' ] ) || empty( $config[ 'dsn' ] ) ) {
            throw new \RuntimeException( 'dsn not set for Pdo.' );
        }
        if( !isset( $config[ 'attributes' ] ) ) {
            $config[ 'attributes' ] = $this->defaultAttr;
        }
        if( !isset( $config[ 'username' ] ) ) {
            $config[ 'username' ] = null;
        }
        if( !isset( $config[ 'password' ] ) ) {
            $config[ 'password' ] = null;
        }
        $class = $this->pdoClass;
        /** @var $pdo \Pdo */
        $pdo = new $class( $config[ 'dsn' ], $config[ 'username' ], $config[ 'password' ], $config[ 'attributes' ] );
        if( isset( $config[ 'exec' ] ) ) {
            $pdo->exec( $config[ 'exec' ] );
        }
        $this->config = $config;
        return $pdo;
    }

    // +----------------------------------------------------------------------+
    /**
     * parses db connection string to config array.
     * 
     * @param string $db_con
     * @return array
     */
    public function prepare( $db_con )
    {
        $conn_str = array( 'dsn', 'username', 'password' );
        $config = array();
        foreach( $conn_str as $parameter ) 
        {
            $pattern = "/{$parameter}=(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) ) {
                $config[ "{$parameter}" ] = $matches[1];
            }
        }
        return $config;
    }
    // +----------------------------------------------------------------------+
}