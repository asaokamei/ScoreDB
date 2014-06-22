<?php
namespace tests\DbAccess;

use WScore\DbAccess\Dba;
use WScore\DbAccess\DbAccess;

require_once( __DIR__ . '/../autoloader.php' );

class DbAccess_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbAccess
     */
    var $dba;
    
    function setup()
    {
        class_exists( 'WScore\DbAccess\Dba' );
        class_exists( 'WScore\DbAccess\DbAccess' );
        $this->dba = new DbAccess();
    }
    
    function test0()
    {
        $this->assertEquals( 'WScore\DbAccess\DbAccess', get_class( $this->dba ) );
    }

    /**
     * @test
     */
    function Dba_config_and_db_returns_the_config()
    {
        Dba::config( function() {
            return 'tested';
        } );
        $this->assertEquals( 'tested', Dba::db() );
        $this->assertEquals( 'tested', Dba::dbWrite() );

        Dba::config( 'named', function() {
            return 'named-tested';
        } );
        $this->assertEquals( 'named-tested', Dba::db('named') );
        $this->assertEquals( 'named-tested', Dba::dbWrite('named') );
    }

    /**
     * @test
     */
    function Dba_reset_returns_null()
    {
        Dba::config( function() {
            return 'tested';
        } );
        $this->assertEquals( 'tested', Dba::db() );
        $this->assertEquals( 'tested', Dba::dbWrite() );

        Dba::reset();
        $this->assertEquals( null, Dba::db('named') );
        $this->assertEquals( null, Dba::dbWrite('named') );
    }
}
