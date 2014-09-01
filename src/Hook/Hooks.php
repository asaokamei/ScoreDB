<?php
namespace WScore\ScoreDB\Hook;

use WScore\ScoreDB\Dao;
use WScore\ScoreDB\Query;

class Hooks
{
    /**
     * @var Events
     */
    protected $events;

    /**
     * @var Scopes
     */
    protected $scopes;

    /**
     * @var Mutants
     */
    protected $mutants;

    public function __construct()
    {
        $this->events = new Events();
        $this->scopes = new Scopes();
        $this->mutants = new Mutants();
    }

    /**
     * @param string $event
     * @param string|object|\Closure $hook
     */
    public function hookEvent( $event, $hook )
    {
        $this->events->hookEvent( $event, $hook );
    }

    /**
     * @param object $scope
     */
    public function setScope( $scope )
    {
        $this->scopes->setScope( $scope );
    }

    /**
     * @param object $mutant
     */
    public function setMutant( $mutant )
    {
        $this->mutants->setMutant( $mutant );
    }

    // +----------------------------------------------------------------------+
    //  scopes
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param Query  $query
     * @param array  $args
     * @return $this
     */
    public function scope( $name, $query, $args )
    {
        return $this->scopes->scope( $name, $query, $args );
    }

    // +----------------------------------------------------------------------+
    //  events (hooks and filters)
    // +----------------------------------------------------------------------+
    /**
     * dumb hooks for various events. $data are all string.
     * available events are:
     * - constructing, constructed, newQuery,
     * - selecting, selected, inserting, inserted,
     * - updating, updated, deleting, deleted,
     *
     * @param string $event
     * @param mixed  $data
     * @param Dao|null  $query
     * @return mixed|null
     */
    public function hook( $event, $data=null, $query=null )
    {
        return $this->events->hook( $event, $data, $query );
    }

    /**
     * @return bool
     */
    public function usesFilterData()
    {
        return $this->events->usesFilterData();
    }

    // +----------------------------------------------------------------------+
    //  mutations
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function muteInto( $name, $value )
    {
        return $this->mutants->muteInto( $name, $value );
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function muteBack( $name, $value )
    {
        return $this->mutants->muteBack( $name, $value );
    }

    // +----------------------------------------------------------------------+
}