<?php
namespace WSTest\DbAccess;

require_once( __DIR__ . '/Query_MySql_Test.php' );

/*
 * TODO: more test on Query. and check the overall design as well.
 */

class Query_MySql_Quoted_Test extends Query_MySql_Test
{
    function setUp()
    {
        parent::setUp();
        $this->query->_prepQuoteUseType = 'quote';
    }
}
