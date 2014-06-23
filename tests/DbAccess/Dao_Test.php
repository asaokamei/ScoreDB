<?php
namespace tests\DbAccess;

use tests\DbAccess\Dao\DaoClean;
use WScore\DbAccess\Hooks;

require_once( __DIR__ . '/../autoloader.php' );

class Dao_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function DaoClean_has_table_and_keyName_set()
    {
        $dao = new DaoClean();
        $this->assertEquals( 'tests\DbAccess\Dao\DaoClean', get_class( $dao ) );
        $this->assertEquals( 'DaoClean',$dao->table );
        $this->assertEquals( 'DaoClean_id',$dao->keyName );
    }

    /**
     * @test
     */
    function Hook_onDaoClean_fires_events()
    {
        $dao = new DaoClean();
        $hook = new Hooks();
        $hook->setHook( $dao );

        $this->assertEquals( false, $dao->tested );
        $this->assertEquals( false, $dao->filtered );

        $hook->hook( 'test' );
        $this->assertEquals( true, $dao->tested );
        $this->assertEquals( false, $dao->filtered );

        $hook->hook( 'more' );
        $this->assertEquals( true, $dao->tested );
        $this->assertEquals( true, $dao->filtered );
    }

}
