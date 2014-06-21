<?php
namespace tests\DbAccess;

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
        $this->dba = new DbAccess();
    }
    
    function test0()
    {
        $this->assertEquals( 'WScore\DbAccess\DbAccess', get_class( $this->dba ) );
    }
}
