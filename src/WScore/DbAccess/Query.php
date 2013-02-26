<?php
namespace WScore\DbAccess;

class Query implements QueryInterface
{
    // PdObject for executing and fetching result from DB.

    /** @var \WScore\DbAccess\DbAccess   Access Database     */
    protected $dbAccess  = null;

    /** @var \PdoStatement               PDO statement obj   */
    protected $pdoStmt = null;

    // variables to build SQL statement.

    /**
     * @Inject
     * @var \WScore\DbAccess\QueryObject
     */
    public $queryObject = null;

    /** @var string           name of database table         */
    protected $table;
    
    /** @var string           name of id (primary key)       */
    protected $id_name = 'id';

    /** @var array            stores data types of columns   */
    public $_col_data_types = array();

    /** @var null             prepare/quote? null default    */
    public $_prepQuoteUseType = null;

    /** @var string           default prepare/quote          */
    public static $pqDefault = 'prepare';

    /** @var QueryObject   SqlObject class    */
    public $_queryObjectClass = '\WScore\DbAccess\QueryObject';
    // +----------------------------------------------------------------------+
    //  Construction and Managing Dba Object.
    // +----------------------------------------------------------------------+
    /**
     * @Inject
     * @param \WScore\DbAccess\DbAccess $pdoObj
     */
    public function __construct( $pdoObj=null ) {
        $this->dbAccess = $pdoObj;
        $this->clear();
        if( is_string( $this->queryObject ) ) $this->queryObject = new $this->queryObject;
    }

    /**
     * clear returns brand new Sql object, instead of using
     * the same object and reset all variables.
     *
     * @return Query
     */
    public function clear() {
        $class = $this->_queryObjectClass;
        $this->queryObject = new $class( $this->dbAccess );
        $this->queryObject->prepQuoteUseType = ( $this->_prepQuoteUseType ) ?: static::$pqDefault;
        $this->queryObject->col_data_types = $this->_col_data_types;
        return $this;
    }

    /**
     * @param mixed $config
     */
    public function connect( $config=null ) {
        $this->dbAccess->connect( $config );
    }

    // +----------------------------------------------------------------------+
    //  executing with PdObject
    // +----------------------------------------------------------------------+
    /**
     * @param       $mode
     * @param null  $class
     * @param array $args
     * @return Query
     */
    public function setFetchMode( $mode, $class=null, $args=array() ) {
        $this->dbAccess->setFetchMode( $mode, $class, $args );
        return $this;
    }

    /**
     * @param null|string $name
     * @return string
     */
    public function lastId( $name=null ) {
        if( !$name ) $name = $this->table . '_id_seq';
        return $this->dbAccess->lastId( $name );
    }

    /**
     * @param string $table
     * @return Query
     */
    public function lockTable( $table=null ) {
        $table = ( $table )?: $this->table;
        $this->dbAccess->lockTable( $table );
        return $this;
    }

    /**
     * get driver name, such as mysql, sqlite, pgsql.
     * @return string
     */
    public function getDriverName() {
        return $this->dbAccess->getDriverName();
    }

    /**
     * executes SQL statement.
     *
     * @throws \RuntimeException
     * @return Query
     */
    public function exec() {
        $this->pdoStmt = $this->dbAccess->query( $this->queryObject );
        return $this;
    }

