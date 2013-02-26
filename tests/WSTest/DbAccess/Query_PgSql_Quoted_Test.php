<?php
namespace WSTest\DbAccess;

require_once( __DIR__ . '/Query_PgSql_Test.php' );

/*
 * TODO: more test on Query. and check the overall design as well.
 */

class Query_PgSql_Quoted_Test extends Query_PgSql_Test
{
    function setUp()
    {
        parent::setUp();
        $this->query->_prepQuoteUseType = 'quote';
    }
}
