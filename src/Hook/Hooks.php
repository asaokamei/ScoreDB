<?php
namespace WScore\ScoreDB\Hook;

use WScore\ScoreDB\Query;

class Hooks
{
    /**
     * @var HookObjectInterface[]
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
     * @param object $hook
     */
    public function setHook( $hook )
    {
        $this->hooks[] = $hook;
    }

    /**
     * @param string $name
     * @param Query  $query
     * @param array  $args
     * @return $this
     */
    public function scope( $name, $query, $args )
    {
        foreach( $this->hooks as $hook ) {
            if( method_exists( $hook, $scope = 'scope'.ucfirst($name) ) ) {
                call_user_func_array( [$hook, $scope], [$query]+$args );
                return $this;
            }
        }
        return false;
    }

    /**
     * dumb hooks for various events. $data are all string.
     * available events are:
     * - constructing, constructed, newQuery,
     * - selecting, selected, inserting, inserted,
     * - updating, updated, deleting, deleted,
     *
     * @param string $event
     * @param mixed  $data
     * @param Query  $query
     * @return mixed|null
     */
    public function hook( $event, $data=null, $query=null )
    {
        $method = 'on'.ucfirst($event).'Hook';
        foreach( $this->hooks as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            $hook->$method( $data, $query );
            if( !$hook instanceof HookObjectInterface ) continue;
            if( $hook->isLoopBreak() ) break;
        }

        $method = 'on'.ucfirst($event).'Filter';
        foreach( $this->hooks as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            $data = $hook->$method( $data, $query );
            if( !$hook instanceof HookObjectInterface ) continue;
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
        foreach( $this->hooks as $hook ) {

            if( !method_exists( $hook, $method ) ) continue;
            return $hook->$method( $value );
        }
        return $value;
    }
}