<?php
namespace WSTest\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\DbAccess\Query */
    var $query;
    /** @var Mock_QueryPdo|\WScore\DbAccess\DbAccess */
    var $pdo;
    function setUp()
    {
        require_once( __DIR__ . '/../../../scripts/require.php' );
        /** @var Mock_QueryPdo */
        $this->pdo = new Mock_QueryPdo();
        /** @var \WScore\DbAccess\Query */
        $this->query = new \WScore\DbAccess\Query( $this->pdo );
    }
    public function getValFromUpdate( $sql, $name ) {
        preg_match( "/{$name}=(:db_prep_[0-9]+)/", $sql, $matches );
        return $matches[1];
    }
    public function checkUpdateContainsVal( $sql, $name, $values, $prepared ) {
        $prep1 = $this->getValFromUpdate( $sql, $name );
        $val1  = $prepared[ $prep1 ];
        $this->assertEquals( $values[ $name ], $val1 );
    }
    public function getSqlFromQuery( $query ) {
        return \WScore\DbAccess\SqlBuilder::build( $query );
    }
    // +----------------------------------------------------------------------+
    public function test_select_with_many_option()
    {
        $table = 'testTable';
        $this->query->table( $table )->select();
        $this->assertEquals( $table, $this->pdo->query->table );
        $this->assertEquals( 'select', $this->pdo->query->queryType );
    }
    public function test_select_with_order()
    {
        $table = 'testTable';
        $this->query->table( $table )->order( 'test order' )->select();
        $this->assertEquals( $table, $this->pdo->query->table );
        $this->assertEquals( 'test order', $this->pdo->query->order );
        $this->assertEquals( 'select', $this->pdo->query->queryType );
    }
    public function test_select_with_group()
    {
        $table = 'testTable';
        $this->query->table( $table )->group( 'test group' )->select();
        $this->assertEquals( $table, $this->pdo->query->table );
        $this->assertEquals( 'test group', $this->pdo->query->group );
        $this->assertEquals( 'select', $this->pdo->query->queryType );
    }
    public function test_select_with_misc()
    {
        $table = 'testTable';
        $this->query->table( $table )->misc( 'test misc' )->select();
        $this->assertEquals( 'test misc', $this->pdo->query->misc );
    }
    public function test_select_with_limit()
    {
        $table = 'testTable';
        $this->query->table( $table )->limit(10)->select();
        $this->assertEquals( "10", $this->pdo->query->limit );
    }
    public function test_select_with_offset()
    {
        $table = 'testTable';
        $this->query->table( $table )->offset(5)->select();
        $this->assertEquals( "5", $this->pdo->query->offset );
    }
    // +----------------------------------------------------------------------+
    function test_where_with_get() 
    {
        $table = 'testTable';
        $this->query->table( $table )->abc->like( '%val%' )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( ":db_prep_1", $this->pdo->query->where[0]['val'] );
        $this->assertEquals( "LIKE", $this->pdo->query->where[0]['rel'] );
        $this->assertEquals( "%val%", $this->pdo->query->prepared_values[':db_prep_1'] );
        // add one more where clause
        $this->query->xyz->like( '%string%' )->select();
        $this->assertEquals( "xyz", $this->pdo->query->where[1]['col'] );
        $this->assertEquals( ":db_prep_2", $this->pdo->query->where[1]['val'] );
        $this->assertEquals( "LIKE", $this->pdo->query->where[1]['rel'] );
        $this->assertEquals( "%string%", $this->pdo->query->prepared_values[':db_prep_2'] );
    }
    public function test_where_w_and_like()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'abc' )->like( '%val%' )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( ":db_prep_1", $this->pdo->query->where[0]['val'] );
        $this->assertEquals( "LIKE", $this->pdo->query->where[0]['rel'] );
        $this->assertEquals( "%val%", $this->pdo->query->prepared_values[':db_prep_1'] );
        // add one more where clause
        $this->query->col( 'xyz' )->like( '%string%' )->select();
        $this->assertEquals( "xyz", $this->pdo->query->where[1]['col'] );
        $this->assertEquals( ":db_prep_2", $this->pdo->query->where[1]['val'] );
        $this->assertEquals( "LIKE", $this->pdo->query->where[1]['rel'] );
        $this->assertEquals( "%string%", $this->pdo->query->prepared_values[':db_prep_2'] );
    }
    public function test_where_w_and_notNull()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'abc' )->notNull( array( 'b','c' ) )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( "IS NOT NULL", $this->pdo->query->where[0]['rel'] );
    }
    public function test_where_w_and_isNull()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'abc' )->isNull( array('b','c') )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( "IS NULL", $this->pdo->query->where[0]['rel'] );
    }
    public function test_where_w_and_notIn()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table );
        // add one more where clause
        $this->query->col( 'abc' )->notIn( array( 'y', 'z' ) )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( ":db_prep_1", $this->pdo->query->where[0]['val'][0] );
        $this->assertEquals( "NOT IN", $this->pdo->query->where[0]['rel'] );
        $this->assertEquals( "y", $this->pdo->query->prepared_values[':db_prep_1'] );
    }
    public function test_where_w_and_in()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'abc' )->in( array('x','y') )->select();
        $this->assertEquals( "abc", $this->pdo->query->where[0]['col'] );
        $this->assertEquals( ":db_prep_1", $this->pdo->query->where[0]['val'][0] );
        $this->assertEquals( "IN", $this->pdo->query->where[0]['rel'] );
        $this->assertEquals( "x", $this->pdo->query->prepared_values[':db_prep_1'] );
        $this->assertEquals( "y", $this->pdo->query->prepared_values[':db_prep_2'] );
    }
    public function test_where_w_and_ge()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->ge( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a >= :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->ge( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a >= :db_prep_1 AND x >= :db_prep_2", $sql );
    }
    public function test_where_w_and_gt()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->gt( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a > :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->gt( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a > :db_prep_1 AND x > :db_prep_2", $sql );
    }
    public function test_where_w_and_le()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->le( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a <= :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->le( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a <= :db_prep_1 AND x <= :db_prep_2", $sql );
    }
    public function test_where_w_and_lt()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->lt( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a < :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->lt( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a < :db_prep_1 AND x < :db_prep_2", $sql );
    }
    public function test_where_w_and_ne()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->ne( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a != :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->ne( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a != :db_prep_1 AND x != :db_prep_2", $sql );
    }
    public function test_where_clause_where()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->where( 'a', 'b', 'c' )->makeSelect()->exec();
        $select = "SELECT * FROM {$table} WHERE a C :db_prep_1";
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( $select, $sql );
        // add one more where clause
        $this->query->where( 'x', 'y', 'z' )->makeSelect()->exec();
        $select .= " AND x Z :db_prep_2";
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( $select, $sql );
        // add one more whereRaw clause. should be as is.
        $this->query->whereRaw( '1', '2', '3' )->makeSelect()->exec();
        $select .= " AND 1 3 2";
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( $select, $sql );
    }
    public function test_where_w_and_eq()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->col( 'a' )->eq( 'b' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a = :db_prep_1", $sql );
        // add one more where clause
        $this->query->col( 'x' )->eq( 'z' )->select();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} WHERE a = :db_prep_1 AND x = :db_prep_2", $sql );
    }
    public function test_values_null_and_empty_string()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'colNull' => null, 'colZero' => '' );
        $this->query->table( $table )->values( $values )->makeUpdate()->exec();

        // check SQL statement
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( "UPDATE {$table} SET ", $sql );
        // check mock PDO
        $this->assertContains( 'colNull=NULL', $sql );
        $this->assertContains( 'col1=:db_prep_', $sql );
        $this->assertContains( 'colZero=:db_prep_', $sql );
    }
    // +----------------------------------------------------------------------+
    /**
     * This test no longer valid. Query does not build sql anymore.
     * the mock class does not build sql.
     *
     * @ expectedException \RuntimeException
     */
    public function test_simple_delete_no_where()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->makeDelete()->exec();
    }
    public function test_simple_delete()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->col( 'id' )->eq(10)->makeDelete()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( $table, $sql );
        $this->assertEquals( "DELETE FROM {$table} WHERE id = :db_prep_1", $sql );
    }
    public function test_simple_update_statement()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->table( $table )->values( $values )->makeUpdate()->exec();

        // check SQL statement
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( "UPDATE {$table} SET ", $sql );
        $this->assertContains( "col1=:db_prep_1", $sql );
        $this->assertContains( "col2=:db_prep_2", $sql );
    }
    public function test_simple_update_statement2()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->table( $table )->update( $values );

        // check SQL statement
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( "UPDATE {$table} SET ", $sql );
        $this->assertContains( "col1=:db_prep_1", $sql );
        $this->assertContains( "col2=:db_prep_2", $sql );
    }
    public function test_simple_insert_statement()
    {
        $table = 'testTable';
        $this->query->table( $table );
        // check INSERT
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->values( $values )->makeInsert()->exec();

        // check SQL statement
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $sql );
        foreach( $this->pdo->query->prepared_values as $key => $val ) {
            $this->assertContains( $key, $sql );
            $this->assertContains( $val, $values );
        }
    }
    public function test_simple_insert_statement2()
    {
        $table = 'testTable';
        $this->query->table( $table );
        // check INSERT
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->insert( $values );

        // check SQL statement
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $sql );
        foreach( $this->pdo->query->prepared_values as $key => $val ) {
            $this->assertContains( $key, $sql );
            $this->assertContains( $val, $values );
        }
    }
    // +----------------------------------------------------------------------+
    public function test_make_simple_count_statement()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->makeCount()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( $table, $sql );
        $this->assertEquals( "SELECT COUNT(*) AS WScore__Count__ FROM {$table}", $sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeCount()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT COUNT(*) AS WScore__Count__ FROM {$table}", $sql );
    }
    public function test_make_simple_select_statement()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( $table, $sql );
        $this->assertEquals( "SELECT * FROM {$table}", $sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT colA FROM {$table}", $sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT colX FROM {$table}", $sql );
    }
    public function test_select_for_update()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->forUpdate()->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( $table, $sql );
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT * FROM {$table} FOR UPDATE", $sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT colA FROM {$table} FOR UPDATE", $sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT colX FROM {$table} FOR UPDATE", $sql );
    }
    public function test_select_distinct()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->distinct()->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertContains( $table, $sql );
        $this->assertEquals( "SELECT DISTINCT * FROM {$table}", $sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT DISTINCT colA FROM {$table}", $sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $sql = $this->getSqlFromQuery( $this->pdo->query );
        $this->assertEquals( "SELECT DISTINCT colX FROM {$table}", $sql );
    }
    // +----------------------------------------------------------------------+
}