<?php
namespace WScore\ScoreDB\Dao;

use WScore\ScoreDB\Dao;

class TableAndKeyName
{
    /**
     * set up table and keyName based on the class name
     * if they are not set.
     *
     * @param mixed     $data
     * @param Dao       $query
     */
    public function onConstructingHook(
        /** @noinspection PhpUnusedParameterInspection */
        $data, $query )
    {
        if( !$table = $query->getTable() ) {
            $table = get_class($query);
            if( false!==strpos($table, '\\') ) {
                $table = substr( $table, strrpos($table,'\\')+1 );
            }
            $query->table( $table );
        }
        if( !$query->getKeyName() ) {
            $query->keyName( $table . '_id' );
        }
    }
}