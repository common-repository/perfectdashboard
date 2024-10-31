<?php
/**
 * Plugin Name: Perfect Dashboard
 * Plugin URI: https://perfectdashboard.com/?utm_source=backend&utm_medium=installer&utm_campaign=in&utm_term=WP
 * Description:
 * Version: 1.23.0
 * Text Domain: autoupdater
 * Author: Perfect Dashboard
 * Author URI: https://perfectdashboard.com/?utm_source=backend&utm_medium=installer&utm_campaign=in&utm_term=WP
 * License: GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
function_exists('add_action') or die;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if (!defined('AUTOUPDATER_WP_VERSION'))
{
    define('AUTOUPDATER_WP_VERSION', $GLOBALS['wp_version']);
}

if (defined('CMSDETECTOR') && !defined('CMSDETECTOR_VERSION') && php_sapi_name() === 'cli')
{
    // Do not load the plugin while CMS Detector is being initialized
}
elseif (file_exists(WP_PLUGIN_DIR . '/autoupdater/autoupdater.php'))
{
    // Do not run the plugin, as there is already a replacement
}
elseif (version_compare(AUTOUPDATER_WP_VERSION, '3.0', '>=') &&
    version_compare(PHP_VERSION, '5.3', '>='))
{
    $data = get_file_data(__FILE__, array('Version' => 'Version'));

    define('AUTOUPDATER_WP_PLUGIN_NAME', 'Perfect Dashboard');
    define('AUTOUPDATER_WP_PLUGIN_FILE', __FILE__);
    define('AUTOUPDATER_WP_PLUGIN_PATH', dirname(__FILE__) . '/');
    define('AUTOUPDATER_WP_PLUGIN_SLUG', plugin_basename(__FILE__));
    define('AUTOUPDATER_WP_PLUGIN_BASENAME', basename(__FILE__, '.php'));

    define('AUTOUPDATER_LIB', true);
    define('AUTOUPDATER_CMS', 'wordpress');
    define('AUTOUPDATER_SITE_PATH', rtrim(ABSPATH, '/\\') . '/');
    define('AUTOUPDATER_VERSION', $data['Version']);
    define('AUTOUPDATER_STAGE', 'app');

    function AutoUpdater_getRootPath()
    {
        if (!empty($_SERVER['SCRIPT_FILENAME']))
        {
            $path = dirname(realpath($_SERVER['SCRIPT_FILENAME'])) . '/';
            if (defined('CMSDETECTOR'))
            {
                // Core files in subdirectory
                if (!file_exists($path . 'index.php') && file_exists($path . '../index.php'))
                {
                    return dirname($path) . '/';
                }
            }
            elseif (basename($path) == 'wp-admin')
            {
                return dirname($path) . '/';
            }

            return $path;
        }

        $files = get_included_files();
        if (isset($files[0]) && substr($files[0], -9) == 'index.php')
        {
            return dirname(realpath($files[0])) . '/';
        }

        return AUTOUPDATER_SITE_PATH;
    }

    define('AUTOUPDATER_ROOT_PATH', AutoUpdater_getRootPath());

    require_once AUTOUPDATER_WP_PLUGIN_PATH . 'lib/src/Init.php';

    $api = AutoUpdater_Api::getInstance();

    if (is_admin() || $api->isInitialized() || defined('WP_CLI'))
    {
        require_once AUTOUPDATER_LIB_PATH . 'Installer.php';
        AutoUpdater_Installer::getInstance();
    }

    if (php_sapi_name() === 'cli')
    {
        @include_once AUTOUPDATER_WP_PLUGIN_PATH . 'legacy.php';
    }
    require_once AUTOUPDATER_WP_PLUGIN_PATH . 'app/Application.php';

    AutoUpdater_WP_Application::getInstance();
}
elseif (!function_exists('autoUpdaterRequirementsNotice'))
{
    function autoUpdaterRequirementsNotice()
    {
        ?>
        <div class="error">
            <p><?php printf(__('Perfect Dashboard plugin requires WordPress %s and PHP %s', 'autoupdater'), '3.0+', '5.3+'); ?></p>
        </div>
        <?php

    }

    add_action('admin_notices', 'autoUpdaterRequirementsNotice');
}
