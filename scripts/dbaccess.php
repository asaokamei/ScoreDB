<?php
namespace WScore\DbAccess;

$dba = new DbAccess();
return $dba->connect( new DbConnect() );