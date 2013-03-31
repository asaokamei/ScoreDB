<?php
namespace WScore\DbAccess;

Interface QueryInterface
{
    public function clear();
    public function connect( $config=null );
    public function lastId( $name=null );
    public function lockTable( $table=null );
    public function exec();
    public function table( $table, $id_name='id' );
    public function update( $values=array() );
    public function insert( $values=array() );
    public function select( $column=null );
    public function count();



}
