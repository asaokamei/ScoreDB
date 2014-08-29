<?php
namespace WScore\ScoreDB\Hook;

interface EventObjectInterface
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
