<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use PdoStatement;
use Traversable;
use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Factory;
use WScore\SqlBuilder\Sql\Sql;
use WScore\SqlBuilder\Sql\Where;

class Query extends Sql implements \IteratorAggregate
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
     *
     */
    public function resetQuery()
    {
        $this->table     = $this->originalTable;
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
     * @param $column
     * @return Where
     */
    public function __get( $column )
    {
        $where = new Where();
        return $where->col( $column );
    }

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
     * @param null|int $limit
     * @return array
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        $this->hooks( 'selecting', $limit );
        $data = $this->performRead( 'fetchAll' );
        $data = $this->hooks( 'selected', $data );
        return $data;
    }

    /**
     * @return int
     */
    public function count()
    {
        $origColumn = $this->columns;
        $this->column( false ); // reset columns
        $this->column( $this::raw( 'COUNT(*)'), 'count' );
        $this->hooks( 'counting' );
        $count = $this->performRead( 'fetchValue', false );
        $count = $this->hooks( 'counted', $count );
        $this->columns = $origColumn;
        return $count;
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
        $page = (int) ( $page > 0 ?: 1 );
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
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $id = $this->hooks( 'loading', $id, $column );
        $this->setId($id, $column);
        $found = $this->select();
        $found = $this->hooks( 'loaded', $found );
        return $found;
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        $data = $this->hooks( 'inserting', $data );
        if( $data ) $this->value($data);
        $this->performWrite( 'insert' );
        $id = $this->getLastId();
        $id = $this->hooks( 'inserted', $id );
        return $id;
    }

    /**
     * @return bool|int
     */
    protected function getLastId()
    {
        if( $this->returnLastId ) {
            $pdo = Dba::dbWrite( $this->connectName );
            $name = ( $pdo->getAttribute(\Pdo::ATTR_DRIVER_NAME) == 'pgsql' ) 
                ? $this->table.'seq_id': null;
            return $pdo->lastInsertId( $name );
        }
        return true;
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

    /**
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        $data = $this->hooks( 'updating', $data );
        if( $data ) $this->value($data);
        $stmt = $this->performWrite( 'update' );
        $stmt = $this->hooks( 'updated', $stmt );
        return $stmt;
    }

    /**
     * @param null|int     $id
     * @return PDOStatement
     */
    public function delete( $id=null )
    {
        $id = $this->hooks( 'deleting', $id );
        $this->setId($id);
        $stmt = $this->performWrite( 'delete' );
        $stmt = $this->hooks( 'deleted', $stmt );
        return $stmt;
    }

    /**
     * @param      $method
     * @param bool $reset
     * @return mixed
     */
    protected function performRead( $method, $reset=true )
    {
        $pdo     = Dba::db( $this->connectName );
        $builder = $this->getBuilder( $pdo );
        $sql     = $builder->toSelect( $this );
        $bind    = $builder->getBind()->getBinding();
        $found   = $pdo->$method( $sql, $bind );
        if( $reset ) $this->resetQuery();
        return $found;
    }

    /**
     * @param string $type
     * @return PDOStatement
     */
    protected function performWrite( $type )
    {
        $pdo     = Dba::dbWrite( $this->connectName );
        $builder = $this->getBuilder( $pdo );
        $toSql   = 'to' . ucwords($type);
        $sql     = $builder->$toSql( $this );
        $bind    = $builder->getBind()->getBinding();
        $found   = $pdo->perform( $sql, $bind );
        $this->resetQuery();
        return $found;
    }

    /**
     * @param ExtendedPdo $pdo
     * @return Builder
     */
    protected function getBuilder( $pdo )
    {
        $type    = $pdo->getAttribute( \PDO::ATTR_DRIVER_NAME );
        return Factory::buildBuilder( $type );
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
    /**
     * Retrieve an external iterator
     * @return Traversable|PdoStatement
     */
    public function getIterator()
    {
        return $this->performRead( 'perform' );
    }

    // +----------------------------------------------------------------------+
}