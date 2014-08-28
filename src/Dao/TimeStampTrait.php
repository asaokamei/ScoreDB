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
    abstract protected function getDateTimeFormat();

    abstract protected function getTimeStamps();

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

    protected $dateTimeFormat = 'Y-m-d H:i:s';
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
        $dateTimeFormat = $this->getDateTimeFormat() ?: 'Y-m-d H:i:s';
        $timeStamps     = $this->getTimeStamps();
        if( !$timeStamps || !is_array( $timeStamps ) ) {
            return $data;
        }
        if( !isset( $timeStamps[$type] ) || !is_array( $timeStamps[$type] ) ) {
            return $data;
        }
        $filters = $timeStamps[$type];
        if( !static::$now ) static::$now = new DateTime();
        foreach( $filters as $column => $format ) {
            if( is_numeric( $column ) ) {
                $column = $format;
                $format = $dateTimeFormat;
            }
            $data[ $column ] = static::$now->format( $format );
        }
        return $data;
    }
}