<?php
namespace WScore\ScoreDB\Hook;

abstract class HookObjectAbstract implements HookObjectInterface
{
    /**
     * @var bool
     */
    protected $_breakEventLoop = false;

    /**
     * @var bool
     */
    protected $_useFilteredData = false;

    /**
     * returns bool to break loop or not.
     *
     * @return bool
     */
    public function isLoopBreak()
    {
        return $this->_breakEventLoop;
    }

    /**
     * call this method to break the event loop.
     *
     * @param bool $break
     */
    protected function breakEventLoop($break=true)
    {
        $this->_breakEventLoop = $break;
    }

    /**
     * call this method to use the returned data
     * from filter to be used as result.
     *
     * @param bool $use
     */
    protected function useFilterData($use=true)
    {
        $this->breakEventLoop();
        $this->_useFilteredData = $use;
    }

    /**
     * @return bool
     */
    public function toUseFilterData()
    {
        return $this->_useFilteredData;
    }

}
