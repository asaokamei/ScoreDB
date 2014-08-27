<?php
namespace tests\Entity;

require_once( __DIR__ . '/../autoloader.php' );

use tests\Entity\Dao\UserDao;
use WScore\ScoreDB\DB;

class EntityMySql_Test extends \PHPUnit_Framework_TestCase
{
    function setup()
    {
        $this->prepareTest('mysql');
    }

    function teardown()
    {
        DB::restart();
    }

    function prepareTest( $dbType )
    {
        DB::restart();
        /** @noinspection PhpIncludeInspection */
        DB::config( include( dirname(__DIR__) . "/configs/{$dbType}-config.php" ) );
        $pdo = DB::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        /** @noinspection PhpIncludeInspection */
        $pdo->query( include( dirname(__DIR__) . "/configs/{$dbType}-create.php" ) );
    }

    function test0()
    {
        $q = UserDao::query();
        $this->assertEquals( 'tests\Entity\Dao\UserDao', get_class($q) );
    }

    /**
     * @test
     */
    function insert_user_data_and_retrieve_as_entityObject()
    {
        $data = makeUserData_for_test();
        $idx  = UserDao::query()->insert($data);
        $this->assertEquals( '1', $idx );

        $user = UserDao::query()->load($idx);
        //$this->assertEquals( 'tests\Entity\Dao\UserDao', get_class($user) );
    }
}
