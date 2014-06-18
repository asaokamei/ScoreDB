<?php
namespace WScore\Basic\Dba;

class Query
{
    /**
     * @var Where
     */
    protected $where;

    /**
     * @var string           name of database table
     */
    public $table;

    /**
     * @var string           name of id (primary key)
     */
    public $id_name;

    /**
     * @var array            join for table
     */
    public $join = [];

    /**
     * @var string|array     columns to select in array or string
     */
    public $columns = [];

    /**
     * @var array            values for insert/update in array
     */
    public $values = [];

    /**
     * @var string[]         such as distinct, for update, etc.
     */
    public $selFlags = [];

    /**
     * @var array            order by. [ [ order, dir ], [].. ]
     */
    public $order = [];

    /**
     * @var string           group by. [ group, group2, ...]
     */
    public $group = [];

    /**
     * @var string
     */
    public $having = [];

    /**
     * @var int
     */
    public $limit = null;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var string
     */
    public $returning;

    /**
     * @var int
     */
    protected static $prepared_counter = 1;

    /**
     * @var array    stores prepared values and holder name
     */
    protected $prepared_values = array();

    /**
     * @var array    stores data types of place holders
     */
    protected $prepared_types = array();

    /**
     * @var array    stores data types of columns
     */
    protected $col_data_types = array();

    /**
     * @var string
     */
    public $tableAlias;

    // +----------------------------------------------------------------------+
    /**
     * @param Where   $where
     */
    public function __construct( $where ) {
        $this->where = $where;
    }

    /**
     * @return Query
     */
    public function fresh() {
        $self = new Query( $this->where );
        return $self;
    }

    /**
     * @param $value
     * @return callable
     */
    public static function raw( $value ) {
        return function() use( $value ) {
            return $value;
        };
    }

    /**
     * @return Where
     */
    public function where() {
        return $this->where;
    }

    // +----------------------------------------------------------------------+
    //  preparing for Insert and Update statement.
    // +----------------------------------------------------------------------+
    /**
     * replaces value with place holder for prepared statement.
     * the value is kept in prepared_value array.
     *
     * if $type is specified, or column data type is set in col_data_types,
     * types for the place holder is kept in prepared_types array.
     *
     * @param string|array $val
     * @param null|int     $type    data type
     * @param null $col     column name. used to find data type
     * @return string|array
     */
    public function prepare( $val, $type=null, $col=null )
    {
        if( is_array( $val ) ) {
            $holder = [];
            foreach( $val as $key => $v ) {
                $holder[$key] = $this->prepare( $v, $type, $col );
            }
            return $holder;
        }
        if( is_callable( $val ) ) return $val;

        $holder = ':db_prep_' . static::$prepared_counter++;
        $this->prepared_values[ $holder ] = $val;
        if( $type ) {
            $this->prepared_types[ $holder ] = $type;
        }
        elseif( $col && array_key_exists( $col, $this->col_data_types ) ) {
            $this->prepared_types[ $holder ] = $this->col_data_types[ $col ];
        }
        return $holder;
    }

    /**
     * @return array
     */
    public function getBinding()
    {
        return $this->prepared_values;
    }

    /**
     * @param Query $query
     */
    public function mergeBinding( $query )
    {
        $this->prepared_values = array_merge( $this->prepared_values, $query->getBinding() );
    }

    // +----------------------------------------------------------------------+
    //  Setting string, array, and data to build SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @param string $id_name
     * @return Query
     */
    public function table( $table, $id_name=null ) {
        $this->table   = $this->table = $table;
        $this->id_name = $id_name ?: null;
        return $this;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function alias( $alias ) {
        $this->tableAlias = $alias;
        return $this;
    }

    /**
     * @param string $column
     * @param null|string $as
     * @return Query
     */
    public function column( $column, $as=null ) {
        $this->columns[ $column ] = $as;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function value( $name, $value=null ) {
        if( is_array( $name ) ) {
            $this->values = $name;
        }
        elseif( func_num_args() > 1 ) {
            $this->values[ $name ] = $value;
        }
        return $this;
    }

    /**
     * @param string $order
     * @param string $sort
     * @return $this
     */
    public function order( $order, $sort='ASC' ) {
        $this->order[] = [ $order, $sort ];
        return $this;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function group( $group ) {
        $this->group[] = $group;
        return $this;
    }

    /**
     * @param string $having
     * @return $this
     */
    public function having( $having ) {
        $this->having[] = $having;
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit( $limit ) {
        $this->limit  = ( is_numeric( $limit ) ) ? $limit: null;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset( $offset ) {
        $this->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }

    /**
     * creates SELECT DISTINCT statement.
     * @return Query
     */
    public function distinct() {
        return $this->flag( 'DISTINCT' );
    }

    /**
     * creates SELECT for UPDATE statement.
     * @return Query
     */
    public function forUpdate() {
        return $this->flag( 'FOR UPDATE' );
    }

    /**
     * @param $flag
     * @return $this
     */
    public function flag( $flag ) {
        $this->selFlags[] = $flag;
        return $this;
    }

    /**
     * @param string $return
     * @return $this
     */
    public function returning( $return ) {
        $this->returning = $return;
        return $this;
    }
    // +----------------------------------------------------------------------+
}