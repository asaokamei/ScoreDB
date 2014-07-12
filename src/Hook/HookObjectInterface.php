<?php
namespace WScore\ScoreDB\Hook;

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
