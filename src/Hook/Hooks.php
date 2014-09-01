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

    /**
     * @param string $name
     * @param mixed $value
     * @param $prefix
     * @return mixed
     */
    public function mutate( $name, $value, $prefix )
    {
        return $this->mutants->mutate( $name, $value, $prefix );
    }
}