<?php
namespace WScore\DbAccess\Sql;

class Quote
{
    /**
     * @var Query
     */
    protected $format = '"%s"';

    protected $quote = '"';

    /**
     * @param string $q
     */
    public function setQuote( $q ) {
        $this->quote = $q;
        $this->format = $q . '%s' . $q;
    }
    
    /**
     * @param string $name
     * @param array|string $separator
     * @return string
     */
    public function quote( $name, $separator=[' as ', ' ', '.'] )
    {
        if( !$separator ) return $this->quoteString( $name );
        if( !is_array( $separator ) ) $separator = array($separator);
        while( $sep = array_shift( $separator ) ) {
            if( false === stripos( $name, $sep ) ) {
                $list = preg_split( "/[$sep]+/i", $name, PREG_SPLIT_NO_EMPTY );
                foreach( $list as $key => $str ) {
                    $list[$key] = $this->quote( $str, $sep );
                }
                $name = implode( $sep, $list );
            }
        }
        return $name;
    }

    /**
     * @param $name
     * @return string
     */
    public function quoteString( $name )
    {
        if( !$name ) return $name;
        if( $name == '*' ) return $name;
        if( substr( $name, 0, 1 ) == $this->quote && 
            substr( $name, -1 ) == $this->quote ) {
            return $name;
        }
        return sprintf( $this->format, $name );
    }
}