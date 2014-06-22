<?php
namespace tests\DbAccess;

use tests\DbAccess\Dao\DaoClean;
use tests\DbAccess\Dao\User;
use WScore\DbAccess\Dba;

require_once( __DIR__ . '/../autoloader.php' );

class Dao_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    var $user;
    
    function setup()
    {
        Dba::reset();
        Dba::config( include(__DIR__.'/configs/mysql-config.php' ) );
        $pdo = Dba::db();
        $sql = 'DROP TABLE IF EXISTS test_WScore;';
        $pdo->query( $sql );
        $pdo->query( include(__DIR__.'/configs/mysql-create.php' ) );
        $this->user = User::forge();
    }
    
    function makeUserData( $idx=1 )
    {
        $data = [
            'name' => 'test-' . $idx ,
            'age'  => 30 + $idx,
            'gender' => 1 + $idx%2,
            'status' => 1 + $idx%3,
            'bday' => (new \DateTime('1989-01-01'))->add(new \DateInterval('P1D'))->format('Y-m-d'),
            'no_null' => 'not null test: ' . mt_rand(1000,9999),
        ];
        return $data;
    }
    
    function saveUser($count=10)
    {
        for( $i = 1; $i <= $count; $i ++ ) {
            $this->user->insert( $this->makeUserData($i) );
        }
    }
    
    function test0()
    {
        $this->assertEquals( 'tests\DbAccess\Dao\User', get_class( $this->user ) );
    }

    /**
     * @test
     */
    function DaoClean_has_table_and_keyName_set()
    {
        $dao = new DaoClean();
        $this->assertEquals( 'DaoClean',$dao->table );
        $this->assertEquals( 'DaoClean_id',$dao->keyName );
    }

    /**
     * @test
     */
    function insert_data_and_select_it()
    {
        $user = $this->makeUserData();
        $id = $this->user->insert( $user );
        $this->assertEquals( 1, $id );
        
        $found = $this->user->load( $id )[0];
        $this->assertEquals( $user['name'], $found['name'] );
        $this->assertEquals( $user['no_null'], $found['no_null'] );
    }

    /**
     * @test
     */
    function select()
    {
        $this->saveUser(10);
        $d = $this->user;
        $found = $d->where( $d->gender->eq(1) )->select();
        $this->assertEquals( 5, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['gender'] );
        }
        $found = $d->load( 1, 'status' );
        $this->assertEquals( 3, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['status'] );
        }
    }
}
