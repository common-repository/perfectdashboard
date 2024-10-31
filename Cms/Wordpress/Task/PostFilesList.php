<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_Cms_Wordpress_PostFilesList extends AutoUpdater_Task_PostFilesList
{
    /**
     * @return array
     */
    protected function getDefaultExclusions()
    {
        $wp_content_dir = str_replace(AUTOUPDATER_SITE_PATH, '',
            WP_CONTENT_DIR . '/'
        );

        $exclusions = array_merge(parent::getDefaultExclusions(), array(
            $wp_content_dir . 'cache',
            $wp_content_dir . 'logs',
            $wp_content_dir . 'upgrade',
        ));

        return $exclusions;
    }
}