<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\ScoreSql\Factory;

class Query extends \WScore\ScoreSql\Query implements IteratorAggregate, QueryInterface
{
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
     * @return mixed
     */
    protected function performWrite()
    {
        $pdo = $this->setPdoAndDbType('write');
        return $this->perform( $pdo, 'perform' );
    }

    /**
     * @param string $method
     * @return mixed
     */
    protected function performRead( $method='fetchAll' )
    {
        $pdo = $this->setPdoAndDbType();
        return $this->perform( $pdo, $method );
    }

    /**
     * @param ExtendedPdo $pdo
     * @param string $method
     * @return mixed
     */
    protected function perform( $pdo, $method )
    {
        $sql = (string) $this;
        $bind  = $this->builder->getBind()->getBinding();
        return $pdo->$method( $sql, $bind );
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
        $this->toSelect();
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
        $this->toSelect();
        return $this->performRead( 'perform' );
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->toCount();
        $count = $this->performRead( 'fetchValue' );
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
        $this->toInsert();
        $this->performWrite();
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
        $this->toUpdate();
        $stmt = $this->performWrite();
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
        $this->toDelete();
        $stmt = $this->performWrite();
        $this->reset();
        return $stmt;
    }

    // +----------------------------------------------------------------------+
}