<?php
namespace tests\DbAccess\Dao;

use WScore\DbAccess\Hook\HookObjectAbstract;

class FilterToReturnTest extends HookObjectAbstract
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
