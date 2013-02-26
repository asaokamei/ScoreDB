<?php
namespace WSTest\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

/*
 * TODO: more test on Query. and check the overall design as well.
 */

class Query_PgSql_Test extends \PHPUnit_Framework_TestCase
{
    static $queryObject;
    var $config = array();
    /** @var \WScore\DbAccess\Query */
    var $query = NULL;
    var $table = 'test_query';
    var $table2= 'test_WScore2';
    // +----------------------------------------------------------------------+
    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        /** @var \WScore\DbAccess\Query */
        require_once( __DIR__ . '/../../../scripts/require.php' );
        self::$queryObject = include( __DIR__ . '/../../../scripts/query.php' );
    }
    public function setUp()
    {
        $this->config = include( __DIR__ . '/dsn-pgsql.php' );
        /** @var \WScore\DbAccess\Query */
        $this->query = self::$queryObject;
        $this->query->connect( $this->config );
        $this->setUp_TestTable();
    }

    /**
     * set up permanent tables for testing.
     * use this if you are testing the tests!
     */
    public function setUp_TestTable_perm()
    {
        $this->table = 'test_WScorePerm';
        $this->setUp_TestTable();
    }

    /**
     * creates new table for testing.
     */
    public function setUp_TestTable()
    {
        $this->query->execSQL( "DROP TABLE IF EXISTS {$this->table};" );
        $this->query->execSQL( "
        CREATE TABLE {$this->table} (
            id SERIAL,
            name VARCHAR(30),
            age  int,
            bdate date,
            no_null text NOT NULL,
            PRIMARY KEY (id)
        );
        " );
        $this->query->execSQL( "DROP TABLE IF EXISTS {$this->table2};" );
        $this->query->execSQL( "
        CREATE TABLE {$this->table2} (
            id SERIAL,
            user_id int,
            contact VARCHAR(30),
            PRIMARY KEY (id)
        );
        " );
    }

    public function getPrepare() {
        return "
            INSERT INTO {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
    }
    public function getPrepare2() {
        return "
            INSERT INTO {$this->table2}
                ( user_id, contact )
            VALUES
                ( :user_id, :contact );
        ";
    }

    public function fill_columns( $max=10 )
    {
        $this->query->execPrepare( $this->getPrepare() );
        for( $i = 0; $i < $max; $i ++ ) {
            $values = $this->get_column_by_row( $i );
            $this->query->execExecute( $values );
        }
    }

    public function get_column_by_row( $row )
    {
        $date = new \DateTime( '1980-05-01' );
        $date = $date->add( new \DateInterval( "P{$row}D" ) );
        $values = array(
            ':name' => 'filed\'s #' . $row,
            ':age' => 40 + $row,
            ':bdate' => $date->format( 'Y-m-d' ),
            ':no_null' => 'never null'.($row+1),
        );
        return $values;
    }
    public function get_value_by_row( $row )
    {
        $column = $this->get_column_by_row( $row );
        $values = array();
        foreach( $column as $key => $val ) {
            $values[ substr( $key, 1 ) ] = $val;
        }
        return $values;
    }
    // +----------------------------------------------------------------------+
    public function test_insert_data()
    {
        $data = $this->get_value_by_row( 21 );

        // add some data
        $return = $this->query->table( $this->table )->insert( $data );
        $this->assertEquals( 'WScore\DbAccess\Query', get_class( $return ) );
        // last ID should be 1, since it is the first data.
        $id = $this->query->lastId();
        $this->assertEquals( '1', $id );

        // now check to see really added
        $return2 = $this->query->table( $this->table )
            ->where( 'id', $id )->select()->fetchAll();
        $this->assertTrue( is_array( $return2 ) );
    }
    public function test_driver_name()
    {
        $driver = $this->query->getDriverName();
        $this->assertEquals( 'pgsql', $driver );
    }
    public function test_fetchRow()
    {
        $max = 12;
        $this->fill_columns( $max );

        // get all data
        $this->query->table( $this->table )->select();

        // check fetchNumRow
        $numRows = $this->query->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            $fetched = $this->query->fetchRow();
            foreach( $columns as $colName ) {
                $this->assertEquals( $fetched[$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_fetchAll()
    {
        $max = 12;
        $this->fill_columns( $max );

        // get all data
        $this->query->table( $this->table )->select();

        // check fetchNumRow
        $numRows = $this->query->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        $allData = $this->query->fetchAll();
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            foreach( $columns as $colName ) {
                $this->assertEquals( $allData[$row][$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_insert_with_last_id()
    {
        $insert = array(
            'name' => 'test query',
            'age' => '40',
            'bdate' => '1990-01-02',
            'no_null' => 'not null',
        );
        $this->query->table( $this->table )->insert( $insert );
        $id1 = $this->query->lastId();
        $this->assertTrue( $id1 > 0 );

        $this->query->table( $this->table )->insert( $insert );
        $id2 = $this->query->lastId();
        $this->assertNotEquals( $id2, $id1 );
        $this->assertEquals( $id2, $id1 + 1 );
    }
    public function test_join_table()
    {
        $this->fill_columns( 3 );
        $this->query->dbAccess()->execSql( $this->getPrepare2(), array( 'user_id'=>'1', 'contact'=>'contact #1' ) );
        $this->query->dbAccess()->execSql( $this->getPrepare2(), array( 'user_id'=>'1', 'contact'=>'contact #2' ) );
        $this->query->dbAccess()->execSql( $this->getPrepare2(), array( 'user_id'=>'2', 'contact'=>'contact #3' ) );

        $data = $this->query->table( $this->table )->select()->fetchAll();
        $this->assertEquals( 3, count( $data ) );
        $this->assertArrayNotHasKey( 'contact', $data[0] );

        $data = $this->query->table( $this->table )->join( $this->table2, 'JOIN', 'ON', $this->table.'.id=' . $this->table2.'.user_id' )->select()->fetchAll();
        $this->assertEquals( 3, count( $data ) );
        $this->assertArrayHasKey( 'contact', $data[0] );

        $data = $this->query->table( $this->table )->join( $this->table2, 'LEFT JOIN', 'ON', $this->table.'.id=' . $this->table2.'.user_id' )->select()->fetchAll();
        $this->assertEquals( 4, count( $data ) );
        $this->assertArrayHasKey( 'contact', $data[0] );
    }

    // +----------------------------------------------------------------------+
}
