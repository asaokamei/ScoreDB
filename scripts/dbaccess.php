<?php
namespace WScore\DbAccess;

use Monolog\Handler\NullHandler;
use Monolog\Logger;

$log = new Logger( 'DbAccess' );
$log->pushHandler( new NullHandler() );
$dba = new DbAccess();
$dba->sqlBuilder = new SqlBuilder();
$dba->log = new Profile( $log );
return $dba->connect( new DbConnect() );