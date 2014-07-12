<?php
namespace WScore\DbAccess\Hook;

use WScore\DbAccess\Query;

class Hooks
{
    /**
     * @var HookObjectInterface[]
     */
    protected $hooks = [];

    protected $useFilterData = false;

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
                call_user_func_array( [$hook, $scope], $query, $args );
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
     * @param string       $event
     * @param mixed  $data
     * @return mixed|null
     */
    public function hook( $event, $data=null )
    {
        foreach( $this->hooks as $hook ) {

            if( !method_exists( $hook, $method = 'on'.ucfirst($event).'Hook' ) ) continue;
            $hook->$method( $data );
            if( !$hook instanceof HookObjectInterface ) continue;
            if( $hook->isLoopBreak() ) break;
        }
        foreach( $this->hooks as $hook ) {

            if( !method_exists( $hook, $method = 'on'.ucfirst($event).'Filter' ) ) continue;
            $data = $hook->$method( $data );
            if( !$hook instanceof HookObjectInterface ) continue;
            if( $hook->toUseFilterData() ) {
                $this->useFilterData = true;
                break;
            }
            if( $hook->isLoopBreak() ) break;
        }
        return $data;
    }

}