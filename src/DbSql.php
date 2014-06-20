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
     * @var string
     */
    protected $connectName = '';

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
     * @return PDOStatement
     */
    public function insert( $data=array() )
    {
        if( $data ) $this->value($data);
        return $this->performWrite( 'insert' );
    }

    /**
     * @param array $data
     * @return PDOStatement
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        return $this->performWrite( 'update' );
    }

    /**
     * @return PDOStatement
     */
    public function delete()
    {
        return $this->performWrite( 'delete' );
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

    /**
     * Retrieve an external iterator
     * @return Traversable|PdoStatement
     */
    public function getIterator()
    {
        return $this->performRead( 'perform' );
    }
}