<?php
namespace WScore\DbAccess;

$dba = include( __DIR__ . '/dbaccess.php' );
return new \WScore\DbAccess\Query( $dba );