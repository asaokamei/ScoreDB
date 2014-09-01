<?php
namespace WScore\ScoreDB\Hook;

class Mutants
{
    /**
     * @var array
     */
    protected $mutants = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @var string
     */
    protected $dateFormat;

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
        if( in_array( $name, $this->dates ) ) {
            return new \DateTime($value);
        }
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
        if( in_array($name, $this->dates) ) {
            return $this->muteBackDateTime($name, $value);
        }
        if( is_object($value) && method_exists( $value, '__toString') ) {
            return (string) $value;
        }
        $method = 'get'.ucfirst($name).'Attribute';
        foreach( $this->mutants as $mutant ) {

            if( !method_exists( $mutant, $method ) ) continue;
            return $mutant->$method( $value );
        }
        return $value;
    }

    /**
     * @param array  $dates
     * @param string $format
     */
    public function setDates( $dates, $format )
    {
        $this->dates = $dates;
        $this->dateFormat = $format;
    }

    /**
     * @param string $key
     * @param \DateTime|mixed $value
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function muteBackDateTime( $key, $value )
    {
        if( in_array($key, $this->dates) ) {
            if( is_string($value) ) {
                $date = new \DateTime($value);
            } elseif( $value instanceof \DateTime ) {
                $date = $value;
            } else {
                throw new \InvalidArgumentException();
            }
            return $date->format($this->dateFormat);
        }
        return $value;
    }

}