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
     * @param mixed $value
     * @param $prefix
     * @return mixed
     */
    public function mutate( $name, $value, $prefix )
    {
        $method = $prefix.ucfirst($name).'Attribute';
        foreach( $this->mutants as $mutant ) {

            if( !method_exists( $mutant, $method ) ) continue;
            return $mutant->$method( $value );
        }
        return $value;
    }

}