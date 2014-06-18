<?php
namespace WScore\Basic\Dba;

class Where
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array|string
     */
    protected $where = array();

    /**
     * @var string
     */
    protected $column;

    // +----------------------------------------------------------------------+
    //  managing objects.
    // +----------------------------------------------------------------------+
    /**
     * @param Query $q
     */
    public function setQuery( $q ) {
        $this->query = $q;
    }

    /**
     * @return Query
     */
    public function q() {
        return $this->query;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call( $method, $args ) {
        return call_user_func_array( [$this->query, $method ], $args );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $where = '';
        foreach( $$this->where as $w ) {
            if( is_array( $w ) ) {
                $where .= $this->formWhere( $w['col'], $w['val'], $w['rel'], $w['op'] );
            } elseif( is_string( $w ) ) {
                $where .= 'and ' .$w;
            }
        }
        $where = trim( $where );
        $where = preg_replace( '/^(and|or) /i', '', $where );
        return $where;
    }

    /**
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return string
     */
    protected function formWhere( $col, $val, $rel='=', $op='AND' )
    {
        $where = '';
        $rel = strtoupper( $rel );
        if( $rel == 'IN' || $rel == 'NOT IN' ) {
            $tmp = is_array( $val ) ? implode( ", ", $val ): "{$val}";
            $val = "( " . $tmp . " )";
        }
        elseif( $col == '(' ) {
            $val = $rel = '';
        }
        elseif( $col == ')' ) {
            $op = $rel = $val = '';
        }
        elseif( "$val" == "" && "$rel" == "" ) {
            return '';
        }
        $where .= trim( "{$op} {$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

    // +----------------------------------------------------------------------+
    //  setting columns.
    // +----------------------------------------------------------------------+

    /**
     * set where statement with values properly prepared/quoted.
     *
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string|bool $type
     * @return $this
     */
    public function where( $col, $val, $rel = '=', $type = null )
    {
        $holder = $this->query->prepare( $val, $type, $col );
        return $this->whereRaw( $col, $holder, $rel );
    }

    /**
     * set where statement as is.
     *
     * @param        $col
     * @param        $val
     * @param string $rel
     * @return $this
     */
    public function whereRaw( $col, $val, $rel = '=' )
    {
        $where          = array( 'col' => $col, 'val' => $val, 'rel' => $rel, 'op' => 'AND' );
        $this->where[ ] = $where;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function __get( $name ) {
        return $this->col( $name );
    }

    /**
     * @param string $col
     * @return $this
     */
    public function col( $col )
    {
        $this->column = $col;
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  where clause.
    // +----------------------------------------------------------------------+
    /**
     * @param string|array $val
     * @param null $type
     * @return $this
     */
    public function id( $val, $type = null )
    {
        if ( is_array( $val ) ) {
            return $this->col( $this->query->id_name )->in( $val, $type );
        }
        return $this->where( $this->query->id_name, $val, '=', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function eq( $val, $type = null )
    {
        if ( is_array( $val ) ) {
            return $this->in( $val, $type );
        }
        return $this->where( $this->column, $val, '=', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function ne( $val, $type = null )
    {
        return $this->where( $this->column, $val, '!=', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function lt( $val, $type = null )
    {
        return $this->where( $this->column, $val, '<', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function le( $val, $type = null )
    {
        return $this->where( $this->column, $val, '<=', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function gt( $val, $type = null )
    {
        return $this->where( $this->column, $val, '>', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function ge( $val, $type = null )
    {
        return $this->where( $this->column, $val, '>=', $type );
    }

    /**
     * @param array $values
     * @param bool $not
     * @param null $type
     * @return $this
     */
    public function in( $values, $not=false, $type = null )
    {
        $holders = $this->query->prepare( $values );
        $holders = '(' . implode( ', ', $holders ) . ')';
        $rel = $not ? 'NOT IN' : 'IN';
        return $this->whereRaw( $this->column, $holders, $rel, $type );
    }

    /**
     * @param $values
     * @param null $type
     * @return $this
     */
    public function notIn( $values, $type = null )
    {
        return $this->in( $values, true, $type );
    }

    /**
     * @param $val1
     * @param $val2
     * @param null $type
     * @return $this
     */
    public function between( $val1, $val2, $type = null )
    {
        return $this->whereRaw( $this->column, false, "BETWEEN $val1 and $val2", $type );
    }

    /**
     * @return $this
     */
    public function isNull()
    {
        return $this->whereRaw( $this->column, false, 'IS NULL' );
    }

    /**
     * @return $this
     */
    public function notNull()
    {
        return $this->whereRaw( $this->column, false, 'IS NOT NULL' );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function like( $val, $type = null )
    {
        return $this->where( $this->column, $val, 'LIKE', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function contain( $val, $type = null )
    {
        return $this->where( $this->column, "%{$val}%", 'LIKE', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function startWith( $val, $type = null )
    {
        return $this->where( $this->column, $val . '%', 'LIKE', $type );
    }

    /**
     * @param $val
     * @param null $type
     * @return $this
     */
    public function endWith( $val, $type = null )
    {
        return $this->where( $this->column, '%' . $val, 'LIKE', $type );
    }

}