<?php
namespace tests\ScoreDB;

use tests\ScoreDB\Dao\User;
use WScore\ScoreDB\DB;
use WScore\ScoreDB\Paginate;
use WScore\ScoreSql\Sql\Join;
use WScore\ScoreSql\Sql\Where;

class Dao_DbType extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    var $user;

    static function setupBeforeClass()
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        class_exists( 'WScore\ScoreDB\Hooks\Hooks' );
        class_exists( 'tests\ScoreDB\Dao\User' );
        DB::restart();
    }

    function setup()
    {
        throw new \Exception( 'WHAT?' );
    }

    function teardown()
    {
        DB::restart();
    }

    function prepareTest( $dbType )
    {
        DB::restart();
        /** @noinspection PhpIncludeInspection */
        DB::config( include( __DIR__ . "/configs/{$dbType}-config.php" ) );
        $pdo = DB::connect();
        $sql = 'DROP TABLE IF EXISTS dao_user;';
        $pdo->query( $sql );
        /** @noinspection PhpIncludeInspection */
        $pdo->query( include( __DIR__ . "/configs/{$dbType}-create.php" ) );
        $this->user = User::query();
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

        $found = User::query()->load( $id )[0];
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
        ;
        User::query()->where( $d->status->eq(1) )->update( ['status' => 9 ] );
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

    /**
     * @test
     */
    function useIterator()
    {
        $this->saveUser(10);
        $d = $this->user->status();
        
        $count = 0;
        foreach( $d as $user ) {
            $this->assertEquals( 1, $user['status'] );
            $count++;
        }
        $this->assertEquals( 3, $count );

        $d = $this->user->status();
        $stmt = $d->getIterator();
        $this->assertEquals( 'PDOStatement', get_class( $stmt ) );
    }

    /**
     * @test
     */
    function useEmptyIterator()
    {
        $count = 0;
        $d = $this->user->status();
        foreach( $d as $user ) {
            $this->assertEquals( 1, $user['status'] );
            $count++;
        }
        $this->assertEquals( 0, $count );
    }

    /**
     * @test
     */
    function update_using_magic_set()
    {
        $this->saveUser(10);
        $d = $this->user;
        // selecting gender is 1.
        $found = $d->where( $d->gender->eq(1) )->select();
        $this->assertEquals( 5, count( $found ) );
        foreach( $found as $user ) {
            $this->assertEquals( 1, $user['gender'] );
        }
        // updating status is 2.
        $d = User::query();
        $d->status = 9;
        $d->where( $d->status->eq(1) )->update();
        $found = User::query()->load( 1, 'status' );
        $this->assertEquals( 0, count( $found ) );
        $found = User::query()->load( 9, 'status' );
        $this->assertEquals( 3, count( $found ) );
    }

    /**
     * @test
     */
    function limit_and_offset()
    {
        $this->saveUser(10);
        $d = $this->user;
        $d->order('user_id')->offset(3)->limit(2);
        $found = $d->select();
        $this->assertEquals( 2, count( $found ) );
        $this->assertEquals( 4, $found[0]['user_id'] );
        $this->assertEquals( 5, $found[1]['user_id'] );
    }

    /**
     * @test
     */
    function security()
    {
        $data = $this->makeUserData();
        $data['no_null'] = 'any\' OR \'x\'=\'x';
        $id = $this->user->insert( $data );
        $saved = $this->user->load($id);
        $this->assertEquals( $data['no_null'], $saved[0]['no_null'] );

        $data['no_null'] = "t'' OR ''t''=''t'";
        $id = $this->user->insert( $data );
        $saved = $this->user->load($id);
        $this->assertEquals( $data['no_null'], $saved[0]['no_null'] );

        $data['no_null'] = "\'' OR 1=1 --";
        $id = $this->user->insert( $data );
        $saved = $this->user->load($id);
        $this->assertEquals( $data['no_null'], $saved[0]['no_null'] );
    }

    /**
     * @test
     */
    function page()
    {
        // construct initial Query.
        $this->saveUser(10);
        $session = [];
        $pager = new Paginate( $session, '/test/' );
        $pager->set( 'perPage', 3 );
        $this->assertEquals( null, $pager->loadQuery() );

        // query with pagination.
        $user = $this->user->order('user_id');
        $pager->setQuery( $user );
        $pager->saveQuery();
        $pager->queryTotal( $user );

        // verify the queried result.
        $found1 = $user->select();
        $this->assertEquals( 3, count( $found1 ) );
        for( $i=0; $i< count($found1) ; $i++ ) {
            $this->assertEquals( $i+1, $found1[$i]['user_id'] );
        }

        // save session and restore.
        $session = serialize( $session );
        $session = unserialize( $session );

        // recall the query, then paginate to the next page.
        $pager = new Paginate( $session, '/test/' );
        $user2 = $pager->loadQuery(2);
        $this->assertEquals( 'tests\ScoreDB\Dao\User', get_class($user2) );

        $found2 = $user2->select();
        $this->assertEquals( 3, count( $found2 ) );
        $this->assertNotEquals( $found1, $found2 );
        for( $i=0; $i< count($found2) ; $i++ ) {
            $this->assertEquals( $i+4, $found2[$i]['user_id'] );
        }
    }

    /**
     * @test
     */
    function page_with_get()
    {
        // construct initial Query.
        $this->saveUser(10);
        $session = [];
        $_GET = [ '_limit'=>3 ];

        $found1 = $this->query_only_gender_is_1( $session );

        $this->assertEquals( 3, count( $found1 ) );
        for( $i=0; $i< count($found1) ; $i++ ) {
            $this->assertEquals( $i*2+2, $found1[$i]['user_id'] );
            $this->assertEquals( '1', $found1[$i]['gender'] );
        }

        $session = serialize( $session );
        $session = unserialize( $session );

        $_GET = [ '_page'=>2 ];
        $found2 = $this->query_only_gender_is_1( $session );

        $this->assertEquals( 2, count( $found2 ) );
        for( $i=0; $i< count($found2) ; $i++ ) {
            $this->assertEquals( $i*2+8, $found2[$i]['user_id'] );
            $this->assertEquals( '1', $found2[$i]['gender'] );
        }
        $_GET = [];
    }

    function query_only_gender_is_1( & $session )
    {
        $pager = new Paginate( $session );
        /** @var User $user */
        if( !$user = $pager->loadQuery() ) {
            $user = User::query();
            $user->order( 'user_id' )->where( $user->gender->is('1') );
            $pager->setQuery( $user );
        }
        $pager->saveQuery();
        $pager->queryTotal();
        return $pager->queryPage();
    }

    /**
     * @test
     */
    function dba_query_returns_active_query()
    {
        $userData = $this->makeUserData();
        $user = DB::query( 'dao_user', 'user_id' );
        $id = $user->insert( $userData );
        $this->assertEquals( 1, $id );

        // check if the data is loaded.
        $found = $user->load( $id )[0];
        $this->assertEquals( $userData['name'], $found['name'] );
        $this->assertEquals( $userData['no_null'], $found['no_null'] );

        $user->where( $user->user_id->eq($id) )->update( ['name'=>'updated'] );

        $found = User::query()->load( $id )[0];
        $this->assertEquals( 'updated', $found['name'] );
    }

    /**
     * @test
     */
    function profile()
    {
        DB::getDba()->useProfile();

        $user = $this->makeUserData();
        $id = $this->user->insert( $user );
        $this->assertEquals( 1, $id );

        $profiler = DB::getDba()->getProfiler();
        $this->assertEquals( 'Aura\Sql\Profiler', get_class( $profiler ) );
        $profile = $profiler->getProfiles();
        $this->assertTrue( is_array( $profile ) );
        $this->assertFalse( empty( $profile ) );
    }

    /**
     * @test
     */
    function join_on_status_with_same_table()
    {
        // construct initial Query.
        $this->saveUser(10);
        $user = $this->user;
        $found = $this->user->
            table( 'dao_user', 'u1' )->
            join( DB::join( 'dao_user', 'u2' )->left()->
                on( Where::column('status')->identical( 'u1.status' ) )
            )
            ->where( $user->user_id->is(1) )
            ->select();
        $this->assertEquals( 4, count( $found ) );

        $found2 = $this->user->
            table( 'dao_user', 'u1' )->
            join( Join::table( 'dao_user', 'u2' )->using( 'status' ) )->
            where( $user->user_id->is(1) )->
            select();
        $this->assertEquals( 4, count( $found2 ) );

        $this->assertEquals( $found, $found2 );
    }
}
