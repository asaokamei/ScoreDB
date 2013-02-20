<?php
namespace WSTest\DbAccess;

class Mock_QueryPdo
{
    var $config;
    var $sql, $prep, $type;
    var $stmt = 'stmt';
    var $query;
    function __construct() {
        $this->config = func_get_args();
    }
    function exec( $sql, $prep, $type ) {
        $this->sql = $sql;
        $this->prep = $prep;
        $this->type = $type;
    }
    function stmt() {
        return NULL;
    }
    function query( $query ) {
        $this->query = $query;
    }
}
