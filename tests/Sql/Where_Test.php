<?php
namespace tests\Sql;

use WScore\DbAccess\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Where_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Where
     */
    var $w;

    function setup()
    {
        $this->w = new Where();
    }

    function test0()
    {
        $this->assertEquals( 'WScore\DbAccess\Sql\Where', get_class( $this->w ) );
    }

    /**
     * @test
     */
    function where_using_call()
    {
        $this->w
            ->eq->eq( 'eq' )
            ->ne->ne( 'ne' )
            ->lt->lt( 'lt' )
            ->gt->gt( 'gt' )
            ->le->le( 'le' )
            ->ge->ge( 'ge' );
        $where = $this->w->getCriteria();
        $this->assertEquals( [ 'col' => 'eq', 'val' => 'eq', 'rel' => '=',  'op' => 'AND' ], $where[ 0 ] );
        $this->assertEquals( [ 'col' => 'ne', 'val' => 'ne', 'rel' => '!=', 'op' => 'AND' ], $where[ 1 ] );
        $this->assertEquals( [ 'col' => 'lt', 'val' => 'lt', 'rel' => '<',  'op' => 'AND' ], $where[ 2 ] );
        $this->assertEquals( [ 'col' => 'gt', 'val' => 'gt', 'rel' => '>',  'op' => 'AND' ], $where[ 3 ] );
        $this->assertEquals( [ 'col' => 'le', 'val' => 'le', 'rel' => '<=', 'op' => 'AND' ], $where[ 4 ] );
        $this->assertEquals( [ 'col' => 'ge', 'val' => 'ge', 'rel' => '>=', 'op' => 'AND' ], $where[ 5 ] );
    }

    /**
     * @test
     */
    function or_makes_or()
    {
        $sql = Where::column('test')->eq('tested')->or()->more->ne('moreD')->build();
        $this->assertEquals( '( test = tested OR more != moreD )', $sql );
    }

    /**
     * @test
     */
    function and_or_and()
    {
        $this->w
            ->set(
                Where::column( 'test' )->eq( 'tested' )->more->eq( 'moreD' )
            )
            ->or()->set(
                Where::column( 'test' )->eq( 'good' )->more->eq( 'bad' )
            );
        $sql = $this->w->build();
        $this->assertEquals(
            '( ( test = tested AND more = moreD ) OR ( test = good AND more = bad ) )',
            $sql
        );
    }

    /**
     * @test
     */
    function or_and_or()
    {
        $this->w
            ->set(
                Where::column( 'test' )->eq( 'tested' )->or()->more->eq( 'moreD' )
            )
            ->set(
                Where::column( 'test' )->eq( 'good' )->or()->more->eq( 'bad' )
            );
        $sql = $this->w->build();
        $this->assertEquals(
            '( test = tested OR more = moreD ) AND ( test = good OR more = bad )',
            $sql
        );
    }
}
