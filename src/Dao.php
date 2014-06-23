<?php
namespace WScore\DbAccess;

use DateTime;

class Dao extends Query
{
    /**
     * @var string
     */
    protected $originalTable;

    /**
     * @var DateTime
     */
    public static $now;

    /**
     * time stamps config.
     * [ type => [ column, format ], type => column ]
     *
     * @var array
     */
    protected $timeStamps = array(
        'created_at' => ['created_at' => 'Y-m-d H:i:s' ],
        'updated_at' => ['updated_at' => 'Y-m-d H:i:s' ],
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
        if( !$this->keyName ) {
            $this->keyName = $this->table . '_id';
        }
        $this->originalTable = $this->table;
        $this->hooks( 'constructed' );
    }

    /**
     * @return $this
     */
    public static function forge()
    {
        /** @var Dao $self */
        $self = new static();
        $self->setHook( new Hooks() );
        return $self;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onInsertingFilter( $data )
    {
        if( $at = $this->timeStamps['created_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        if( $at = $this->timeStamps['updated_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onUpdatingFilter( $data )
    {
        if( $at = $this->timeStamps['updated_at'] ) {
            $data = $this->onTimeStampFilter( $data, $at );
        }
        return $data;
    }

    /**
     * @param array $data
     * @param array $filters
     * @return array
     */
    protected function onTimeStampFilter( $data, $filters ) 
    {
        if( !static::$now ) static::$now = new DateTime();
        foreach( $filters as $column => $format ) {
            if( is_numeric( $column ) ) {
                $column = $format;
                $format = 'Y-m-d H:i:s';
            }
            $data[ $column ] = static::$now->format( $format );
        }
        return $data;
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
    
}