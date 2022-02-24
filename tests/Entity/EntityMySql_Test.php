<?php
namespace tests\Entity;

require_once( __DIR__ . '/../autoloader.php' );

use tests\Entity\Dao\UserDao;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Entity\ActiveRecord;

class EntityMySql_Test extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass() : void
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        class_exists( 'WScore\ScoreDB\Hooks\Hooks' );
        class_exists( 'tests\Entity\Dao\User' );
        class_exists( 'tests\Entity\Dao\UserDao' );
        DB::restart();
    }

    protected function setUp() : void
    {
        $this->prepareTest('mysql');
    }

    protected function teardown(): void
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
        $this->assertTrue( is_array($users) );

        // test the basic access of the Entity object.
        /** @var ActiveRecord $user */
        $user = $users[0];
        $this->assertEquals( 'WScore\ScoreDB\Entity\ActiveRecord', get_class($user) );
        $this->assertEquals( $data['no_null'], $user->no_null );
        $this->assertEquals( $idx, $user->getKey() );
        $this->assertEquals( true, $user->isFetched() );

        // check dates
        /** @var \DateTime $bday1 */
        $bday1 = $user->bday;
        $this->assertEquals( 'DateTime', get_class($bday1) );
        $bday2 = clone($bday1);
        $bday2->add( new \DateInterval('P3D') );
        $user->bday = $bday2;
        $bdayS = $user->_getRaw('bday');
        $this->assertTrue( is_string($bdayS) );
        $this->assertEquals( $bdayS, $bday2->format('Y-m-d H:i:s') );

        // check mutation.
        $status  = $user->_getRaw('status');
        $status1 = $user->status;
        $this->assertEquals( 'Status-'.$status, $status1 );
        $user->status = 5;
        $this->assertEquals( 'Status-5', $user->status );

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
