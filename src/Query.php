<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use IteratorAggregate;
use PdoStatement;
use Traversable;
use WScore\SqlBuilder\QueryInterface;
use WScore\SqlBuilder\Query as SqlQuery;

class Query extends SqlQuery implements IteratorAggregate, QueryInterface
{
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
    }

    /**
     * @param $sql
     * @param string $method
     * @return PDOStatement|array
     */
    protected function perform( $sql, $method='perform' )
    {
        $bind  = $this->getBind();
        return $this->pdo->$method( $sql, $bind );
    }

    protected function performFetch( $sql, $method='fetchAll' )
    {

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
        $this->setPdoAndDbType();
        $sql = parent::select($limit);
        $this->hooks( 'selecting', $limit );
        $data = $this->perform( $sql, 'fetchAll' );
        $data = $this->hooks( 'selected', $data );
        return $data;
    }

    /**
     * Retrieve an external iterator
     * @return Traversable|PdoStatement
     */
    public function getIterator()
    {
        $this->setPdoAndDbType();
        $sql = parent::select();
        return $this->perform( $sql, 'perform' );
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->setPdoAndDbType();
        $this->hooks( 'counting' );
        $sql   = parent::count();
        $count = $this->perform( $sql, 'fetchValue' );
        $count = $this->hooks( 'counted', $count );
        return $count;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $this->setPdoAndDbType();
        $id   = $this->hooks( 'loading', $id, $column );
        $sql  = parent::load( $id, $column );
        $data = $this->perform( $sql, 'fetchAll' );
        $data = $this->hooks( 'loaded', $data );
        return $data;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        $this->setPdoAndDbType('write');
        $data = $this->hooks( 'inserting', $data );
        $sql = parent::insert($data);
        $this->perform( $sql );
        $id = ( $this->returnLastId ) ? $this->lastId() : true;
        $id = $this->hooks( 'inserted', $id );
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
        $this->setPdoAndDbType('write');
        $data = $this->hooks( 'updating', $data );
        $sql = parent::update($data);
        $stmt = $this->perform( $sql );
        $stmt = $this->hooks( 'updated', $stmt );
        return $stmt;
    }

    /**
     * @param null|int     $id
     * @return PDOStatement
     */
    public function delete( $id=null )
    {
        $this->setPdoAndDbType('write');
        $id = $this->hooks( 'deleting', $id );
        $sql = parent::delete($id);
        $stmt = $this->perform( $sql );
        $stmt = $this->hooks( 'deleted', $stmt );
        return $stmt;
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
     * @param string       $event
     * @param mixed|null   $data
     * @return mixed|null
     */
    protected function hooks( $event, $data=null )
    {
        if( $this->hooks ) {
            $args = func_get_args();
            $data = call_user_func_array( [$this->hooks, 'hook'], $args );
        }
        return $data;
    }

    /**
     * @param Hooks $hook
     */
    public function setHook( $hook )
    {
        $this->hooks = $hook;
        $this->hooks->setHook( $this );
    }

    // +----------------------------------------------------------------------+
}