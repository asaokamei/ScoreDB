<?php
namespace WScore\DbAccess\Hook;

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