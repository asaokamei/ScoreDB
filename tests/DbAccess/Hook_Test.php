<?php
namespace tests\DbAccess;

use tests\DbAccess\Dao\FilterToReturnTest;
use tests\DbAccess\Dao\User;
use WScore\ScoreDB\Hook\HookObjectInterface;
use WScore\ScoreDB\Hook\Hooks;

require_once( __DIR__ . '/../autoloader.php' );

class Hook_Test extends \PHPUnit_Framework_TestCase
{
    function test0()
    {
        $filter = new FilterToReturnTest();
        $this->assertEquals( 'tests\DbAccess\Dao\FilterToReturnTest', get_class( $filter) );
        $this->assertTrue( $filter instanceof HookObjectInterface );
    }

    /**
     * @test
     */
    function filter_sets_useFilteredData_flag_in_hook_object()
    {
        $hook = new Hooks();
        $filter = new FilterToReturnTest();
        $hook->setHook( $filter );
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
        $hook->setHook( $filter );
        $user->setHook( $hook );
        $value  = 'value:'.mt_rand(1000,9999);
        $found = $user->select( $value );
        $this->assertEquals( 'tested-'.$value, $found );
    }
}
