<?php
namespace WSTest\DbAccess;

use \WScore\DbAccess\DbAccess;
require_once( __DIR__ . '/../../autoloader.php' );

class DbAccess_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    
    /** @var \WScore\DbAccess\DbAccess */
    var $pdo = null;
    
    var $table = 'test_WScore';
    
    var $column_list = '';
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        require_once( __DIR__ . '/../../../scripts/require.php' );
        $this->config = include( __DIR__ . '/dsn-mysql.php' );
        $this->pdo = include( __DIR__ . '/../../../scripts/dbaccess.php' );
        $this->pdo->connect( $this->config );
        $this->column_list = '
            id int NOT NULL AUTO_INCREMENT,
            name CHAR(30),
            age  int,
            bdate date,
            no_null text NOT NULL,
            PRIMARY KEY (id)
        ';
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
        $this->pdo->execSql( "DROP TABLE IF EXISTS {$this->table};" );
        $this->pdo->execSql( "
        CREATE TABLE {$this->table} ( {$this->column_list} );
        " );
    }

    public function fill_columns( $max=10 )
    {
        $prepare = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $this->pdo->execPrepare( $prepare );
        for( $i = 0; $i < $max; $i ++ ) {
            $values = $this->get_column_by_row( $i );
            $this->pdo->execExecute( $values );
        }
    }

    public function get_column_by_row( $row )
    {
        $date = new \DateTime( '1980-05-01' );
        $date = $date->add( new \DateInterval( "P{$row}D" ) );
        $values = array(
            ':name' => 'filed #' . $row,
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
    public function test_connected_injection()
    {
        // build with config into DbConnect in prior.
        $dbc = new \WScore\DbAccess\DbConnect( $this->config );
        $dba = new \WScore\DbAccess\DbAccess();
        $dba->sqlBuilder = new \WScore\DbAccess\SqlBuilder();
        $dba->connect( $dbc );

        $max = 1;
        $arg = new Mock_PdObjectData();
        $class = 'WSTest\DbAccess\Mock_PdObjectDao';
        $this->fill_columns( $max );
        $dba->setFetchMode( \PDO::FETCH_CLASS, $class, array( $arg ) );
        /** @var $ret \PdoStatement */
        $ret = $dba->execSql( "SELECT * FROM {$this->table};" );

        $fetched = $ret->fetch();
        $this->assertTrue( is_object( $fetched ) );
        $this->assertEquals( $class, get_class( $fetched ) );
        $this->assertEquals( $arg, $fetched->getConstructed() );
        $this->assertSame( $arg, $fetched->getConstructed() );
    }

    public function test_fetch_data_record_class()
    {
        $max = 1;
        $arg = new Mock_PdObjectData();
        $class = 'WSTest\DbAccess\Mock_PdObjectDao';
        $this->fill_columns( $max );
        $this->pdo->setFetchMode( \PDO::FETCH_CLASS, $class, array( $arg ) );
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( "SELECT * FROM {$this->table};" );

        $fetched = $ret->fetch();
        $this->assertTrue( is_object( $fetched ) );
        $this->assertEquals( $class, get_class( $fetched ) );
        $this->assertEquals( $arg, $fetched->getConstructed() );
        $this->assertSame( $arg, $fetched->getConstructed() );
    }
    public function test_fetch_mode_class()
    {
        $max = 1;
        $class = 'WSTest\DbAccess\Mock_PdObjectData';
        $this->fill_columns( $max );
        $this->pdo->setFetchMode( \PDO::FETCH_CLASS, $class );
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( "SELECT * FROM {$this->table};" );
        
        $fetched = $ret->fetch();
        $this->assertTrue( is_object( $fetched ) );
        $this->assertEquals( $class, get_class( $fetched ) );
        
        $rowData = $this->get_column_by_row(0);
        foreach( $rowData as $col => $val ) {
            $name = substr( $col, 1 );
            $this->assertEquals( $fetched->$name, $rowData[$col] );
        }
    }
    public function test_fetch_object()
    {
        $max = 1;
        $this->fill_columns( $max );
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( "SELECT * FROM {$this->table};" );

        $fetched = $ret->fetch( \PDO::FETCH_OBJ );
        $this->assertTrue( is_object( $fetched ) );

        $rowData = $this->get_column_by_row(0);
        foreach( $rowData as $col => $val ) {
            $name = substr( $col, 1 );
            $this->assertEquals( $fetched->$name, $rowData[$col] );
        }
    }
    public function test_fetch_returns_data_one_by_one()
    {
        $max = 4;
        $this->fill_columns( $max );

        // get all data
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        for( $row = 0; $row < $max; $row ++ ) {
            $fetched = $ret->fetch();
            $rowData = $this->get_column_by_row($row);
            foreach( $columns as $colName ) {
                $this->assertEquals( $fetched[$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_fetchAll_returns_all_data()
    {
        $max = 4;
        $this->fill_columns( $max );

        // get all data
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $allData = $ret->fetchAll();
        $numRows = count( $allData );
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            foreach( $columns as $colName ) {
                $this->assertEquals( $allData[$row][$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_prepare_lastId_and_select()
    {
        $prepare = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $values = array(
            ':name' => 'test prepare',
            ':age' => '41',
            ':bdate' => '1980-02-03',
            ':no_null' => 'never null',
        );
        $this->pdo->execSql( $prepare, $values );
        $id1 = $this->pdo->lastId();
        $this->assertTrue( $id1 > 0 );
        
        $select = "SELECT * FROM {$this->table} WHERE id='{$id1}'";
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( $select );
        $result = $ret->fetch();
        foreach( $values as $key => $val ) {
            $key = substr( $key, 1 );
            $this->assertEquals( $val, $result[ $key ] );
        }
    }
    public function test_insert_lastId_and_select_data()
    {
        $data = $this->get_column_by_row( 1 );
        $insert = "INSERT {$this->table} ( name, age, bdate, no_null ) VALUES (
            '{$data{':name'}}', '{$data{':age'}}', '{$data{':bdate'}}', '{$data{':no_null'}}'
        )";
        $this->pdo->execSql( $insert );
        $id = $this->pdo->lastId();
        $this->assertEquals( '1', $id );
        
        $select = "SELECT * FROM {$this->table} WHERE id='{$id}'";
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( $select );
        $result = $ret->fetch();
        $data[ ':id' ] = $id;
        foreach( $data as $key => $val ) {
            $key = substr( $key, 1 );
            $this->assertEquals( $val, $result[ $key ] );
        }
    }
    function test_quote()
    {
        $data   = 'test';
        $quoted = $this->pdo->quote( $data );
        $this->assertEquals( "'" . addslashes( $data ) . "'", $quoted );
    }
    function test_quote_with_quote()
    {
        $data   = 'tests\' more';
        $quoted = $this->pdo->quote( $data );
        $this->assertEquals( "'" . addslashes( $data ) . "'", $quoted );
    }
    function test_quote_with_array()
    {
        $data   = array(
            'test',
            'tests\' more',
        );
        $quoted = $this->pdo->quote( $data );
        $this->assertEquals( "'" . addslashes( $data[0] ) . "'", $quoted[0] );
        $this->assertEquals( "'" . addslashes( $data[1] ) . "'", $quoted[1] );
    }
    public function test_prepare_with_data_type()
    {
        // todo: not sure what to test. at least it worked without error/exception.
        $prepare = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $values = array(
            ':name' => 'test prepare',
            ':age' => '41',
            ':bdate' => '1980-02-03',
            ':no_null' => 'never null',
        );
        $types = array(
            ':name' => \PDO::PARAM_STR,
            ':age' => \PDO::PARAM_INT,
            ':no_null' => \PDO::PARAM_STR,
        );
        $this->pdo->execSql( $prepare, $values, $types );
        $id1 = $this->pdo->lastId();
        $this->assertTrue( $id1 > 0 );

        $select = "SELECT * FROM {$this->table} WHERE id='{$id1}'";
        /** @var $ret \PdoStatement */
        $ret = $this->pdo->execSql( $select );
        $result = $ret->fetch();
        foreach( $values as $key => $val ) {
            $key = substr( $key, 1 );
            $this->assertEquals( $val, $result[ $key ] );
        }
    }
    // +----------------------------------------------------------------------+
}