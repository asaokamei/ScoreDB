<?php
namespace WScore\Basic\Dba;

class Builder
{
    /**
     * @var Query
     */
    protected $query;
    
    protected $select = [
        'flags',
        'column',
        'table',
        'tableAlias',
        'join',
        'where',
        'groupBy',
        'having',
        'orderBy',
    ];
    
    protected $insert = [
        'table',
        'insertCol',
        'insertVal'
    ];

    protected $update = [
        'table',
        'updateSet',
        'where',
    ];
    /**
     * @var Quote
     */
    protected $quote;

    // +----------------------------------------------------------------------+
    //  construction
    // +----------------------------------------------------------------------+
    /**
     * @param string $db
     */
    public function __construct( $db )
    {
        if( $db == 'mysql' ) {
            
            $this->quote->setQuote('`');
            $this->select[] = 'limitOffset';
            $this->update[] = 'limit';
            
        } elseif( $db == 'pgsql' ) {
            
            $this->select[] = 'limit';
            $this->select[] = 'offset';
            $this->insert[] = 'returning';
            $this->update[] = 'returning';
            
        } elseif( $db == 'pgsql' ) {
            
            $this->select[] = 'limit';
            $this->select[] = 'offset';
        }
    }

    // +----------------------------------------------------------------------+
    //  convert to SQL statements.
    // +----------------------------------------------------------------------+
    /**
     * @param Query $query
     * @return string
     */
    public function toSelect( $query )
    {
        $this->query = $query;
        $sql = 'SELECT' . $this->buildByList( $this->select );
        return $sql;
    }

    /**
     * @param Query $query
     * @return string
     */
    public function toInsert( $query )
    {
        $this->query = $query;
        $sql = 'INSERT INTO' . $this->buildByList( $this->insert );
        return $sql;
    }

    /**
     * @param Query $query
     * @return string
     */
    public function toUpdate( $query )
    {
        $this->query = $query;
        $sql = 'UPDATE' . $this->buildByList( $this->insert );
        return $sql;
    }

    // +----------------------------------------------------------------------+
    //  builders
    // +----------------------------------------------------------------------+
    /**
     * @param $list
     * @return string
     */
    protected function buildByList( $list )
    {
        $statement = '';
        foreach( $list as $item ) {
            $method = 'build'.ucwords($item);
            if( $sql = $this->$method ) {
                $statement = ' ' . $sql;
            }
        }
        return $statement;
    }

    /**
     * @return string
     */
    protected function buildInsertCol() {
        $columns = array_keys( $this->query->values );
        foreach( $columns as $key => $col ) {
            $columns[$key] = $this->quote->quote($col);
        }
        return '( '.implode( ', ', $columns ).' )';
    }

    /**
     * @return string
     */
    protected function buildInsertVal() {
        $columns = [];
        foreach( $this->query->values as $key => $col ) {
            if( is_callable($col) ) {
                $columns[$key] = $col();
            } else {
                $columns[$key] = $col;
            }
        }
        return 'VALUES ( '.implode( ', ', $columns ).' )';
    }
    
    protected function buildUpdateSet() {
        $setter = [];
        foreach( $this->query->values as $col => $val ) {
            if( is_callable($val) ) {
                $val = $val();
            }
            $setter[] = $this->quote->quote($col).'='.$val;
        }
        return 'SET '.implode( ', ', $setter );
    }
    
    /**
     * @return string
     */
    protected function buildFlags() {
        return $this->query->selFlags ? implode( ' ', $this->query->selFlags ) : '';
    }
    
    /**
     * @return string
     */
    protected function buildTable() {
        return $this->quote->quote( $this->query->table );
    }

    /**
     * @return string
     */
    protected function buildTableAlias() {
        return $this->query->tableAlias ? $this->quote->quote( $this->query->tableAlias ) : '';
    }

    /**
     * @return string
     */
    protected function buildJoin() {
        return '';
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function buildColumn() {
        if( !$this->query->columns ) {
            throw new \InvalidArgumentException('No column is set');
        }
        return implode( ', ', $this->query->columns );
    }

    /**
     * @return string
     */
    protected function buildWhere() {
        return $this->query->where()->__toString();
    }

    /**
     * @return string
     */
    protected function buildGroupBy() {
        return $this->query->group ? 'GROUP BY '.implode( ', ', $this->query->group ) : '';
    }

    /**
     * @return string
     */
    protected function buildOrderBy() {
        if( !$this->query->order ) return '';
        $sql = [];
        foreach( $this->query->order as $order ) {
            $sql[] = $order[0]." ".$order[1];
        }
        return 'ORDER BY ' . implode( ', ', $sql );
    }

    /**
     * @return string
     */
    protected function buildLimit() {
        if( is_numeric( $this->query->limit ) && $this->query->limit > 0 ) {
            return "LIMIT ".$this->query->limit;
        }
        return '';
    }

    /**
     * @return string
     */
    protected function buildOffset() {
        if( is_numeric( $this->query->offset ) && $this->query->offset > 0 ) {
            return "OFFSET ".$this->query->offset;
        }
        return '';
    }

    /**
     * @return string
     */
    protected function buildLimitOffset() {
        $sql = '';
        if ( $this->query->limit && $this->query->offset ) {
            $sql .= ' LIMIT ' . $this->query->offset . ' , ' . $this->query->limit;
        } elseif ( $this->query->limit ) {
            $sql .= ' LIMIT ' . $this->query->limit;
        }
        return $sql;
    }

    /**
     * @return string
     */
    protected function buildReturning() {
        return $this->query->returning ? 'RETURNING '.$this->query->returning:'';
    }
    
    // +----------------------------------------------------------------------+
}