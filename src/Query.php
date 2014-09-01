<?php
namespace WScore\ScoreDB;

use Aura\Sql\ExtendedPdo;
use InvalidArgumentException;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\ScoreDB\Hook\Hooks;
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
    protected $returnLastId = true;

    /**
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
        return $this->perform( $pdo, 'perform' );
    }

    /**
     * @param string $method
     * @return \PdoStatement|array
     */
    protected function performRead( $method=null )
    {
        $pdo = $this->setPdoAndDbType();
        $stm = $this->perform( $pdo, $method );
        if( is_object($stm) && $stm instanceof \PdoStatement ) {
            $this->setFetchMode( $stm );
        }
        return $stm;
    }

    /**
     * overwrite this method to set fetch mode.
     *
     * @param \PdoStatement $stm
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
     * @param \PdoStatement $stm
     * @return bool
     */
    protected function setFetchClass( $stm )
    {
        return $stm->setFetchMode( \PDO::FETCH_CLASS, $this->fetch_class, [] );
    }

    /**
     * @param ExtendedPdo $pdo
     * @param string $method
     * @return mixed
     */
    protected function perform( $pdo, $method )
    {
        $sql = (string) $this;
        $bind  = $this->getBind();
        if( !$method ) {
            $method = $this->fetch_class ? 'perform' : 'fetchAll';
        }
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
            $data = $this->hooks->hook( $event, $data, $this );
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
    public function key( $id, $column=null )
    {
        if( !$id ) return $this;
        $column = $column ?: $this->keyName;
        $this->where( $this->$column->eq( $id ) );
        return $this;
    }

    /**
     * @param null|int $limit
     * @return array|\PdoStatement
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        $this->toSelect();
        $data = $this->performRead();
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
     * @return string|\PdoStatement
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