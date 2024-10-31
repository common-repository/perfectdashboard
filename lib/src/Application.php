<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Application
{
    protected static $instance = null;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        $class_name = AutoUpdater_Loader::loadClass('Application');

        static::$instance = new $class_name();

        return static::$instance;
    }

    /**
     * @return array|null
     */
    public function getNotification()
    {
        if (!AutoUpdater_Config::get('autoupdater_available'))
        {
            $key = 'unavailable';
        }
        elseif (AutoUpdater_Config::get('autoupdater_enabled'))
        {
            $key = 'enabled';
        }
        else
        {
            $key = 'disabled';
        }

        $mode    = AutoUpdater_Config::get('notification_' . $key . '_mode');
        $content = AutoUpdater_Config::get('notification_' . $key . '_template');

        if ($content && in_array($mode, array('closable', 'permanent')) &&
            ($mode == 'permanent' || !AutoUpdater_Config::get('notification_' . $key . '_closed')))
        {
            return array(
                'content'  => $content,
                'closable' => $mode == 'closable'
            );
        }

        return null;
    }

    /**
     * @return bool
     */
    public function closeNotification()
    {
        if (!AutoUpdater_Config::get('autoupdater_available'))
        {
            $key = 'unavailable';
        }
        elseif (AutoUpdater_Config::get('autoupdater_enabled'))
        {
            $key = 'enabled';
        }
        else
        {
            $key = 'disabled';
        }

        return AutoUpdater_Config::set('notification_' . $key . '_closed', time());
    }
}