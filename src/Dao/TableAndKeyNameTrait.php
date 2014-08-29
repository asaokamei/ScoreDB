<?php
namespace WScore\ScoreDB\Dao;

trait TableAndKeyNameTrait
{
    abstract public function getTable();

    abstract public function table( $table, $alias = null );

    abstract public function getKeyName();

    abstract public function keyName( $name );

    /**
     * set up table and keyName based on the class name
     * if they are not set.
     */
    public function onConstructingHook()
    {
        if( !$table = $this->getTable() ) {
            $table = get_class($this);
            if( false!==strpos($table, '\\') ) {
                $table = substr( $table, strrpos($table,'\\')+1 );
            }
            $this->table( $table );
        }
        if( !$this->getKeyName() ) {
            $this->keyName( $table . '_id' );
        }
    }
}