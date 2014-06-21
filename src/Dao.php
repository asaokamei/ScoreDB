<?php
namespace WScore\DbAccess;

use DateTime;

class Dao extends DbSql
{
    /**
     * @var string
     */
    protected $originalTable;

    /**
     * @var DateTime
     */
    protected $now;

    /**
     * @var string      format for created/updated at stamps.
     */
    protected $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * time stamps config.
     * [ type => [ column, format ], type => column ]
     *
     * @var array
     */
    protected $timeStamps = array(
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    );

    /**
     * sets table and keyName from class name if they are not set. 
     * 
     * @param Hooks $hook
     */
    public function __construct( $hook=null )
    {
        if( $hook ) $hook->setHook( $this );
        $this->hooks( 'constructing' );

        if( !$this->table ) {
            $this->table = get_class($this);
            if( false!==strpos($this->table, '\\') ) {
                $this->table = substr( $this->table, strrpos($this->table,'\\')+1 );
            }
        }
        if( !$this->primaryKey ) {
            $this->primaryKey = $this->table . '_id';
        }
        $this->originalTable = $this->table;
        $this->hooks( 'constructed' );
    }

    /**
     * @param array $data
     * @return array
     */
    public function onInsertingFilter( $data )
    {
        if( $at = $this->timeStamps['created_at'] ) $data[ $at ] = $this->getNow();
        if( $at = $this->timeStamps['updated_at'] ) $data[ $at ] = $this->getNow();
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onUpdatingFilter( $data )
    {
        if( $at = $this->timeStamps['updated_at'] ) $data[ $at ] = $this->getNow();
        return $data;
    }

    /**
     * @return string
     */
    protected function getNow()
    {
        if( !$this->now ) $this->now = new DateTime();
        return $this->now->format( $this->dateTimeFormat );
    }
    
    /**
     * @param $method
     * @param $args
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call( $method, $args )
    {
        if( method_exists( $this, $scope = 'scope'.ucfirst($method) ) ) {
            call_user_func_array( [$this, $scope], $args );
            return $this;
        }
        throw new \BadMethodCallException( 'no such method: '.$method );
    }

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
    
}