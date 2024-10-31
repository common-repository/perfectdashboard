<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostFileDownload extends AutoUpdater_Task_PostFileDownload
{
    /**
     * @return array
     */
    public function doTask()
    {
        $type = $this->input('type');
        $slug = $this->input('slug');

        if (!$this->input('file_url') && ($type == 'plugin' || $type == 'theme') && $slug)
        {
            $updates = get_site_transient($type == 'plugin' ? 'update_plugins' : 'update_themes');
            if (!empty($updates->response[$slug]))
            {
                $update = (array) $updates->response[$slug];
                if (!empty($update['package']))
                {
                    $this->setInput('file_url', $update['package']);
                }
            }
        }

        return parent::doTask();
    }
}