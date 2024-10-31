<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostCmsUpgradeAfter extends AutoUpdater_Task_PostCmsUpgradeAfter
{
    /**
     * @return array
     */
    public function doTask()
    {
        return AutoUpdater_Task::getInstance('PostCmsUpdateAfter', $this->payload)
            ->doTask();
    }
}