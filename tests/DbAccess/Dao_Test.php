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
    
    function test0()
    {
        $this->assertEquals( 'tests\DbAccess\Dao\User', get_class( $this->user ) );
    }
    
    function test_extendedPdo()
    {
        $sql = 'INSERT INTO `test_WScore` ( `name`, `age`, `bday`, `no_null` ) VALUES ( :db_prep_1, :db_prep_2, :db_prep_3, :db_prep_4 )';
        $bind = [
            'db_prep_1' => 'test',
            'db_prep_2' => '30',
            'db_prep_3' => '1989-01-01',
            'db_prep_4' => 'not null test',
        ];
        $pdo = Dba::db();
        $pdo->perform( $sql, $bind );
    }

    function test_pdo()
    {
        $sql = 'INSERT INTO `test_WScore` ( `name`, `age`, `bday`, `no_null` ) VALUES ( :db_prep_1, :db_prep_2, :db_prep_3, :db_prep_4 )';
        $bind = [
            ':db_prep_1' => 'test',
            ':db_prep_2' => '30',
            'db_prep_3' => '1989-01-01',
            'db_prep_4' => 'not null test',
        ];
        $pdo = Dba::db();
        $pdo = $pdo->getPdo();
        $stm = $pdo->prepare( $sql );
        $stm->execute( $bind );
    }

    function test_insert()
    {
        $id = $this->user->insert([
            'name' => 'test',
            'age'  => '30',
            'bday' => '1989-01-01',
            'no_null' => 'not null test',
        ]);
    }
}
