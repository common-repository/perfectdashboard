<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostCmsUpdateAfter extends AutoUpdater_Task_PostCmsUpdateAfter
{
    /**
     * @return array
     */
    public function doTask()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        wp_upgrade();

        return array(
            'success' => true,
        );
    }
}