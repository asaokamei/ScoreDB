<?php
namespace WScore\ScoreDB;

use Aura\Sql\ExtendedPdo;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\ScoreSql\Query as SqlQuery;

class Query extends SqlQuery implements IteratorAggregate, QueryInterface
{
    /**
     * @var string
     */
    protected $connectName = '';

    /**
     * @var bool
     */
    protected $returnLastId = false;

    /**
     * class name used as fetched object.
     *
     * @var null|string
     */
    protected $fetch_class = null;

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
     * @return ExtendedPdo
     */
    protected function setPdoAndDbType( $type='' )
    {
        $method = 'connect'.ucwords($type);
        /** @var ExtendedPdo $pdo */
        if( $pdo = DB::$method( $this->connectName ) ) {
            $this->dbType = $pdo->getAttribute( \Pdo::ATTR_DRIVER_NAME );
        }
        return $pdo;
    }

    /**
     * @return mixed
     */
    protected function performWrite()
    {
        $pdo = $this->setPdoAndDbType('write');
        return $this->perform( $pdo );
    }

    /**
     * @param string $method
     * @return PdoStatement|array
     */
    protected function performRead( $method )
    {
        $pdo = $this->setPdoAndDbType();
        $stm = $this->perform( $pdo );
        if( $stm instanceof PDOStatement ) {
            if( $method == 'fetchValue' ) {
                return $stm->fetchColumn(0);
            }
            if( $method == 'fetchAll' ) {
                $this->setFetchMode( $stm );
                return $stm->fetchAll();
            }
        }
        return $stm;
    }

    /**
     * overwrite this method to set fetch mode.
     *
     * @param PdoStatement $stm
     * @return bool
     */
    protected function setFetchMode( $stm )
    {
        if( $this->fetch_class ) {
            return $this->setFetchClass($stm);
        }
        return $stm->setFetchMode( \PDO::FETCH_ASSOC );
    }

    /**
     * overwrite this method to set fetch mode.
     *
     * @param PdoStatement $stm
     * @return bool
     */
    protected function setFetchClass( $stm )
    {
        return $stm->setFetchMode( \PDO::FETCH_CLASS, $this->fetch_class, [] );
    }

    /**
     * @param ExtendedPdo $pdo
     * @return PDOStatement|array
     */
    protected function perform( $pdo )
    {
        $sql = (string) $this;
        $bind  = $this->getBind();
        return $pdo->perform( $sql, $bind );
    }

    // +----------------------------------------------------------------------+
    //  execute sql.
    // +----------------------------------------------------------------------+
    /**
     * @param        $id
     * @param string $column
     * @return $this|void
     */
    public function key( $id, $column=null )
    {
        if( !$id ) return $this;
        $column = $column ?: $this->keyName;
        $this->where( $this->$column->eq( $id ) );
        return $this;
    }

    /**
     * @param null|int $limit
     * @return array|mixed
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
        $this->reset();
        return $stmt;
    }

    /**
     * @param int $id
     * @param string $column
     * @return PdoStatement
     */
    public function delete( $id=null, $column=null )
    {
        $this->key($id, $column);
        $this->toDelete();
        $stmt = $this->performWrite();
        $this->reset();
        return $stmt;
    }

    // +----------------------------------------------------------------------+
}