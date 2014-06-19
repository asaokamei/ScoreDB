<?php
namespace WScore\DbAccess;

use Aura\Sql\ExtendedPdo;
use WScore\DbAccess\Sql\Builder;
use WScore\DbAccess\Sql\Query;

class DbSql extends Query
{
    /**
     * @var ExtendedPdo
     */
    protected $pdo;

    /**
     * @var ExtendedPdo
     */
    protected $pdoWrite;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param ExtendedPdo $pdo
     * @param Builder     $builder
     * @param ExtendedPdo $pdoWrite
     */
    public function setup( $pdo, $builder, $pdoWrite=null )
    {
        $this->pdo = $pdo;
        $this->builder = $builder;
        $this->pdoWrite = $pdoWrite;
    }

    /**
     * @param null|int $limit
     * @return array
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        $sql  = $this->builder->toInsert( $this );
        $bind = $this->bind()->getBinding();
        return $this->pdo->fetchAll( $sql, $bind );
    }

    /**
     * @param array $data
     * @return \PDOStatement
     */
    public function insert( $data=array() )
    {
        if( $data ) $this->value($data);
        $sql  = $this->builder->toInsert( $this );
        $bind = $this->bind()->getBinding();
        return $this->performWrite( $sql, $bind );
    }

    /**
     * @param array $data
     * @return \PDOStatement
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        $sql  = $this->builder->toUpdate( $this );
        $bind = $this->bind()->getBinding();
        return $this->performWrite( $sql, $bind );
    }

    /**
     * @return \PDOStatement
     */
    public function delete()
    {
        $sql  = $this->builder->toDelete( $this );
        $bind = $this->bind()->getBinding();
        return $this->performWrite( $sql, $bind );
    }

    /**
     * @param string $sql
     * @param array  $bind
     * @return \PDOStatement
     */
    protected function performWrite( $sql, $bind )
    {
        $pdo = $this->pdoWrite ?: $this->pdo;
        return $pdo->perform( $sql, $bind );
    }
}