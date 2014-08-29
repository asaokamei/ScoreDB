<?php
namespace tests\ScoreDB\Dao;

use WScore\ScoreDB\Hook\EventObjectAbstract;

class FilterToReturnTest extends EventObjectAbstract
{
    function onTestFilter( $value )
    {
        $this->useFilterData();
        return 'tested-'.$value;
    }

    function onSelectingFilter( $value )
    {
        $this->useFilterData();
        return 'tested-'.$value;
    }
}
