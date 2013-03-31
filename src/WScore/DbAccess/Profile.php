<?php
namespace WScore\DbAccess;

use \Psr\Log\LoggerInterface;

/**
 * Class Profile
 *
 * @package WScore\DbAccess
 * 
 * @singleton
 */
class Profile
{
    /**
     * @Inject
     * @var LoggerInterface
     */
    public $log;

    public $count = 0;
    
    public $time = 0;

    /**
     * @param LoggerInterface $log
     */
    public function __construct( $log=null ) 
    {
        if( $log ) $this->log = $log;
    }
    /**
     * log sql such as execution time. 
     * 
     * @param string $query
     * @param float  $time
     * @param array  $prep
     * @param array  $types
     */
    public function log( $query, $time, $prep, $types )
    {
        if( $this->log ) {
            $info = array(
                'query' => $query,
                'time'  => $time,
                'prep'  => $prep,
                'types' => $types,
            );
            $this->log->debug( 'execLog', $info );
        }
        $this->count++;
        $this->time += $time;
    }

    /**
     * get the profile of sql execution count and time. 
     * 
     * @return array
     */
    public function getProfile()
    {
        return array(
            'count' => $this->count,
            'profile' => $this->time,
        );
    }

    /**
     * log the profile of sql execution count and time into logger's info. 
     */
    public function logProfile()
    {
        if( $this->log ) {
            $this->log->info( 'profile', $this->getProfile() );
        }
    }
}