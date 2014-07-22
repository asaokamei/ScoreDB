<?php
namespace tests\DbAccess;

use WScore\ScoreDB\Dba;
use WScore\ScoreDB\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class Dba_Mysql_Test extends \PHPUnit_Framework_TestCase
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

    static function setupBeforeClass()
    {
        class_exists( 'WScore\ScoreDB\Dba' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        Dba::reset();
    }

    function test_mysql_create()
    {
        Dba::config( include( __DIR__ . '/configs/mysql-config.php' ) );
        $pdo = Dba::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        $pdo->query( include( __DIR__ . '/configs/mysql-create.php' ) );
    }

    function test_pgsql_create()
    {
        Dba::config( include( __DIR__ . '/configs/pgsql-config.php' ) );
        $pdo = Dba::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        $pdo->query( include( __DIR__ . '/configs/pgsql-create.php' ) );
        $pdo->query( $sql );
    }
}
