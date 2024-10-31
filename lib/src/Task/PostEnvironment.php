<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostEnvironment extends AutoUpdater_Task_Base
{
    /**
     * @return array
     */
    public function doTask()
    {
        return AutoUpdater_Task::getInstance('GetEnvironment', $this->payload)
            ->doTask();
    }
}