<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostChildUpdate extends AutoUpdater_Task_Base
{
    protected $encrypt = false;

    /**
     * @return array
     */
    public function doTask()
    {
        return array(
            'success' => true,
        );
    }
}