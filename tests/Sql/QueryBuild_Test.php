<?php
namespace tests\Sql;

use WScore\DbAccess\Sql\Bind;
use WScore\DbAccess\Sql\Builder;
use WScore\DbAccess\Sql\Query;
use WScore\DbAccess\Sql\Quote;
use WScore\DbAccess\Sql\Where;

require_once( dirname(__DIR__).'/autoloader.php');

class QueryBuild_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bind
     */
    var $b;

    /**
     * @var Quote
     */
    var $q;

    /**
     * @var Builder
     */
    var $builder;

    /**
     * @var Query
     */
    var $query;
    
    function setup()
    {
        $this->b = new Bind();
        $this->q = new Quote();
        $this->builder = new Builder( $this->b, $this->q );
        $this->query   = new Query( new Where( $this->q ), $this->b );
        Bind::reset();
    }
    
    function get($head='value') {
        return $head . mt_rand(1000,9999);
    }
    
    function test0()
    {
        $this->assertEquals( 'WScore\DbAccess\Sql\Bind', get_class( $this->b ) );
        $this->assertEquals( 'WScore\DbAccess\Sql\Quote', get_class( $this->q ) );
        $this->assertEquals( 'WScore\DbAccess\Sql\Builder', get_class( $this->builder ) );
        $this->assertEquals( 'WScore\DbAccess\Sql\Query', get_class( $this->query ) );
    }

    /**
     * @test
     */
    function insert()
    {
        $value = $this->get();
        $this->query->table( 'testTable' )->value( 'testCol', $value );
        $sql = $this->builder->toInsert( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals( 'INSERT INTO "testTable" ( "testCol" ) VALUES ( :db_prep_1 )', $sql );
        $this->assertEquals( $value, $bind[':db_prep_1'] );
    }

    /**
     * @test
     */
    function update()
    {
        $values = [
            'testCol' => $this->get(),
            'moreCol' => $this->get(),
        ];
        $keyVal = $this->get();
        $this->query->table( 'testTable' )->value( $values )->where()->pKey->eq($keyVal);
        $sql = $this->builder->toUpdate( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals(
            'UPDATE "testTable" SET "testCol"=:db_prep_1, "moreCol"=:db_prep_2 WHERE "pKey" = :db_prep_3',
            $sql );
        $this->assertEquals( $keyVal, $bind[':db_prep_3'] );
        $this->assertEquals( $values['testCol'], $bind[':db_prep_1'] );
        $this->assertEquals( $values['moreCol'], $bind[':db_prep_2'] );
    }

    /**
     * @test
     */
    function select()
    {
        $this->query
            ->table( 'testTable' )
            ->column( 'colTest', 'aliasAs' )
            ->where()->col('"my table".name')->like( 'bob' )->q()
            ->order( 'pKey' );
        $sql = $this->builder->toSelect( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals(
            'SELECT "colTest" AS "aliasAs" FROM "testTable" ' .
            'WHERE "my table"."name" LIKE :db_prep_1 ORDER BY "pKey" ASC',
            $sql );
        $this->assertEquals( 'bob', $bind[':db_prep_1'] );
    }

    /**
     * @test
     */
    function select_in()
    {
        $in = [
            $this->get(),
            $this->get(),
        ];
        $this->query
            ->table( 'testTable' )
            ->where()->name->contain( 'bob' )->status->in($in)->q()
            ->order( 'pKey' );
        $sql = $this->builder->toSelect( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals(
            'SELECT * FROM "testTable" ' .
            'WHERE "name" LIKE :db_prep_1 AND "status" IN ( :db_prep_2, :db_prep_3 ) ' .
            'ORDER BY "pKey" ASC',
            $sql );
        $this->assertEquals( '%bob%', $bind[':db_prep_1'] );
    }


    /**
     * @test
     */
    function select_between()
    {
        $this->query
            ->table( 'testTable' )
            ->where()->value->between(123,345)->q()
            ->order( 'pKey' );
        $sql = $this->builder->toSelect( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals(
            'SELECT * FROM "testTable" ' .
            'WHERE "value" BETWEEN :db_prep_1 AND :db_prep_2 ' .
            'ORDER BY "pKey" ASC',
            $sql );
        $this->assertEquals( '123', $bind[':db_prep_1'] );
        $this->assertEquals( '345', $bind[':db_prep_2'] );
    }

    /**
     * @test
     */
    function select_2()
    {
        $this->query
            ->table( 'testTable' )
            ->alias( 'aliasTable' )
            ->forUpdate()
            ->distinct()
            ->column( 'colTest', 'aliasAs' )
            ->where()->name->contain( 'bob' )->q()
            ->group( 'grouped' )
            ->order( 'pKey' )
            ->limit(5)
            ->offset(10);
        $sql = $this->builder->toSelect( $this->query );
        $bind = $this->b->getBinding();
        $this->assertEquals(
            'SELECT FOR UPDATE DISTINCT "colTest" AS "aliasAs" ' .
            'FROM "testTable" "aliasTable" WHERE "name" LIKE :db_prep_1 ' .
            'GROUP BY "grouped" ORDER BY "pKey" ASC',
            $sql );
        $this->assertEquals( '%bob%', $bind[':db_prep_1'] );
    }
}
