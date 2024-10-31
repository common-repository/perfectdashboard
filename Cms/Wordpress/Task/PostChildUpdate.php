<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostChildUpdate extends AutoUpdater_Task_PostChildUpdate
{
    /**
     * @return array
     */
    public function doTask()
    {
        $this->setInput('type', 'plugin');
        $this->setInput('slug', AUTOUPDATER_WP_PLUGIN_SLUG);
        $this->setInput('path', AutoUpdater_Config::getAutoUpdaterUrl()
                . 'download/child/' . AUTOUPDATER_CMS . '/' . AUTOUPDATER_WP_PLUGIN_BASENAME . '.zip');

        return AutoUpdater_Task::getInstance('PostExtensionUpdate', $this->payload)
            ->doTask();
    }
}