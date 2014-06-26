<?php
namespace WScore\DbAccess\Hook;

interface HookObjectInterface
{
    /**
     * @return bool
     */
    public function isLoopBreak();

    /**
     * @return bool
     */
    public function toUseFilterData();
}
