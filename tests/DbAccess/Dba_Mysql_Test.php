<?php
namespace tests\DbAccess;

use WScore\ScoreDB\DB;
use WScore\ScoreDB\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class Dba_Mysql_Test extends \PHPUnit\Framework\TestCase
{
    var $configMysql = [
        'dsn' => 'mysql:dbname=test_WScore',
        'user' => 'admin',
        'pass' => 'admin',
    ];

    var $configPgsql = [
        'dsn' => 'pgsql:dbname=test_WScore',
        'user' => 'pg_admin',
        'pass' => 'admin',
    ];

    public static function setUpBeforeClass() : void
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        DB::reset();
    }

    function test_mysql_create()
    {
        DB::config( include( __DIR__ . '/configs/mysql-config.php' ) );
        $pdo = DB::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        $pdo->query( include( __DIR__ . '/configs/mysql-create.php' ) );
    }

    function test_pgsql_create()
    {
        DB::config( include( __DIR__ . '/configs/pgsql-config.php' ) );
        $pdo = DB::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        $pdo->query( include( __DIR__ . '/configs/pgsql-create.php' ) );
        $pdo->query( $sql );
    }
}
