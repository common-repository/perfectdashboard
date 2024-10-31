<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostChildSettingsRefresh extends AutoUpdater_Task_Base
{
    /**
     * @return array
     */
    public function doTask()
    {
        if (($site_id = (int) $this->input('site_id')))
        {
            AutoUpdater_Config::set('site_id', $site_id);
        }

        return array(
            'success' => AutoUpdater_Config::loadAutoUpdaterConfigByApi(true),
        );
    }
}