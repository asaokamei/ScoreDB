<?php
namespace WScore\ScoreDB\Hook;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Query;

class Events
{
    /**
     * apply to any events, i.e.
     * Events::hookEvent( Events::ANY_EVENT, $eventHandler );
     */
    const ANY_EVENT = '*';

    /**
     * @var array
     */
    protected $hooks = [
        self::ANY_EVENT => []
    ];

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
     * @param Query|Dao|null  $query
     * @return mixed|null
     */
    public function hook( $event, $data=null, $query=null )
    {
        $method = 'on'.ucfirst($event).'Hook';
        if( array_key_exists( $method, $this->hooks) ) {
            $this->dispatchHook( $method, $data, $query );
        }
        $method = 'on'.ucfirst($event).'Filter';
        if( array_key_exists( $method, $this->hooks) ) {
            $data = $this->dispatchFilter( $method, $data, $query );
        }
        return $data;
    }

    /**
     * @param string $method
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function findHooks($method)
    {
        $hooks = $this->hooks[ self::ANY_EVENT ];
        if( array_key_exists($method, $this->hooks) ) {
            if( !is_array($this->hooks[$method]) ) {
                throw new \InvalidArgumentException;
            }
            $hooks = array_merge( $hooks, $this->hooks[$method] );
        }
        return $hooks;
    }

    /**
     * @param string    $method
     * @param mixed     $data
     * @param Query|Dao|null $query
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function dispatchHook( $method, $data, $query )
    {
        if( !$hooks = $this->findHooks($method) ) return $data;
        foreach( $hooks as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            $hook->$method( $data, $query );
            if( $hook instanceof EventObjectInterface && $hook->isLoopBreak() ) break;
        }
        return $data;
    }

    /**
     * @param string    $method
     * @param mixed     $data
     * @param Query|Dao|null $query
     * @return mixed
     */
    protected function dispatchFilter( $method, $data, $query )
    {
        if( !$hooks = $this->findHooks($method) ) return $data;
        foreach( $this->hooks[$method] as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            $data = $hook->$method( $data, $query );
            if( $hook instanceof EventObjectInterface ) {
                if( $hook->toUseFilterData() ) {
                    $this->useFilterData = true;
                }
                if( $hook->isLoopBreak() ) break;
            }
        }
        return $data;
    }
}