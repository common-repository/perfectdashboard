<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Config extends AutoUpdater_Config
{
    protected $prefix = 'autoupdater_';

    protected function getOptionSiteUrl()
    {
        return get_home_url();
    }

    protected function getOptionSiteBackendUrl()
    {
        return get_admin_url();
    }

    protected function getOptionSiteLanguage()
    {
        return str_replace('_', '-',
            get_option('WPLANG', defined('WPLANG') && WPLANG ? WPLANG : 'en_US'));
    }

    protected function getOption($key, $default = null)
    {
        $value = get_option($this->prefix . $key, $default);
        if (empty($value) && $key === 'write_token' && AUTOUPDATER_WP_PLUGIN_BASENAME !== 'autoupdater')
        {
            $value = get_option(AUTOUPDATER_WP_PLUGIN_BASENAME . '_' . $key, $default);
        }

        return $value;
    }

    protected function setOption($key, $value)
    {
        // Possible comparison of values string(1) "0" and int(0) so don't use
        // identical operator
        $old_value = get_option($this->prefix . $key, null);
        if ($old_value == $value && !is_null($old_value))
        {
            return true;
        }

        return update_option($this->prefix . $key, $value);
    }

    protected function removeOption($key)
    {
        return delete_option($this->prefix . $key);
    }

    protected function removeAllOptions()
    {
        global $wpdb;

        $options = $wpdb->get_col('SELECT option_name'
            . ' FROM ' . $wpdb->options
            . ' WHERE option_name LIKE "' . $this->prefix . '%"'
        );

        foreach ($options as $option)
        {
            delete_option($option);
        }

        return true;
    }
}