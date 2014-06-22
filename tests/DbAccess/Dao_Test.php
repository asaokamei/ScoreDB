<?php
namespace tests\DbAccess;

use tests\DbAccess\Dao\User;
use WScore\DbAccess\Dao;
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
        $this->user = new User();
    }
    
    function makeUserData( $idx=1 )
    {
        $data = [
            'name' => 'test-' . $idx ,
            'age'  => 30 + $idx,
            'bday' => (new \DateTime('1989-01-01'))->add(new \DateInterval('P1D'))->format('Y-m-d'),
            'no_null' => 'not null test: ' . mt_rand(1000,9999),
        ];
        return $data;
    }
    
    function test0()
    {
        $this->assertEquals( 'tests\DbAccess\Dao\User', get_class( $this->user ) );
    }
    
    function test_insert()
    {
        $user = $this->makeUserData();
        $id = $this->user->insert( $user );
    }
}
