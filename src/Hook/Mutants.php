<?php
namespace WScore\ScoreDB\Hook;

class Mutants
{
    /**
     * @var array
     */
    protected $mutants = [];

    /**
     * @param object $mutant
     */
    public function setMutant( $mutant )
    {
        $this->mutants[] = $mutant;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function muteInto( $name, $value )
    {
        $method = 'set'.ucfirst($name).'Attribute';
        foreach( $this->mutants as $mutant ) {

            if( !method_exists( $mutant, $method ) ) continue;
            return $mutant->$method( $value );
        }
        return $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function muteBack( $name, $value )
    {
        $method = 'get'.ucfirst($name).'Attribute';
        foreach( $this->mutants as $mutant ) {

            if( !method_exists( $mutant, $method ) ) continue;
            return $mutant->$method( $value );
        }
        return $value;
    }

}