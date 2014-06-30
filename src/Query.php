<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\ScoreSql\Builder\Builder;
use WScore\ScoreSql\Factory;
use WScore\ScoreSql\Sql\Sql;

class Query extends Sql implements IteratorAggregate, QueryInterface
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
     * @var string
     */
    protected $connectName = '';

    /**
     * @var bool
     */
    protected $returnLastId = true;

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
     * @return \Aura\Sql\ExtendedPdo
     */
    protected function setPdoAndDbType( $type='' )
    {
        $method = 'db'.ucwords($type);
        /** @var ExtendedPdo $pdo */
        $pdo = Dba::$method( $this->connectName );
        $this->dbType = $pdo->getAttribute( \Pdo::ATTR_DRIVER_NAME );
        $this->builder = Factory::buildBuilder( $this->dbType );
        return $pdo;
    }

    /**
     * @param string $sqlType
     * @return mixed
     */
    protected function performWrite( $sqlType )
    {
        $pdo = $this->setPdoAndDbType('write');
        return $this->perform( $pdo, 'perform', $sqlType );
    }

    /**
     * @param string $method
     * @param string $sqlType
     * @return mixed
     */
    protected function performRead( $method='fetchAll', $sqlType='select' )
    {
        $pdo = $this->setPdoAndDbType();
        return $this->perform( $pdo, $method, $sqlType );
    }

    /**
     * @param ExtendedPdo $pdo
     * @param string $method
     * @param string $sqlType
     * @return mixed
     */
    protected function perform( $pdo, $method, $sqlType )
    {
        $sqlType = 'to' . ucwords( $sqlType );
        $sql = $this->builder->$sqlType( $this );
        $bind  = $this->builder->getBind()->getBinding();
        return $pdo->$method( $sql, $bind );
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
        $count = $this->performRead( 'fetchValue', 'count' );
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
        $pdo = $this->setPdoAndDbType('write');
        return $pdo->lastInsertId( $name );
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