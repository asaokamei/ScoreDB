<?php
namespace tests\ScoreDB;

use tests\ScoreDB\Dao\DaoClean;
use WScore\ScoreDB\Hook\Hooks;

require_once( __DIR__ . '/../autoloader.php' );

class Dao_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function DaoClean_has_table_and_keyName_set()
    {
        $dao = DaoClean::query();
        $this->assertEquals( 'tests\ScoreDB\Dao\DaoClean', get_class( $dao ) );
        $this->assertEquals( 'DaoClean',$dao->magicGet('table') );
        $this->assertEquals( 'DaoClean_id',$dao->magicGet('keyName') );
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
