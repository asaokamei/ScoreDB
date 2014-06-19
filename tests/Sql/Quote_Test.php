<?php
namespace tests\Sql;

use WScore\DbAccess\Sql\Quote;

require_once( dirname(__DIR__).'/autoloader.php');

class Quote_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Quote
     */
    var $q;
    
    function setup()
    {
        $this->q = new Quote();
    }
    
    function get($head='test') {
        return $head . mt_rand(1000,9999);
    }
    
    function test0()
    {
        $this->assertEquals( 'WScore\DbAccess\Sql\Quote', get_class( $this->q ) );
    }

    /**
     * @test
     */
    function quote_wraps_value()
    {
        $token = $this->get();
        $quoted = $this->q->quote($token);
        $this->assertEquals( "\"{$token}\"", $quoted );
    }

    /**
     * @test
     */
    function setQuote_uses_different_char_to_quote()
    {
        $token = $this->get();
        $this->q->setQuote('*');
        $quoted = $this->q->quote($token);
        $this->assertEquals( "*{$token}*", $quoted );
    }

    /**
     * @test
     */
    function quote_does_not_quote_a_quoted_value()
    {
        $token = $this->q->quote($this->get());;
        $quoted = $this->q->quote($token);
        $this->assertEquals( $token, $quoted );
    }

    /**
     * @test
     */
    function quote_split_as_and_space_and_period()
    {
        $token = "test.more as quote";
        $quoted = $this->q->quote($token);
        $this->assertEquals( '"test"."more" as "quote"', $quoted );
    }
}