<?php
namespace tests\ScoreDB;

use tests\ScoreDB\Dao\FilterToReturnTest;
use tests\ScoreDB\Dao\User;
use WScore\ScoreDB\Hook\EventObjectInterface;
use WScore\ScoreDB\Hook\Hooks;

require_once( __DIR__ . '/../autoloader.php' );

class Hook_Test extends \PHPUnit_Framework_TestCase
{
    function test0()
    {
        $filter = new FilterToReturnTest();
        $this->assertEquals( 'tests\ScoreDB\Dao\FilterToReturnTest', get_class( $filter) );
        $this->assertTrue( $filter instanceof EventObjectInterface );
    }

    /**
     * @test
     */
    function filter_sets_useFilteredData_flag_in_hook_object()
    {
        $hook = new Hooks();
        $filter = new FilterToReturnTest();
        $hook->hookEvent( 'onTestFilter', $filter );
        $value  = 'value:'.mt_rand(1000,9999);
        $found  = $hook->hook( 'test', $value );
        $this->assertTrue( $hook->usesFilterData() );
        $this->assertEquals( 'tested-'.$value, $found );
    }

    /**
     * @test
     */
    function query_filter_()
    {
        $user = new User();
        $filter = new FilterToReturnTest();
        $hook = new Hooks();
        $hook->hookEvent( 'onSelectingFilter', $filter );
        $user->setHook( $hook );
        $value  = 'value:'.mt_rand(1000,9999);
        $found = $user->select( $value );
        $this->assertEquals( 'tested-'.$value, $found );
    }
}
