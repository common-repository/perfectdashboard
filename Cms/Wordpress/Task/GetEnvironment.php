<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_GetEnvironment extends AutoUpdater_Task_GetEnvironment
{
    /**
     * @return array
     */
    public function doTask()
    {
        global $wpdb;

        $data = parent::doTask();

        $data['cms_version'] = AUTOUPDATER_WP_VERSION;

        /** $wpdb->is_mysql @since 3.3.0 */
        $data['database_name'] = version_compare(AUTOUPDATER_WP_VERSION, '3.3.0', '<') || $wpdb->is_mysql
            ? 'MySQL' : null;

        $data['database_version'] = $wpdb->db_version();
        $database_version_info    = $wpdb->get_var('SELECT version()');

        if (!empty($database_version_info) && strpos(strtolower($database_version_info), 'mariadb') !== false)
        {
            $data['database_name'] = 'MariaDB';
            $version               = explode('-', $database_version_info);
            if (!empty($version[0]))
            {
                $data['database_version'] = $version[0];
            }
        }

        return $data;
    }
}