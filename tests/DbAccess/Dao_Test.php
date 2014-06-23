<?php
namespace tests\DbAccess;

use tests\DbAccess\Dao\DaoClean;

require_once( __DIR__ . '/../autoloader.php' );

class Dao_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function DaoClean_has_table_and_keyName_set()
    {
        $dao = new DaoClean();
        $this->assertEquals( 'tests\DbAccess\Dao\DaoClean', get_class( $dao ) );
        $this->assertEquals( 'DaoClean',$dao->table );
        $this->assertEquals( 'DaoClean_id',$dao->keyName );
    }


}
