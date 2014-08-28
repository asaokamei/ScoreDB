<?php
namespace WScore\ScoreDB\Dao;

trait TableAndKeyNameTrait
{
    abstract public function getTable();

    abstract protected function setTable( $table );

    abstract public function getKeyName();

    abstract protected function setKeyName( $name );

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
            $this->setTable( $table );
        }
        if( !$this->getKeyName() ) {
            $this->setKeyName( $table . '_id' );
        }
    }
}