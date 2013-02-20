<?php
namespace WScore\DbAccess;

$dba = new DbAccess();
$dba->sqlBuilder = new SqlBuilder();
return $dba->connect( new DbConnect() );