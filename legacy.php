<?php
defined('AUTOUPDATER_LIB') or die;

if (!defined('PERFECTDASHBOARD_LIB_PATH'))
{
    define('PERFECTDASHBOARD_LIB_PATH', AUTOUPDATER_LIB_PATH);
}

if (!defined('PERFECTDASHBOARD_VERSION'))
{
    define('PERFECTDASHBOARD_VERSION', AUTOUPDATER_VERSION);
}

if (!class_exists('PerfectDashboard_Installer'))
{
    require_once AUTOUPDATER_LIB_PATH . 'Installer.php';

    class PerfectDashboard_Installer extends AutoUpdater_Installer
    {
    }
}


function autoUpdaterLegacy_getOption($func_name, $default = null)
{
    $option = 'autoupdater' . str_replace(__FUNCTION__, '', $func_name);

    return get_option($option, $default);
}

function autoUpdaterLegacy_updateOption($func_name, $value)
{
    $option = 'autoupdater' . str_replace(__FUNCTION__, '', $func_name);
    return update_option($option, $value);
}


/**
 * Filters the value of an existing option before it is retrieved.
 *
 * The dynamic portion of the hook name, `$option`, refers to the option name.
 *
 * Passing a truthy value to the filter will short-circuit retrieving
 * the option value, returning the passed value instead.
 *
 * @since WP 1.5.0
 * @since WP 4.4.0 The `$option` parameter was added.
 *
 * @param bool|mixed $pre_option Value to return instead of the option value.
 *                               Default false to skip it.
 * @param string     $option     Option name.
 */
function autoUpdaterLegacy_getOption_read_token($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__);
}

function autoUpdaterLegacy_getOption_write_token($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__);
}

function autoUpdaterLegacy_getOption_token_expires_at($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__);
}

function autoUpdaterLegacy_getOption_ping($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__);
}

function autoUpdaterLegacy_getOption_hide_child($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__, 0);
}

function autoUpdaterLegacy_getOption_protect_child($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__, 0);
}

function autoUpdaterLegacy_getOption_whitelabel_name($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__, '');
}

function autoUpdaterLegacy_getOption_whitelabel_author($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__, '');
}

function autoUpdaterLegacy_getOption_whitelabel_child_page($pre_option = false, $option = '')
{
    return autoUpdaterLegacy_getOption(__FUNCTION__, '');
}

/**
 * Filters a specific option before its value is (maybe) serialized and updated.
 *
 * The dynamic portion of the hook name, `$option`, refers to the option name.
 *
 * @since WP 2.6.0
 * @since WP 4.4.0 The `$option` parameter was added.
 *
 * @param mixed  $value     The new, unserialized option value.
 * @param mixed  $old_value The old option value.
 * @param string $option    Option name.
 */
function autoUpdaterLegacy_updateOption_read_token($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_write_token($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_token_expires_at($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_ping($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_hide_child($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_protect_child($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_whitelabel_name($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_whitelabel_author($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

function autoUpdaterLegacy_updateOption_whitelabel_child_page($value = null, $old_value = null, $option = '')
{
    return autoUpdaterLegacy_updateOption(__FUNCTION__, $value);
}

$options = array(
    'read_token',
    'write_token',
    'token_expires_at',
    'ping',
    'hide_child',
    'protect_child',
    'whitelabel_name',
    'whitelabel_author',
    'whitelabel_child_page'
);

foreach ($options as $option)
{
    add_filter('pre_option_perfectdashboard_' . $option, 'autoUpdaterLegacy_getOption_' . $option);
    add_filter('pre_update_option_perfectdashboard_' . $option, 'autoUpdaterLegacy_updateOption_' . $option);
}
