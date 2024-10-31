<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostCmsUpdateDo extends AutoUpdater_Task_PostCmsUpdateDo
{
    /**
     * @return array
     */
    public function doTask()
    {
        $this->setInput('type', 'cms');
        $this->setInput('slug', 'core');

        return AutoUpdater_Task::getInstance('PostExtensionUpdate', $this->payload)
            ->doTask();
    }
}