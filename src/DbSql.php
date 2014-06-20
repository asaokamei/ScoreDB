<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use PdoStatement;
use Traversable;
use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Factory;
use WScore\SqlBuilder\Sql\Sql;

class DbSql extends Sql implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $hooks = [];
    
    /**
     * @var string
     */
    protected $connectName = '';

    /**
     * @var bool
     */
    protected $returnLastId = true;

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
        return $this->performRead( 'fetchAll' );
    }

    /**
     * @param array $data
     * @return int|bool
     */
    public function insert( $data=array() )
    {
        if( $data ) $this->value($data);
        $data = $this->hooks( 'inserting' );
        $this->performWrite( 'insert' );
        $id = $this->getLastId();
        $this->hooks( 'inserted', $data );
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
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        $this->hooks( 'updating' );
        $stmt = $this->performWrite( 'update' );
        $this->hooks( 'updated' );
        return $stmt;
    }

    /**
     * @param null|int     $id
     * @param null|string  $column
     * @return PDOStatement
     */
    public function delete( $id=null, $column=null )
    {
        if( $id ) {
            $column ?: $column = $this->keyName;
            $this->$column->eq( $id );
        }
        $this->hooks( 'deleting' );
        $stmt = $this->performWrite( 'delete' );
        $this->hooks( 'deleted' );
        return $stmt;
    }

    /**
     * @param $method
     * @return mixed
     */
    protected function performRead( $method )
    {
        $pdo     = Dba::db( $this->connectName );
        $builder = $this->getBuilder( $pdo );
        $sql     = $builder->toSelect( $this );
        $bind    = $builder->getBind()->getBinding();
        return $pdo->$method( $sql, $bind );
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
        return $pdo->perform( $sql, $bind );
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
     * @param string $event
     * @param mixed|null   $data
     * @return mixed|null
     */
    protected function hooks( $event, $data=null )
    {
        $args = func_get_args();
        array_shift($args);
        foreach( $this->hooks as $hook ) {
            if( method_exists( $hook, $method = 'on'.ucfirst($event).'Hook' ) ) {
                call_user_func_array( [$hook, $method], $args );
            }
            if( method_exists( $hook, $method = 'on'.ucfirst($event).'Filter' ) ) {
                $data = call_user_func_array( [$hook, $method], $args );
            }
        }
        return $data;
    }

    /**
     * @param object $hook
     */
    public function setHook( $hook )
    {
        $this->hooks[] = $hook;
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