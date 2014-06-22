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
        
        // check if the data is loaded.
        $found = $this->user->load( $id )[0];
        $this->assertEquals( $user['name'], $found['name'] );
        $this->assertEquals( $user['no_null'], $found['no_null'] );
        
        // is created and updated at filled?
        $now = User::$now;
        $this->assertEquals( $now->format('Y-m-d H:i:s'), $found['created_at'] );
        $this->assertEquals( $now->format('Y-m-d'), $found['open_date'] );
        $this->assertEquals( $now->format('Y-m-d H:i:s'), $found['updated_at'] );

        $upTime = clone( $now );
        User::$now = $upTime->add(new \DateInterval('P1D') );
        $this->user->where( $this->user->user_id->eq($id) )->update( ['name'=>'updated'] );

        $found = $this->user->load( $id )[0];
        $this->assertEquals( 'updated', $found['name'] );
        $this->assertEquals( $now->format('Y-m-d H:i:s'), $found['created_at'] );
        $this->assertEquals( $now->format('Y-m-d'), $found['open_date'] );
        $this->assertEquals( $upTime->format('Y-m-d H:i:s'), $found['updated_at'] );
    }

    /**
     * @test
     */
    function select_update_and_delete()
    {
        $this->saveUser(10);
        $d = $this->user;
        // selecting gender is 1.
        $found = $d->where( $d->gender->eq(1) )->select();
        $this->assertEquals( 5, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['gender'] );
        }
        // selecting status is 1.
        $found = $d->load( 1, 'status' );
        $this->assertEquals( 3, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['status'] );
        }
        // updating status is 2.
        $d->where( $d->status->eq(1) )->update( ['status' => 9 ] );
        $found = $d->load( 1, 'status' );
        $this->assertEquals( 0, count( $found ) );
        $found = $d->load( 9, 'status' );
        $this->assertEquals( 3, count( $found ) );
        
        // deleting one of status 9.
        $id_to_del = $found[1]['user_id'];
        $d->delete( $id_to_del );
        $found = $d->load( 9, 'status' );
        $this->assertEquals( 2, count( $found ) );
    }

    /**
     * @test
     */
    function inserting_null_to_bday()
    {
        $user = $this->makeUserData();
        $user['bday'] = null;
        $id = $this->user->insert( $user );
        $this->assertEquals( 1, $id );
        $found = $this->user->load( $id )[0];
        $this->assertEquals( $user['name'], $found['name'] );
        $this->assertEquals( null, $found['bday'] );
    }

    /**
     * @test
     */
    function count_returns_number_and_query_still_works()
    {
        $this->saveUser(10);
        $d = $this->user;
        $count = $d->where( $d->gender->eq(1) )->count();
        $found = $d->select();
        $this->assertEquals( $count, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['gender'] );
        }
    }

    /**
     * @test
     */
    function scopeStatus_selects_by_status()
    {
        $this->saveUser(10);
        $d = $this->user;

        // selecting status is 1.
        $found = $d->status()->select();
        $this->assertEquals( 3, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['status'] );
        }
    }
}
