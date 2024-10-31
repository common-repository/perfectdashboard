<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostCmsEnable extends AutoUpdater_Task_Base
{
    protected $encrypt = false;

    /**
     * @return array
     */
    public function doTask()
    {
        $offline = (bool) AutoUpdater_Config::get('offline');
        if ($offline && AutoUpdater_Config::set('offline', 0))
        {
            $offline = false;
        }

        return array(
            'success'    => ($offline === false),
            'is_offline' => $offline,
        );
    }
}