<?php
namespace WSTest\DbAccess;

use \WScore\DbAccess\DbConnect;

/*
 * TODO: RDB written, i.e. rewrite this test as well. 
 */
require_once( __DIR__ . '/../../autoloader.php' );

class DbConnect_Test extends \PHPUnit_Framework_TestCase
{
    var $mockPdo;
    /** @var \WScore\DbAccess\DbConnect */
    var $rdb;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        require_once( __DIR__ . '/../../../scripts/require.php' );
        $this->mockPdo = '\WSTest\DbAccess\Mock_RdbPdo';
        $this->rdb     = new DbConnect();
        \WScore\DbAccess\DbConnect::$pdoClass = $this->mockPdo;
    }

    public static function tearDownAfterClass() {
        \WScore\DbAccess\DbConnect::$pdoClass = '\PDO';
    }
    // +----------------------------------------------------------------------+
    public function test_connect_with_new()
    {
        $name1 = "dsn=db:Test;dbname=test1";
        $dsn   = "db:Test;dbname=test1";
        /** @var $pdo1 \WSTest\DbAccess\Mock_RdbPdo */
        $pdo1 = $this->rdb->connect( $name1 );
        $this->assertEquals( $dsn, $pdo1->config[0] );
    }
    /**
     * @expectedException \RuntimeException
     */
    public function test_name_not_set()
    {
        $this->rdb->connect( null );
    }
    // +----------------------------------------------------------------------+
    public function test_config_with_dsn()
    {
        $dsn  = array(
            'dsn'  => 'db=myTest dbname=my_test',
            'exec' => 'SET NAMES UTF8',
            'username' => 'test_user',
            'password' => 'testPswd',
        );
        /** @var $pdo \WSTest\DbAccess\Mock_RdbPdo */
        $pdo = $this->rdb->connect( $dsn );

        $this->assertEquals( $dsn['dsn'], $pdo->config[0] );
        $this->assertEquals( 'test_user', $pdo->config[1] );
        $this->assertEquals( 'testPswd', $pdo->config[2] );
        $this->assertEquals( 'SET NAMES UTF8', $pdo->exec );
    }
    // +----------------------------------------------------------------------+
    public function test_construct_config()
    {
        $dsn  = 'dsn=db:myTest;dbname:my_test';
        /** @var $pdo \WSTest\DbAccess\Mock_RdbPdo */
        $pdo = $this->rdb->connect( $dsn );

        $this->assertEquals( 'db:myTest;dbname:my_test', $pdo->config[0] );

    }
}

