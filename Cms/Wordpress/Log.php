<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Log extends AutoUpdater_Log
{
    /**
     * @return string
     */
    public function getLogsPath()
    {
        return rtrim(WP_CONTENT_DIR, '/\\') . '/logs/';
    }
}