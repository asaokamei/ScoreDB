<?php
namespace WScore\ScoreDB\Dao;

use DateTime;

/**
 * Class DaoTrait
 * @package WScore\ScoreDB
 *
 * a trait to behave like a DAO.
 * the class must have or extend the QueryInterface.
 */
trait TimeStampTrait
{
    /**
     * @var DateTime
     */
    public static $now;

    /**
     * time stamps config.
     *
     * $timeStamps = array(
     *    'created_at' => [ column-name, [ column-name => datetime-format ] ],
     *    'updated_at' => [ column-name, [ column-name => datetime-format ] ],
     * );
     * where
     * - types are created_at or updated_at.
     * - list the column-name, or array of column-name with datetime-format.
     *
     * @var array
     */
    /*
    protected $timeStamps = array(
        'created_at' => [ 'created_at', 'created_date' => 'Y-m-d' ],
        'updated_at' => [ 'updated_at' ],
    );
    */

    /**
     * @param array $data
     * @return array
     */
    public function onCreateStampFilter( $data )
    {
        $data = $this->onTimeStampFilter( $data, 'created_at' );
        $data = $this->onTimeStampFilter( $data, 'updated_at' );
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function onUpdateStampFilter( $data )
    {
        $data = $this->onTimeStampFilter( $data, 'updated_at' );
        return $data;
    }

    /**
     * @param array $data
     * @param string $type
     * @return array
     */
    protected function onTimeStampFilter( $data, $type )
    {
        if( !isset( $this->timeStamps ) ||
            !is_array( $this->timeStamps ) ) {
            return $data;
        }
        if( !isset( $this->timeStamps[$type] ) ||
            !is_array( $this->timeStamps[$type] ) ) {
            return $data;
        }
        $filters = $this->timeStamps[$type];
        if( !static::$now ) static::$now = new DateTime();
        foreach( $filters as $column => $format ) {
            if( is_numeric( $column ) ) {
                $column = $format;
                $format = 'Y-m-d H:i:s';
            }
            $data[ $column ] = static::$now->format( $format );
        }
        return $data;
    }
}