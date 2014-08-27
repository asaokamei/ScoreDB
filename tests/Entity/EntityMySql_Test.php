<?php
namespace tests\Entity;

require_once( __DIR__ . '/../autoloader.php' );

use tests\Entity\Dao\UserDao;
use WScore\ScoreDB\DB;

class EntityMySql_Test extends \PHPUnit_Framework_TestCase
{
    static function setupBeforeClass()
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        class_exists( 'WScore\ScoreDB\Hooks\Hooks' );
        class_exists( 'tests\Entity\Dao\User' );
        class_exists( 'tests\Entity\Dao\UserDao' );
        DB::restart();
    }

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

    function xtest0()
    {
        $q = UserDao::query();
        $this->assertEquals( 'tests\Entity\Dao\UserDao', get_class($q) );
    }

    /**
     * @test
     */
    function insert_user_data_and_retrieve_as_entityObject()
    {
        // save data, first.
        $data = makeUserData_for_test();
        $idx  = UserDao::query()->insert($data);
        $this->assertEquals( '1', $idx );

        // fetch the data from db.
        $users = UserDao::query()->load($idx);
        $this->assertEquals( 'PDOStatement', get_class($users) );

        // test the basic access of the Entity object.
        $user = $users->fetch();
        $this->assertEquals( 'WScore\ScoreDB\Entity\ActiveRecord', get_class($user) );
        $this->assertEquals( $data['no_null'], $user->no_null );
        $this->assertEquals( $idx, $user->getKey() );
        $this->assertEquals( true, $user->isFetched() );

        // modify some value.
        $user->no_null = $no_null = 'test-active-record';
        $this->assertEquals( $no_null, $user->no_null );
        // set new property, and remove it.
        $user->testEO = 'test-entity-object';
        $this->assertNotNull( $user->testEO );
        unset( $user->testEO );
        $this->assertNull( $user->testEO );

        // save it. test ActiveRecord.
        $user->save();
    }

}
