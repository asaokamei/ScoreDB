<?php
namespace WScore\DbAccess;

class Hooks
{
    protected $hooks = [];

    /**
     * @return bool
     */
    public function usesFilterData()
    {
        return false;
    }

    /**
     * @param object $hook
     */
    public function setHook( $hook )
    {
        $this->hooks[] = $hook;
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
        }
        foreach( $this->hooks as $hook ) {
            if( !method_exists( $hook, $method = 'on'.ucfirst($event).'Filter' ) ) continue;
            $data = $hook->$method( $data );
        }
        return $data;
    }

}