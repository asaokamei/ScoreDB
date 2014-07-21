<?php
namespace WScore\ScoreDB;

use Aura\Sql\ExtendedPdo;
use InvalidArgumentException;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\ScoreDB\Hook\Hooks;
use WScore\ScoreSql\Sql;

class Query extends Sql implements IteratorAggregate, QueryInterface
{
    /**
     * @var string
     */
    protected $connectName = '';

    /**
     * @var bool
     */
    protected $returnLastId = true;

    /**
     * @var Hooks
     */
    protected $hooks;

    /**
     * set true to use the value set in $useFilteredData.
     *
     * @var bool
     */
    protected $useFilteredFlag = false;

    protected $filteredData = null;

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
     * @param $method
     * @param $args
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call( $method, $args )
    {
        if( $this->hooks->scope( $method, $this, $args ) ) {
            return $this;
        }
        throw new \BadMethodCallException( 'no such method: '.$method );
    }

    /**
     * @param string $type
     * @return \Aura\Sql\ExtendedPdo
     */
    protected function setPdoAndDbType( $type='' )
    {
        $method = 'db'.ucwords($type);
        /** @var ExtendedPdo $pdo */
        if( $pdo = Dba::$method( $this->connectName ) ) {
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
        if( $this->useFilteredFlag ) {
            $this->useFilteredFlag = false;
            return $this->filteredData;
        }
        $sql = (string) $this;
        $bind  = $this->getBind();
        return $pdo->$method( $sql, $bind );
    }

    // +----------------------------------------------------------------------+
    //  hooks
    // +----------------------------------------------------------------------+
    /**
     * dumb hooks for various events. $data are all string.
     * available events are:
     * - constructing, constructed, newQuery,
     * - selecting, selected, inserting, inserted,
     * - updating, updated, deleting, deleted,
     *
     * @param string $event
     * @param mixed  $data
     * @return mixed|null
     */
    protected function hook( $event, $data=null )
    {
        if( $this->hooks ) {
            $data = $this->hooks->hook( $event, $data );
            if( $this->hooks->usesFilterData() ) {
                $this->filteredData = $data;
                $this->useFilteredFlag = true;
            }
        }
        return $data;
    }

    /**
     * @param Hooks $hook
     */
    public function setHook( $hook )
    {
        $this->hooks = $hook;
    }
    
    // +----------------------------------------------------------------------+
    //  execute sql.
    // +----------------------------------------------------------------------+
    /**
     * @param        $id
     * @param string $column
     * @return $this|void
     */
    public function setKey( $id, $column=null )
    {
        if( !$id ) return $this;
        $column = $column ?: $this->keyName;
        $this->where( $this->$column->eq( $id ) );
        return $this;
    }

    /**
     * @param null|int $limit
     * @return array
     */
    public function select($limit=null)
    {
        $limit = $this->hook( 'selecting', $limit );
        if( $limit ) $this->limit($limit);
        $this->toSelect();
        $data = $this->performRead( 'fetchAll' );
        $data = $this->hook( 'selected', $data );
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
        $this->hook( 'counting' );
        $this->toCount();
        $count = $this->performRead( 'fetchValue' );
        $count = $this->hook( 'counted', $count );
        return $count;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        list( $id, $column ) = $this->hook( 'loading', [ $id, $column ] );
        $this->setKey($id, $column);
        $data = $this->performRead( 'fetchAll' );
        $data = $this->hook( 'loaded', $data );
        $this->reset();
        return $data;
    }

    /**
     * @param $data
     * @throws InvalidArgumentException
     * @return int|PdoStatement
     */
    public function save( $data )
    {
        $by   = $this->hook( 'saveMethod', $data );
        if( !$by ) {
            throw new InvalidArgumentException( 'save method not defined. ' );
        }
        $data = $this->hook( 'saving', $data );
        $stmt = $this->$by( $data);
        $stmt = $this->hook( 'saved', $stmt );
        return $stmt;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        $data = $this->hook( 'createStamp', $data );
        $data = $this->hook( 'inserting', $data );
        if( $data ) $this->value($data);
        $this->toInsert();
        $this->performWrite();
        $id = ( $this->returnLastId ) ? $this->lastId() : true;
        $id = $this->hook( 'inserted', $id );
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
        $data = $this->hook( 'updateStamp', $data );
        $data = $this->hook( 'updating', $data );
        if( $data ) $this->value($data);
        $this->toUpdate();
        $stmt = $this->performWrite();
        $stmt = $this->hook( 'updated', $stmt );
        return $stmt;
    }

    /**
     * @param int $id
     * @param string $column
     * @return string
     */
    public function delete( $id=null, $column=null )
    {
        list( $id, $column ) = $this->hook( 'deleting', [ $id, $column ] );
        $this->setKey($id, $column);
        $this->toDelete();
        $stmt = $this->performWrite();
        $stmt = $this->hook( 'deleted', $stmt );
        $this->reset();
        return $stmt;
    }

    // +----------------------------------------------------------------------+
}