<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Factory;
use WScore\SqlBuilder\Sql\Sql;

class Query extends Sql implements IteratorAggregate
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $dbType;

    /**
     * @var Hooks
     */
    protected $hooks;
    
    /**
     * @var string
     */
    protected $connectName = '';

    /**
     * @var bool
     */
    protected $returnLastId = true;

    /**
     * @var ExtendedPdo
     */
    protected $pdo;

    // +----------------------------------------------------------------------+
    //  managing database connection, etc.
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @return $this
     */
    public function connect( $name=null )
    {
        $this->connectName = $name;
        return $this;
    }

    /**
     * @param string $type
     */
    protected function setPdoAndDbType( $type='' )
    {
        $method = 'db'.ucwords($type);
        /** @var ExtendedPdo $pdo */
        $this->pdo = $pdo = Dba::$method( $this->connectName );
        $this->dbType = $pdo->getAttribute( \Pdo::ATTR_DRIVER_NAME );
        $this->builder = Factory::buildBuilder( $this->dbType );
    }

    /**
     * @param string $sqlType
     * @return mixed
     */
    protected function performWrite( $sqlType )
    {
        $this->setPdoAndDbType('write');
        return $this->perform( 'perform', $sqlType );
    }

    /**
     * @param string $method
     * @return mixed
     */
    protected function performRead( $method='fetchAll' )
    {
        $this->setPdoAndDbType();
        return $this->perform( $method, 'select' );
    }

    /**
     * @param string $method
     * @param string $sqlType
     * @return mixed
     */
    protected function perform( $method, $sqlType )
    {
        $sqlType = 'to' . ucwords( $sqlType );
        $sql = $this->builder->$sqlType( $this );
        $bind  = $this->builder->getBind()->getBinding();
        return $this->pdo->$method( $sql, $bind );
    }

    /**
     *
     */
    public function reset()
    {
        $this->where     = null;
        $this->join      = [ ];
        $this->columns   = [ ];
        $this->values    = [ ];
        $this->selFlags  = [ ];
        $this->order     = [ ];
        $this->group     = [ ];
        $this->having    = null;
        $this->limit     = null;
        $this->offset    = 0;
        $this->returning = null;
        $this->forUpdate = false;
    }

    /**
     * for paginate.
     *
     * $perPage is a default number of rows per page, but
     * does not override the $limit if already set.
     *
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function page( $page, $perPage=20 )
    {
        $page = (int) ( ( $page > 0 ) ? $page: 1 );
        if( !$this->limit ) {
            $this->limit( $perPage );
        }
        $this->offset( $perPage * ($page - 1) );
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @param        $id
     * @param string $column
     */
    protected function setId( $id, $column=null )
    {
        if( !$id ) return;
        $column = $column ?: $this->keyName;
        $this->where( $this->$column->eq( $id ) );
    }

    // +----------------------------------------------------------------------+
    //  execute sql.
    // +----------------------------------------------------------------------+
    /**
     * @param null|int $limit
     * @return array
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        $data = $this->performRead( 'fetchAll' );
        $this->reset();
        return $data;
    }

    /**
     * Retrieve an external iterator
     * @return Traversable|PdoStatement
     */
    public function getIterator()
    {
        return $this->performRead( 'perform' );
    }

    /**
     * @return int
     */
    public function count()
    {
        $origColumn    = $this->columns;
        $origOrder     = $this->order;
        $this->order   = [];    // reset columns
        $this->column( false ); // reset columns
        $this->column( $this::raw( 'COUNT(*)'), 'count' );
        $count = $this->performRead( 'fetchValue' );
        $this->columns = $origColumn;
        $this->order   = $origOrder;
        return $count;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $this->setId($id, $column);
        $data = $this->performRead( 'fetchAll' );
        $this->reset();
        return $data;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        if( $data ) $this->value($data);
        $this->performWrite( 'insert' );
        $id = ( $this->returnLastId ) ? $this->lastId() : true;
        $this->reset();
        return $id;
    }

    /**
     * @param string $name
     * @return int
     */
    public function lastId( $name=null )
    {
        if ( $this->dbType == 'pgsql' && !$name ) {
            $name = implode( '_', [ $this->table, $this->keyName, 'seq' ] );
        } else {
            $name = null;
        }
        return $this->pdo->lastInsertId( $name );
    }

    /**
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        $stmt = $this->performWrite( 'update' );
        $this->reset();
        return $stmt;
    }

    /**
     * @param int $id
     * @param string $column
     * @return string
     */
    public function delete( $id=null, $column=null )
    {
        $this->setId($id, $column);
        $stmt = $this->performWrite( 'delete' );
        $this->reset();
        return $stmt;
    }

    // +----------------------------------------------------------------------+
}