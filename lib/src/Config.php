<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Config
{
    protected static $host     = 'perfect';
    protected static $instance = null;

    /**
     * @return static
     */
    protected static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        $class_name = AutoUpdater_Loader::loadClass('Config');

        static::$instance = new $class_name();
        static::$host     .= 'dash' . 'board' . '.com';

        return static::$instance;
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if ($key == 'debug' && defined('AUTOUPDATER_DEBUG') && AUTOUPDATER_DEBUG)
        {
            return 1;
        }

        return static::getInstance()->getOption($key, $default);
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    protected function getOption($key, $default = null)
    {
        return $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function set($key, $value)
    {
        return static::getInstance()->setOption($key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    protected function setOption($key, $value)
    {
        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function remove($key)
    {
        return static::getInstance()->removeOption($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function removeOption($key)
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function removeAll()
    {
        return static::getInstance()->removeAllOptions();
    }

    /**
     * @return bool
     */
    protected function removeAllOptions()
    {
        return true;
    }

    /**
     * @return string
     */
    public static function getSiteUrl()
    {
        return rtrim(static::getInstance()->getOptionSiteUrl(), '/');
    }

    /**
     * @return string
     */
    protected function getOptionSiteUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public static function getSiteBackendUrl()
    {
        return rtrim(static::getInstance()->getOptionSiteBackendUrl(), '/');
    }

    /**
     * @return string
     */
    protected function getOptionSiteBackendUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public static function getSiteLanguage()
    {
        return static::getInstance()->getOptionSiteLanguage();
    }

    /**
     * @return string
     */
    protected function getOptionSiteLanguage()
    {
        return '';
    }

    /**
     * @return string
     */
    public static function getAutoUpdaterUrl()
    {
        // Callback syntax: "subdomain:port:protocol". Example: "app:443:https" or just "app"
        if (!defined('AUTOUPDATER_CALLBACK'))
        {
            return 'https://' . AUTOUPDATER_STAGE . '.' . static::$host . '/';
        }

        @list($subdomain, $port, $protocol) = explode(':', AUTOUPDATER_CALLBACK);

        return ($protocol == 'http' ? 'http' : 'https') . '://'
            . $subdomain . '.' . static::$host
            . ($port > 0 ? ':' . (int) $port : '')
            . '/';
    }

    /**
     * @param bool $force
     * @return bool
     * @throws AutoUpdater_Exception_Response
     */
    public static function loadAutoUpdaterConfigByApi($force = false)
    {
        return static::getInstance()->loadAutoUpdaterConfig($force);
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    protected function loadAutoUpdaterConfig($force = false)
    {
        if (!$this->getOption('site_id'))
        {
            return true;
        }

        if (!$force && !defined('AUTOUPDATER_DEBUG') && $this->getOption('config_cached', 0) > strtotime('-1 hour'))
        {
            return true;
        }

        $response = AutoUpdater_Request::api('get', 'autoupdater/settings');
        if ($response->code !== 200)
        {
            return false;
        }

        if (!isset($response->body->settings))
        {
            return false;
        }
        $settings = $response->body->settings;

        // Auto-Updater state
        if (isset($settings->autoupdater_available))
        {
            $this->setOption('autoupdater_available', (int) $settings->autoupdater_available);
        }
        if (isset($settings->autoupdater_enabled))
        {
            $this->setOption('autoupdater_enabled', (int) $settings->autoupdater_enabled);
        }


        // Updates settings
        if (isset($settings->update_cms))
        {
            $this->setOption('update_cms', (int) $settings->update_cms);
        }
        if (isset($settings->update_cms_stage))
        {
            $this->setOption('update_cms_stage', (string) $settings->update_cms_stage);
        }
        if (isset($settings->update_extensions))
        {
            $this->setOption('update_extensions', (int) $settings->update_extensions);
        }
        if (isset($settings->update_themes))
        {
            $this->setOption('update_themes', (int) $settings->update_themes);
        }
        if (isset($settings->excluded_extensions))
        {
            $excluded_extensions = array();
            if (is_array($settings->excluded_extensions))
            {
                foreach ($settings->excluded_extensions as $extension)
                {
                    if (!in_array($extension->type, array('theme', 'template')))
                    {
                        $excluded_extensions[] = $extension->type . '::' . $extension->slug;
                    }
                }
            }
            $this->setOption('excluded_extensions', $excluded_extensions);
        }
        if (isset($settings->excluded_themes))
        {
            $excluded_themes = array();
            if (is_array($settings->excluded_themes))
            {
                foreach ($settings->excluded_themes as $theme)
                {
                    if (in_array($theme->type, array('theme', 'template')))
                    {
                        $excluded_themes[] = $theme->type . '::' . $theme->slug;
                    }
                }
            }
            $this->setOption('excluded_themes', $excluded_themes);
        }
        if (isset($settings->time_of_day))
        {
            $this->setOption('time_of_day', (string) $settings->time_of_day);
        }


        // Email address to receive notification
        if (isset($settings->notification_end_user_email))
        {
            $this->setOption('notification_end_user_email', (string) $settings->notification_end_user_email);
        }

        if (isset($settings->notification_on_success))
        {
            $this->setOption('notification_on_success', (int) $settings->notification_on_success);
        }
        if (isset($settings-> notification_on_failure))
        {
            $this->setOption('notification_on_failure', (int) $settings->notification_on_failure);
        }

        // Notifications in CMS dashboard
        if (isset($settings->notification_unavailable_mode))
        {
            $this->setOption('notification_unavailable_mode', (string) $settings->notification_unavailable_mode);
        }
        if (isset($settings->notification_unavailable_template))
        {
            $this->setOption('notification_unavailable_template', (string) $settings->notification_unavailable_template);
        }
        if (isset($settings->notification_disabled_mode))
        {
            $this->setOption('notification_disabled_mode', (string) $settings->notification_disabled_mode);
        }
        if (isset($settings->notification_disabled_template))
        {
            $this->setOption('notification_disabled_template', (string) $settings->notification_disabled_template);
        }
        if (isset($settings->notification_enabled_mode))
        {
            $this->setOption('notification_enabled_mode', (string) $settings->notification_enabled_mode);
        }
        if (isset($settings->notification_enabled_template))
        {
            $this->setOption('notification_enabled_template', (string) $settings->notification_enabled_template);
        }


        // Plugin page view
        if (isset($settings->page_unavailable_template))
        {
            $this->setOption('page_unavailable_template', (string) $settings->page_unavailable_template);
        }
        if (isset($settings->page_disabled_template))
        {
            $this->setOption('page_disabled_template', (string) $settings->page_disabled_template);
        }
        if (isset($settings->page_enabled_template))
        {
            $this->setOption('page_enabled_template', (string) $settings->page_enabled_template);
        }


        // Save the time when settings were cached
        $this->setOption('config_cached', time());

        return true;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function saveAutoUpdaterConfigByApi($data)
    {
        return static::getInstance()->saveAutoUpdaterConfig($data);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function saveAutoUpdaterConfig($data)
    {
        if (!$this->getOption('site_id'))
        {
            return true;
        }

        $changed  = false;
        $settings = array();

        if ($this->getOption('autoupdater_available'))
        {
            $settings['language'] = $this->getOptionSiteLanguage();

            if (array_key_exists('autoupdater_enabled', $data))
            {
                $settings['autoupdater_enabled'] = (int) $data['autoupdater_enabled'];
                if ((int) $this->getOption('autoupdater_enabled') !== $settings['autoupdater_enabled'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('update_cms', $data))
            {
                $settings['update_cms'] = (int) $data['update_cms'];
                if ((int) $this->getOption('update_cms') !== $settings['update_cms'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('update_extensions', $data))
            {
                $settings['update_extensions'] = (int) $data['update_extensions'];
                if ((int) $this->getOption('update_extensions') !== $settings['update_extensions'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('update_themes', $data))
            {
                $settings['update_themes'] = (int) $data['update_themes'];
                if ((int) $this->getOption('update_themes') !== $settings['update_themes'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('time_of_day', $data))
            {
                $settings['time_of_day'] = (string) $data['time_of_day'];
                if ((string) $this->getOption('time_of_day') !== $settings['time_of_day'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('notification_end_user_email', $data))
            {
                $settings['notification_end_user_email'] = (string) $data['notification_end_user_email'];
                if ((string) $this->getOption('notification_end_user_email') !== $settings['notification_end_user_email'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('notification_on_success', $data))
            {
                $settings['notification_on_success'] = (int) $data['notification_on_success'];
                if ((int) $this->getOption('notification_on_success') !== $settings['notification_on_success'])
                {
                    $changed = true;
                }
            }
            if (array_key_exists('notification_on_failure', $data))
            {
                $settings['notification_on_failure'] = (int) $data['notification_on_failure'];
                if ((int) $this->getOption('notification_on_failure') !== $settings['notification_on_failure'])
                {
                    $changed = true;
                }
            }

            if (array_key_exists('excluded_extensions', $data))
            {
                $data['excluded_extensions'] = (array) $data['excluded_extensions'];
                $excluded_extensions         = (array) $this->getOption('excluded_extensions', array());

                // Check if number of selected items has change
                if (count($data['excluded_extensions']) !== count($excluded_extensions) ||
                    count($data['excluded_extensions']) !== count(array_unique(array_merge($excluded_extensions, $data['excluded_extensions']))))
                {
                    $changed = true;
                }
                unset($excluded_extensions);

                $settings['excluded_extensions'] = array();
                foreach ($data['excluded_extensions'] as $extension)
                {
                    list($type, $slug) = explode('::', $extension, 2);
                    $settings['excluded_extensions'][] = array('type' => $type, 'slug' => $slug);
                }
            }

            if (array_key_exists('excluded_themes', $data))
            {
                $data['excluded_themes'] = (array) $data['excluded_themes'];
                $excluded_themes         = (array) $this->getOption('excluded_themes', array());

                if (count($data['excluded_themes']) !== count($excluded_themes) ||
                    count($data['excluded_themes']) !== count(array_unique(array_merge($excluded_themes, $data['excluded_themes']))))
                {
                    $changed = true;

                }
                unset($excluded_themes);

                $settings['excluded_themes'] = array();
                foreach ($data['excluded_themes'] as $extension)
                {
                    list($type, $slug) = explode('::', $extension, 2);
                    $settings['excluded_themes'][] = array('type' => $type, 'slug' => $slug);
                }
            }
        }


        if (array_key_exists('read_token', $data))
        {
            $settings['read_token'] = (string) $data['read_token'];
            if ((string) $this->getOption('read_token') !== $settings['read_token'])
            {
                $changed = true;
            }
        }
        if (array_key_exists('write_token', $data))
        {
            $settings['write_token'] = (string) $data['write_token'];
            if ((string) $this->getOption('write_token') !== $settings['write_token'])
            {
                $changed = true;
            }
        }
        if (array_key_exists('aes_key', $data))
        {
            $settings['aes_key'] = (string) $data['aes_key'];
            if ((string) $this->getOption('aes_key') !== $settings['aes_key'])
            {
                $changed = true;
            }
        }


        if ($changed === false)
        {
            return true;
        }

        $response = AutoUpdater_Request::api('post', 'autoupdater/settings', $settings);
        if ($response->code === 200)
        {
            return true;
        }

        return false;
    }
}