    /**
     * executes SQL statement. mostly for backward compatibility.
     *
     * @param null  $sql
     * @param array $prepared
     * @param array $dataType
     * @throws \RuntimeException
     * @return Query
     */
    public function execSQL( $sql=null, $prepared=array(), $dataType=array() ) {
        if( !$this->dbAccess ) throw new \RuntimeException( 'Pdo Object not set.' );
        $this->pdoStmt = $this->dbAccess->execSql( $sql, $prepared, $dataType );
        return $this;
    }
    public function execPrepare( $sql ) {
        $this->dbAccess->execPrepare( $sql );
        return $this;
    }
    public function execExecute( $prepared, $dataType=array() ) {
        $this->dbAccess->execExecute( $prepared, $dataType );
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Getting result from PdoStatement.
    // +----------------------------------------------------------------------+
    /**
     * @return int|null
     */
    public function numRows() {
        if( is_numeric( $this->pdoStmt ) ) {
            return $this->pdoStmt;
        }
        return $this->pdoStmt->rowCount();
    }

    /**
     * @return int|null
     */
    public function fetchNumRow() {
        return $this->numRows();
    }

    /**
     * @return array
     */
    public function fetchAll() {
        if( is_object( $this->pdoStmt ) ) {
            return $this->pdoStmt->fetchAll();
        }
        return array();
    }

    /**
     * @param int $row
     * @throws \RuntimeException
     * @return array|mixed
     */
    public function fetchRow( $row=0 ) {
        if( is_object( $this->pdoStmt ) ) {
            if( $row > 0 ) {
                $driver = $this->getDriverName();
                if( $driver == 'mysql' || $driver == 'sqlite' ) {
                    throw new \RuntimeException( "Cannot fetch with offset for ".$driver );
                }
            }
            return $this->pdoStmt->fetch( null, \PDO::FETCH_ORI_ABS, $row );
        }
        return array();
    }
    // +----------------------------------------------------------------------+
    //  Quoting and Preparing Values for Prepared Statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $val
     * @return mixed
     */
    public function p( $val ) {
        $this->queryObject->prepare( $val );
        return $val;
    }

    /**
     * @param string $val
     * @return string
     */
    public function q( $val ) {
        $this->queryObject->quote( $val );
        return $val;
    }
    // +----------------------------------------------------------------------+
    //  Setting string, array, and data to build SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @param string $id_name
     * @return \WScore\DbAccess\Query
     */
    public function table( $table, $id_name='id' ) {
        $this->clear();
        $this->table   = $this->queryObject->table = $table;
        $this->id_name = $this->queryObject->id_name = $id_name;
        return $this;
    }

    /**
     * @param string|array $column
     * @return Query
     */
    public function column( $column ) {
        $this->queryObject->columns = $column;
        return $this;
    }

    /**
     * set values for INSERT or UPDATE.
     * @param array $values
     * @return Query
     */
    public function values( $values ) {
        $this->queryObject->values = $values;
        return $this;
    }

    /**
     * set SQL functions for INSERT or UPDATE. The functions are not 'prepared'.
     * TODO: find better name than functions??? how about rawValue?
     * @param $func
     * @return Query
     */
    public function functions( $func ) {
        $this->queryObject->functions = $func;
        return $this;
    }
    public function order( $order ) {
        $this->queryObject->order = $order;
        return $this;
    }
    public function group( $group ) {
        $this->queryObject->group = $group;
        return $this;
    }
    public function misc( $misc ) {
        $this->queryObject->misc = $misc;
        return $this;
    }
    public function limit( $limit ) {
        $this->queryObject->limit  = ( $limit  ) ? $limit : false;
        return $this;
    }
    public function offset( $offset ) {
        $this->queryObject->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }

    /**
     * creates SELECT DISTINCT statement.
     * @return Query
     */
    public function distinct(){
        $this->queryObject->distinct = true;
        return $this;
    }

    /**
     * creates SELECT for UPDATE statement.
     * @return Query
     */
    public function forUpdate() {
        $this->queryObject->forUpdate = true;
        return $this;
    }

    /**
     * Building JOIN clause...
     *
     * @param $table
     * @param $join
     * @param null $by
     * @param null $columns
     * @return Query
     */
    public function join( $table, $join, $by=null, $columns=null ) {
        $this->queryObject->join[] = compact( 'table', 'join', 'by', 'columns' );
        return $this;
    }
    public function joinUsing( $table, $columns ) {
        return $this->join( $table, 'JOIN', 'USING', $columns );
    }
    public function joinLeftUsing( $table, $columns ) {
        return $this->join( $table, 'LEFT JOIN', 'USING', $columns );
    }
    public function joinOn( $table, $columns ) {
        return $this->join( $table, 'JOIN', 'ON', $columns );
    }
    public function joinLeftOn( $table, $columns ) {
        return $this->join( $table, 'LEFT JOIN', 'ON', $columns );
    }
    // +----------------------------------------------------------------------+
    //  Building WHERE clause.
    // +----------------------------------------------------------------------+
    /**
     * set where statement with values properly prepared/quoted. 
     * 
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string|bool   $type
     * @return Query
     */
    public function where( $col, $val, $rel='=', $type=null ) {
        $this->queryObject->where( $col, $val, $rel, $type );
        return $this;
    }

    /**
     * set where statement as is. 
     * 
     * @param        $col
     * @param        $val
     * @param string $rel
     * @return Query
     */
    public function whereRaw( $col, $val, $rel='=' ) {
        $this->queryObject->whereRaw( $col, $val, $rel );
        return $this;
    }

    /**
     * sets OR operation for the last where statement data. 
     * 
     * @return Query
     */
    public function or_() {
        $this->queryObject->modRaw( array( 'op' => 'OR' ) );
        return $this;
    }
    public function __get( $name ) {
        return $this->col( $name );
    }
    public function col( $col ) {
        $this->queryObject->col( $col );
        return $this;
    }
    public function mod( $val, $rel, $type=null ) {
        $mod = array( 'val' => $val, 'rel' => $rel );
        $this->queryObject->mod( $mod, $type );
        return $this;
    }
    public function modRaw( $val, $rel ) {
        $mod = array( 'val' => $val, 'rel' => $rel );
        $this->queryObject->modRaw( $mod );
        return $this;
    }
    public function id( $val, $type=null ) {
        if( is_array( $val ) ) return $this->col( $this->id_name )->in( $val, $type );
        return $this->col( $this->id_name )->mod( $val, '=', $type );
    }
    public function eq( $val, $type=null ) {
        if( is_array( $val ) ) return $this->in( $val, $type );
        return $this->mod( $val, '=', $type );
    }
    public function ne( $val, $type=null ) {
        return $this->mod( $val, '!=', $type );
    }
    public function lt( $val, $type=null ) {
        return $this->mod( $val, '<', $type );
    }
    public function le( $val, $type=null ) {
        return $this->mod( $val, '<=', $type );
    }
    public function gt( $val, $type=null ) {
        return $this->mod( $val, '>', $type );
    }
    public function ge( $val, $type=null ) {
        return $this->mod( $val, '>=', $type );
    }
    public function in( $val, $type=null ) {
        return $this->mod( $val, 'IN', $type );
    }
    public function notIn( $val, $type=null ) {
        return $this->mod( $val, 'NOT IN', $type );
    }
    public function between( $val, $type=null ) {
        return $this->mod( $val, 'BETWEEN', $type );
    }
    public function isNull() {
        return $this->modRaw( null, 'IS NULL' );
    }
    public function notNull() {
        return $this->modRaw( null, 'IS NOT NULL' );
    }
    public function like( $val, $type=null ) {
        return $this->mod( $val, 'LIKE', $type );
    }
    public function contain( $val, $type=null ) {
        return $this->mod( "%{$val}%", 'LIKE', $type );
    }
    public function startWith( $val, $type=null ) {
        return $this->mod( $val.'%', 'LIKE', $type );
    }
    public function endWith( $val, $type=null ) {
        return $this->mod( '%'.$val, 'LIKE', $type );
    }
    /**
     * sets where. replaces where data as is.
     * @param string $where
     * @return Query
     */
    public function setWhere( $where ) {
        $this->queryObject->where = $where;
        return $this;
    }

    /**
     * @param string $where
     * @return Query
     */
    public function addWhere( $where ) {
        return $this->whereRaw( $where, '', '' );
    }

    /**
     * @return Query
     */
    public function clearWhere() {
        $this->queryObject->where = array();
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  constructing and executing SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param array $values
     * @return Query
     */
    public function update( $values ) {
        return $this->values( $values )->makeUpdate()->exec();
    }

    /**
     * @param array $values
     * @return Query
     */
    public function insert( $values ) {
        return $this->values( $values )->makeInsert()->exec();
    }

    /**
     * @param array|null $column
     * @return array
     */
    public function select( $column=null ) {
        if( $column ) $this->column( $column );
        return $this->makeSelect()->exec()->fetchAll();
    }

    /**
     * @return string
     */
    public function count() {
        return $this->makeCount()->exec()->pdoStmt->fetchColumn(0);
    }

    /**
     * makes SQL statement. $types are:
     * INSERT, UPDATE, DELETE, COUNT, SELECT.
     * @param $type
     * @return Query
     */
    public function makeSQL( $type )
    {
        $this->queryObject->query( $type );
        return $this;
    }
    public function makeSelect() {
        return $this->makeSQL( 'Select' );
    }
    public function makeCount() {
        return $this->makeSQL( 'Count' );
    }
    public function makeDelete() {
        return $this->makeSQL( 'Delete' );
    }
    public function makeInsert() {
        return $this->makeSQL( 'Insert' );
    }
    public function makeUpdate() {
        return $this->makeSQL( 'Update' );
    }
    // +----------------------------------------------------------------------+
}