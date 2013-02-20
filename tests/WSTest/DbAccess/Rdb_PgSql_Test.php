<?php
namespace WSTest\DbAccess;
use \WScore\DbAccess\DbConnect;

require_once( __DIR__ . '/../../autoloader.php' );

class Rdb_PgSql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    /** @var \WScore\DbAccess\DbConnect */
    var $rdb;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        require_once( __DIR__ . '/../../../scripts/require.php' );
        $this->config = include( __DIR__ . '/dsn-pgsql.php' );
        $this->rdb    = new DbConnect();
    }
    // +----------------------------------------------------------------------+
    public function test_create_table_insert_and_select()
    {
        // test through all sqls.
        $pdo = $this->rdb->connect( $this->config );

        $test = "DROP TABLE IF EXISTS test;";
        $pdo->query( $test );

        $test = "CREATE TABLE test ( id int, text text );";
        $pdo->query( $test );
        
        // insert and select
        $id = 12;
        $text = 'iso8859-1 text';
        $insert = "INSERT INTO test VALUES ( $id, '{$text}' )";
        $pdo->query( $insert );
        
        $select = "SELECT * FROM test;";
        $stmt = $pdo->query( $select );
        $row = $stmt->fetch();
        
        $this->assertEquals( $id, $row['id'] );
        $this->assertEquals( $text, $row['text'] );
        
        // default is FETCH_ASSOC
        $this->assertFalse( isset( $row[0] ) );
        
        // delete 
        $pdo->query( "DELETE FROM test;" );
        
        $id = 14;
        $text = '日本語（Japanese）';
        $insert = "INSERT INTO test VALUES ( $id, '{$text}' )";
        $pdo->query( $insert );

        $select = "SELECT * FROM test;";
        $stmt = $pdo->query( $select );
        $row = $stmt->fetch();

        $this->assertEquals( $id, $row['id'] );
        $this->assertEquals( $text, $row['text'] );

        $test = "DROP TABLE test;";
        $pdo->query( $test );
    }
    /**
     * @expectedException \PDOException
     */
    public function test_bad_sql_statement()
    {
        $pdo = $this->rdb->connect( $this->config );
        $test = "CREATE TABLE test ( id int ) is a bad sql ;";
        $pdo->query( $test );
    }

    /**
     *
     */
    public function test_connection_to_WScore_db()
    {
        // should not throw any exceptions.
        $this->rdb->connect( $this->config );
    }

    /**
     *
     */
    public function test_mysql_driver_name()
    {
        $pdo = $this->rdb->connect( $this->config );
        $db  = $pdo->getAttribute( \PDO::ATTR_DRIVER_NAME );
        $this->assertEquals( 'pgsql', $db );
    }

    /**
     * @expectedException \PDOException
     */
    public function test_bad_database_connection()
    {
        $badDsn = array(
            'dsn' => 'db=noDb dbname=test username=admin password=admin'
        );
        $this->rdb->connect( $badDsn );
    }
    // +----------------------------------------------------------------------+
}