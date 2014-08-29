<?php
namespace WScore\ScoreDB\Hook;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Query;

class Events
{
    /**
     * @var array
     */
    protected $hooks = [];

    protected $useFilterData = false;

    public function __construct() {}
    
    /**
     * @return bool
     */
    public function usesFilterData()
    {
        return $this->useFilterData;
    }

    /**
     * @param string $event
     * @param string|object|\Closure $hook
     */
    public function hookEvent( $event, $hook )
    {
        if( is_string($hook) ) {
            $hook = new $hook;
        }
        $this->hooks[$event][] = $hook;
    }

    /**
     * dumb hooks for various events. Fires event name
     * on{$event}Hook and on{$event}Filter as event name.
     *
     * @param string     $event
     * @param mixed      $data
     * @param Dao|Query  $query
     * @return mixed|null
     */
    public function hook( $event, $data=null, $query=null )
    {
        $this->dispatchEvent( 'on'.ucfirst($event).'Hook', $data, $query );
        return $this->dispatchEvent( 'on'.ucfirst($event).'Filter', $data, $query, true );
    }

    /**
     * @param string    $method
     * @param mixed     $data
     * @param Query|Dao $query
     * @param bool      $useReturned
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function dispatchEvent( $method, $data, $query, $useReturned=false )
    {
        if( !array_key_exists($method, $this->hooks) ) return $data;
        if( !is_array($this->hooks[$method]) ) return $data;
        foreach( $this->hooks[$method] as $hook ) {

            if( !method_exists( $hook, $method ) ) {
                throw new \InvalidArgumentException;
            }
            if( $useReturned ) {
                $data = $hook->$method( $data, $query );
            } else {
                $hook->$method( $data, $query );
            }
            if( !$hook instanceof EventObjectInterface ) continue;
            if( $hook->toUseFilterData() ) {
                $this->useFilterData = true;
                break;
            }
            if( $hook->isLoopBreak() ) break;
        }
        return $data;
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
        foreach( $this->hookObjects as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            return $hook->$method( $value );
        }
        return $value;
    }
}