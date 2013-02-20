<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

class All_DbAccess_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s DbAccess' );
        $folder = dirname( __FILE__ ) . '/';
        //$suite->addTestFile( $folder . 'Rdb_Test.php' );
        $suite->addTestFile( $folder . 'DbConnect_MySql_Test.php' );
        $suite->addTestFile( $folder . 'DbConnect_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'DbConnect_Sqlite_Test.php' );
        $suite->addTestFile( $folder . 'DbAccess_MySql_Test.php' );
        $suite->addTestFile( $folder . 'DbAccess_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'Query_Test.php' );
        $suite->addTestFile( $folder . 'Query_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Query_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'Query_PgSql_Quoted_Test.php' );
        return $suite;
    }
}

