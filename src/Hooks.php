<?php
namespace WScore\DbAccess;

class Hooks
{
    protected $hooks = [];

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
     * @param mixed|null   $data
     * @return mixed|null
     */
    public function hook( $event, $data=null )
    {
        $args = func_get_args();
        array_shift($args);
        foreach( $this->hooks as $hook ) {
            if( !method_exists( $hook, $method = 'on'.ucfirst($event).'Hook' ) ) continue;
            call_user_func_array( [$hook, $method], $args );
        }
        foreach( $this->hooks as $hook ) {
            if( !method_exists( $hook, $method = 'on'.ucfirst($event).'Filter' ) ) continue;
            $data = call_user_func_array( [$hook, $method], $args );
        }
        return $data;
    }

}