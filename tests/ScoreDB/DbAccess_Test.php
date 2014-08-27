<?php
namespace tests\ScoreDB;

use WScore\ScoreDB\DB;
use WScore\ScoreDB\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class DbAccess_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbAccess
     */
    var $dba;
    
    function setup()
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        $this->dba = new DbAccess();
    }
    
    function test0()
    {
        $this->assertEquals( 'WScore\ScoreDB\DbAccess', get_class( $this->dba ) );
    }

    /**
     * @test
     */
    function Dba_config_and_db_returns_the_config()
    {
        DB::config( function() {
            return 'tested';
        } );
        $this->assertEquals( 'tested', DB::connect() );
        $this->assertEquals( 'tested', DB::connectWrite() );

        DB::config( 'named', function() {
            return 'named-tested';
        } );
        $this->assertEquals( 'named-tested', DB::connect('named') );
        $this->assertEquals( 'named-tested', DB::connectWrite('named') );
    }

    /**
     * @test
     */
    function Dba_reset_returns_null()
    {
        DB::config( function() {
            return 'tested';
        } );
        $this->assertEquals( 'tested', DB::connect() );
        $this->assertEquals( 'tested', DB::connectWrite() );

        DB::restart();
        $this->assertEquals( null, DB::connect('named') );
        $this->assertEquals( null, DB::connectWrite('named') );
    }

    /**
     * @test
     */
    function config_for_read_and_write()
    {
        $configMy = include( dirname(__DIR__) . '/configs/mysql-config.php' );
        $configPg = include( dirname(__DIR__) . '/configs/pgsql-config.php' );
        $configPg['for'] = 'write';
        $this->dba->config( $configMy );
        $this->dba->config( $configPg );
        $pdoMy = $this->dba->connect();
        $pdoPg = $this->dba->connectWrite();

        $this->assertNotEquals( $pdoMy, $pdoPg );
        $this->assertEquals( 'mysql', $pdoMy->getAttribute( \PDO::ATTR_DRIVER_NAME ) );
        $this->assertEquals( 'pgsql', $pdoPg->getAttribute( \PDO::ATTR_DRIVER_NAME ) );
    }

    /**
     * @test
     */
    function config_using_names()
    {
        $this->dba->config( 'test', function() { return 'tested'; } );
        $this->dba->config( 'more', function() { return 'more-d'; } );
        $this->assertEquals( 'tested', $this->dba->connect( 'test' ) );
        $this->assertEquals( 'more-d', $this->dba->connect( 'more' ) );
    }
}
