<?php
namespace WScore\DbAccess;

class xxxDbAccess implements \Serializable
{
    /**
     * @Inject
     * @var \WScore\DbAccess\DbConnect
     */
    public $dbConnect;

    /**
     * @Inject
     * @var \WScore\DbAccess\SqlBuilder
     */
    public $sqlBuilder;

    /**
     * @Inject
     * @var \WScore\DbAccess\Profile
     */
    public $log;

    /** @var \Pdo                        PDO object          */
    protected $pdoObj  = null;

    /** @var \PdoStatement */
    protected $pdoStmt;

    /** @var string|array                dsn used for db connection  */
    private $connConfig = null;

    /** @var array                       for serialize.  */
    private $toSerialize = array( 'dbConnect', 'sqlBuilder', 'log', 'connConfig', );
    // +----------------------------------------------------------------------+
    //  Constructor and Managing Objects.
    // +----------------------------------------------------------------------+
    /**
     * inject Pdo and Sql object.
     *
     * @Inject
     * @param \Pdo $pdoObj
     */
    public function __construct( $pdoObj=null )
    {
        $this->connect( $pdoObj );
    }

    /**
     * set database connection. can accept various input...
     *
     * @param \Pdo|DbConnect|string|array $pdo
     * @return DbAccess
     */
    public function connect( $pdo=null )
    {
        if( !isset( $pdo ) ) { // do nothing
        }
        elseif( $pdo instanceof \PDO ) {
            $this->pdoObj = $pdo;
        }
        elseif( $pdo instanceof \WScore\DbAccess\DbConnect ) {
            $this->dbConnect = $pdo;
        }
        elseif( is_string( $pdo ) || is_array( $pdo ) ) {
            $this->connConfig = $pdo;
        }
        if( !isset( $this->pdoObj ) && isset( $this->dbConnect ) ) {
            $this->pdoObj = $this->dbConnect->connect( $this->connConfig );
        }
        return $this;
    }

    /**
     * @return string|array
     */
    public function getConnConfig() {
        return $this->connConfig;
    }

    /**
     * @return null|\Pdo
     */
    public function pdo() {
        return $this->pdoObj;
    }
    // +----------------------------------------------------------------------+
    //  Executing SQL. all methods returns Dba object.
    // +----------------------------------------------------------------------+
    /**
     * @param QueryObject $query
     * @return \PdoStatement
     */
    public function query( $query )
    {
        $sql = $this->sqlBuilder->build( $query );
        return $this->execSql( $sql, $query->prepared_values, $query->prepared_types );
    }
    /**
     * executes an SQL statement using prepare statement.
     *
     * @param string $sql
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders.
     * @throws \RuntimeException
     * @return \PdoStatement
     */
    public function execSql( $sql, $prepared=array(), $dataTypes=array() )
    {
        if( !$sql ) throw new \RuntimeException( "missing Sql statement." );
        $this->execPrepare( $sql );
        $this->execExecute( $prepared, $dataTypes );
        return $this->pdoStmt;
    }

    /**
     * @param string $sql
     * @throws \RuntimeException
     * @return \PdoStatement
     */
    public function execPrepare( $sql ) 
    {
        if( is_object( $this->pdoStmt ) ) {
            $this->pdoStmt->closeCursor();
        }
        $this->pdoStmt = $this->pdoObj->prepare( $sql, array(
        //    \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        return $this->pdoStmt;
    }

    /**
     * @param array $prepared     place holders for prepared statement.
     * @param array $dataTypes    data types for the place holders.
     * @throws \PDOException
     * @return \PdoStatement
     */
    public function execExecute( $prepared, $dataTypes=array() ) 
    {
        if( !empty( $dataTypes ) ) {
            
            // bind value for each holder/value.
            foreach( $prepared as $holder => $value ) {
                if( array_key_exists( $holder, $dataTypes ) ) {
                    // data types for the holder specified. 
                    $this->pdoStmt->bindValue( $holder, $value, $dataTypes[ $holder ] );
                }
                else {
                    $this->pdoStmt->bindValue( $holder, $value );
                }
            }
            $prepared = null;
        }
        $start = microtime( true );
        try {
            $this->pdoStmt->execute( $prepared );
        } catch( \PDOException $e ) {
            $msg = $e->getMessage();
            $sql = $this->pdoStmt->queryString;
            throw new \PDOException( "$sql\n$msg" );
        }
        if( $this->log ) {
            $this->log->log( $this->pdoStmt->queryString, microtime( true ) - $start, $prepared, $dataTypes );
        }
        return $this->pdoStmt;
    }
    // +----------------------------------------------------------------------+
    //  fetching result from the database.
    // +----------------------------------------------------------------------+
    /**
     * @param null|string $name
     * @return string
     */
    public function lastId( $name=null ) {
        return $this->pdoObj->lastInsertId( $name );
    }

    // +----------------------------------------------------------------------+
    //  Miscellaneous methods.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return DbAccess
     */
    public function lockTable( $table ) {
        $lock = "LOCK TABLE {$table}";
        $driver = $this->getDriverName();
        if( $driver == 'pgsql' ) {
            $lock .= ' IN ACCESS EXCLUSIVE MODE';
        }
        $this->execSql( $lock );
        return $this;
    }

    /**
     * get driver name, such as mysql, sqlite, pgsql.
     * @return string
     */
    public function getDriverName() {
        return $this->pdoObj->getAttribute( \PDO::ATTR_DRIVER_NAME );
    }

    /**
     * Quote string using Pdo's quote (or just add-slashes if Pdo not present).
     *
     * @param string|array $val
     * @return string|array
     */
    public function quote( $val )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $v = $this->quote( $v );
            }
        }
        elseif( isset( $this->pdoObj ) ) {
            $val = $this->pdoObj->quote( $val );
        }
        else {
            $val = "'" . addslashes( $val ) . "'";
        }
        return $val;
    }
    // +----------------------------------------------------------------------+
    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $data = array();
        foreach( $this->toSerialize as $var ) { $data[ $var ] = $this->$var; }
        return serialize( $data );
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized   The string representation of the object.
     * @return mixed the original value unserialized.
     */
    public function unserialize( $serialized )
    {
        $info = unserialize( $serialized );
        foreach( $this->toSerialize as $var ) { $this->$var = $info[ $var ]; }
        $this->connect();
    }
}