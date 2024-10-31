<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostCmsDisable extends AutoUpdater_Task_Base
{
    protected $encrypt = false;

    /**
     * @return array
     */
    public function doTask()
    {
        $offline = (bool) AutoUpdater_Config::get('offline');
        if (!$offline && AutoUpdater_Config::set('offline', 1))
        {
            $offline = true;
        }

        return array(
            'success'    => ($offline === true),
            'is_offline' => $offline,
        );
    }
}