<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_GetExtensions extends AutoUpdater_Task_GetExtensions
{
    protected $current_theme = '';
    protected $updates       = array();

    /**
     * @return array
     */
    protected function getExtensions()
    {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
        require_once ABSPATH . '/wp-admin/includes/theme.php';

        $extensions    = array();
        $this->updates = $this->getUpdatesFromCustomServers();

        $cms                 = new stdClass();
        $cms->name           = 'WordPress';
        $cms->type           = 'cms';
        $cms->slug           = 'wordpress';
        $cms->version        = get_bloginfo('version');
        $cms->enabled        = 1;
        $cms->update_servers = array();
        $cms->update         = null;

        $translations                 = new stdClass();
        $translations->name           = 'Translations';
        $translations->type           = 'language';
        $translations->slug           = 'core';
        $translations->version        = AUTOUPDATER_WP_VERSION;
        $translations->enabled        = 1;
        $translations->update_servers = array();
        $translations->update         = $this->checkForUpdates($translations->slug, $translations->type);

        $extensions[] = $cms;
        $extensions[] = $translations;

        $list = get_plugins();

        if (version_compare(AUTOUPDATER_WP_VERSION, '3.4.0', '>='))
        {
            $list                = array_merge($list, wp_get_themes());
            $this->current_theme = $this->filterHTML(wp_get_theme()->get('Name'));
        }
        else
        {
            $list                = array_merge($list, get_allowed_themes());
            $this->current_theme = $this->filterHTML(get_current_theme());
        }

        foreach ($list as $slug => $item)
        {
            if ($item instanceof WP_Theme || isset($item['Template']))
            {
                $extensions[] = $this->getThemeInfo($slug, $item);
            }
            elseif (isset($item['PluginURI']))
            {
                $plugin = $this->getPluginInfo($slug, $item);
                if ($slug == 'pwebcontact/pwebcontact.php')
                {
                    $this->filterPluginPwebcontact($plugin);
                }
                $extensions[] = $plugin;
            }
        }

        return $extensions;
    }

    /**
     * @param string $slug
     * @param array  $plugin
     *
     * @return array
     */
    protected function getPluginInfo($slug, $plugin)
    {
        $item                 = new stdClass();
        $item->name           = $this->filterHTML($plugin['Name']);
        $item->type           = 'plugin';
        $item->slug           = $slug;
        $item->version        = strtolower($this->filterHTML($plugin['Version']));
        $item->enabled        = (int) is_plugin_active($slug);
        $item->update_servers = array();
        $item->update         = $this->checkForUpdates($item->slug, $item->type);

        if ($slug == AUTOUPDATER_WP_PLUGIN_SLUG)
        {
            $item->name = $this->filterHTML(AutoUpdater_Config::get('whitelabel_name', $item->name));
        }

        return $item;
    }

    /**
     * @param string         $slug
     * @param array|WP_Theme $theme
     *
     * @return array
     */
    protected function getThemeInfo($slug, $theme)
    {
        /**
         * @var WP_Theme $theme
         * @since 3.4.0
         */
        $legacy = !($theme instanceof WP_Theme);

        // build array with themes data to Dashboard
        $item                 = new stdClass();
        $item->name           = $this->filterHTML($legacy ? $theme['Name'] : $theme->get('Name'));
        $item->type           = 'theme';
        $item->slug           = $legacy ? $theme['Template'] : pathinfo($slug, PATHINFO_FILENAME);
        $item->version        = strtolower($this->filterHTML($legacy ? $theme['Version'] : $theme->get('Version')));
        $item->enabled        = (int) ($this->current_theme == $item->name);
        $item->update_servers = array();
        $item->update         = $this->checkForUpdates($item->slug, $item->type);

        return $item;
    }

    /**
     * @return array
     */
    protected function getUpdatesFromCustomServers()
    {
        global $pagenow;
        $pagenow = 'update-core.php';

        // get updates for exceptional extensions (it must be called here)
        if (!class_exists(AutoUpdater_Loader::getClassPrefix() . 'Cms_Wordpress_Helper_Extension'))
        {
            require_once AUTOUPDATER_WP_PLUGIN_PATH . 'Cms/Wordpress/Helper/Extension.php';
        }
        AutoUpdater_Cms_Wordpress_Helper_Extension::loadMasterSliderPro();

        if (!class_exists(AutoUpdater_Loader::getClassPrefix() . 'Cms_Wordpress_Helper_Tracker'))
        {
            require_once AUTOUPDATER_WP_PLUGIN_PATH . 'Cms/Wordpress/Helper/Tracker.php';
        }
        AutoUpdater_Cms_Wordpress_Helper_Tracker::initDefaults();

        // catch updateservers
        add_filter('pre_http_request', 'AutoUpdater_Cms_Wordpress_Helper_Tracker::trackRequest', 11, 3);

        // delete cached data with updates
        delete_site_transient('update_plugins');
        delete_site_transient('update_themes');
        wp_cache_delete('plugins', 'plugins');

        do_action('load-update-core.php');

        // find updates
        // do it two times, so all data will be correctly filled after deleting whole site_transient for update_plugins and update_themes
        // looks redundant, but for sure after calling wp_update only once there's no "checked" property in update_plugins and update_themes transients
        // and available updates of some plugins are missing in "response" property of these transients
        wp_update_plugins();
        wp_update_plugins();
        wp_update_themes();
        wp_update_themes();

        // get updates
        $plugins = get_site_transient('update_plugins');
        $themes  = get_site_transient('update_themes');

        $updates         = array();
        $forbidden_hosts = array(
            'downloads.wordpress.org',
            'www.perfect-web.co',
        );

        if (!empty($plugins->response))
        {
            foreach ($plugins->response as $slug => $plugin)
            {
                if (!is_object($plugin))
                {
                    if (is_array($plugin))
                    {
                        $plugin = (object) $plugin;
                    }
                    else
                    {
                        continue;
                    }
                }
                if (!empty($plugin->new_version))
                {
                    if (isset($plugin->package))
                    {
                        // Filter and validate download URL
                        $plugin->package = trim(html_entity_decode($plugin->package));
                        if (filter_var($plugin->package, FILTER_VALIDATE_URL) === false)
                        {
                            $plugin->package = '';
                        }
                        else
                        {
                            // Skip forbidden hosts
                            $host = parse_url($plugin->package, PHP_URL_HOST);
                            if (in_array($host, $forbidden_hosts))
                            {
                                continue;
                            }
                        }
                    }
                    else
                    {
                        $plugin->package = '';
                    }

                    $updates[$slug . '_plugin'] = array(
                        'version'         => $plugin->new_version,
                        'download_url'    => $plugin->package,
                        'cms_version_max' => !empty($plugin->tested) ? $plugin->tested : null
                    );
                }
            }
        }

        if (!empty($themes->response))
        {
            foreach ($themes->response as $slug => $theme)
            {
                if (!is_object($theme))
                {
                    if (is_array($theme))
                    {
                        $theme = (object) $theme;
                    }
                    else
                    {
                        continue;
                    }
                }
                if (!empty($theme->new_version))
                {
                    if (isset($theme->package))
                    {
                        // Filter and validate download URL
                        $theme->package = trim(html_entity_decode($theme->package));
                        if (filter_var($theme->package, FILTER_VALIDATE_URL) === false)
                        {
                            $theme->package = '';
                        }
                        else
                        {
                            // Skip forbidden hosts
                            $host = parse_url($theme->package, PHP_URL_HOST);
                            if (in_array($host, $forbidden_hosts))
                            {
                                continue;
                            }
                        }
                    }
                    else
                    {
                        $theme->package = '';
                    }

                    $updates[$slug . '_theme'] = array(
                        'version'         => $theme->new_version,
                        'download_url'    => $theme->package,
                        'cms_version_max' => !empty($theme->tested) ? $theme->tested : null
                    );
                }
            }
        }

        $translations = false;
        if (!empty($plugins->translations) || !empty($themes->translations))
        {
            $translations = true;
        }
        else
        {
            $core = get_site_transient('update_core');
            if (!empty($core->translations))
            {
                $translations = true;
            }
        }

        if ($translations)
        {
            $updates['core_language'] = array(
                'version'         => AUTOUPDATER_WP_VERSION . (substr_count(AUTOUPDATER_WP_VERSION, '.') === 1 ? '.0.1' : '.1'),
                'download_url'    => null,
                'cms_version_max' => AUTOUPDATER_WP_VERSION
            );
        }

        return $updates;
    }

    /**
     * @param array $plugin
     */
    protected function filterPluginPwebcontact(&$plugin)
    {
        // Get download ID
        $settings = get_option('pwebcontact_settings', array());
        if (!empty($settings['dlid']))
        {
            $plugin->update_servers = array('https://www.perfect-web.co/index.php?option=com_ars&view=update&task=stream&format=json&id=8&dlid=' . trim($settings['dlid']));
        }

        // Fix name
        if (version_compare($plugin->version, '2.1.5', '<') &&
            strripos($plugin->name, ' PRO') === false &&
            file_exists(WP_PLUGIN_DIR . '/pwebcontact/uploader.php'))
        {
            $plugin->name = $plugin->name . ' PRO';
        }
    }

    /**
     * @param string $slug
     * @param string $type
     *
     * @return object|null
     */
    protected function checkForUpdates($slug, $type)
    {
        return isset($this->updates[$slug . '_' . $type]) ? $this->updates[$slug . '_' . $type] : null;
    }
}