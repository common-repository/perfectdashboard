<?php
defined('AUTOUPDATER_LIB') or die;

if (!defined('AUTOUPDATER_STAGE'))
{
    define('AUTOUPDATER_STAGE', 'app');
}
if (!empty($_REQUEST['pd_callback']))
{
    // callback syntax: "subdomain:port:protocol". Example: "app:443:https" or just "app"
    define('AUTOUPDATER_CALLBACK', preg_replace('/[^a-z0-9:]/', '', $_REQUEST['pd_callback']));
}

if (!defined('AUTOUPDATER_CMS'))
{
    define('AUTOUPDATER_CMS', null);
}
if (!defined('AUTOUPDATER_VERSION'))
{
    define('AUTOUPDATER_VERSION', '1.0');
}
if (!defined('AUTOUPDATER_SITE_PATH'))
{
    define('AUTOUPDATER_SITE_PATH', dirname(dirname(__FILE__)) . '/');
}

if (!defined('AUTOUPDATER_ROOT_PATH'))
{
    define('AUTOUPDATER_ROOT_PATH', AUTOUPDATER_SITE_PATH);
}

if (AUTOUPDATER_STAGE != 'app' && !defined('AUTOUPDATER_DEBUG'))
{
    define('AUTOUPDATER_DEBUG', true);
}

if (!defined('AUTOUPDATER_LIB_PATH'))
{
    define('AUTOUPDATER_LIB_PATH', dirname(__FILE__) . '/');

    require_once AUTOUPDATER_LIB_PATH . 'Loader.php';
    require_once AUTOUPDATER_LIB_PATH . 'Config.php';

    require_once AUTOUPDATER_LIB_PATH . 'Api.php';
    require_once AUTOUPDATER_LIB_PATH . 'Authentication.php';
    require_once AUTOUPDATER_LIB_PATH . 'Application.php';
    require_once AUTOUPDATER_LIB_PATH . 'Backuptool.php';
    require_once AUTOUPDATER_LIB_PATH . 'Db.php';
    require_once AUTOUPDATER_LIB_PATH . 'Filemanager.php';
    require_once AUTOUPDATER_LIB_PATH . 'Log.php';
    require_once AUTOUPDATER_LIB_PATH . 'Request.php';
    require_once AUTOUPDATER_LIB_PATH . 'Response.php';
    require_once AUTOUPDATER_LIB_PATH . 'Task.php';

    require_once AUTOUPDATER_LIB_PATH . 'Exception/Response.php';

    if (file_exists(AUTOUPDATER_LIB_PATH . 'vendor/autoload.php'))
    {
        require_once AUTOUPDATER_LIB_PATH . 'vendor/autoload.php';
    }
}