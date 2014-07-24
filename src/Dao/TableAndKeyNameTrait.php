<?php
namespace WScore\ScoreDB\Dao;

trait TableAndKeyNameTrait
{
    /**
     * set up table and keyName based on the class name
     * if they are not set.
     */
    public function onConstructingHook()
    {
        if( !isset( $this->table ) ) {
            $this->table = get_class($this);
            if( false!==strpos($this->table, '\\') ) {
                $this->table = substr( $this->table, strrpos($this->table,'\\')+1 );
            }
        }
        if( !isset( $this->keyName ) ) {
            $this->keyName = $this->table . '_id';
        }
    }
}