<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Backuptool extends AutoUpdater_Backuptool
{
    /**
     * @param bool $htaccess_disable
     */
    protected function setWAFExceptions($htaccess_disable = false)
    {
        // Flush htaccess rules to add a new rule for the backup tool directory.
        global $wp_rewrite;
        $wp_rewrite->flush_rules();

        parent::setWAFExceptions($htaccess_disable);
    }

    /**
     * @return string
     */
    protected function getInstallerName()
    {
        return 'angie-wordpress';
    }
}