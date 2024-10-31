<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Loader
{
    protected static $class_prefix = 'AutoUpdater_';
    protected static $loaded       = array();

    /**
     * @return string
     */
    public static function getClassPrefix()
    {
        return static::$class_prefix;
    }

    /**
     * @param string $name
     *
     * @return bool|string
     */
    public static function loadClass($name)
    {
        if (isset(static::$loaded[$name]))
        {
            return static::$loaded[$name];
        }

        $path = str_replace('_', '/', $name) . '.php';

        if (!class_exists(static::getClassPrefix() . $name))
        {
            if (!file_exists(AUTOUPDATER_LIB_PATH . $path))
            {
                return false;
            }

            include_once AUTOUPDATER_LIB_PATH . $path;

            if (!class_exists(static::getClassPrefix() . $name))
            {
                return false;
            }
        }

        static::$loaded[$name] = static::getClassPrefix() . $name;

        if (AUTOUPDATER_CMS)
        {
            $prefix = 'Cms_' . ucfirst(strtolower(AUTOUPDATER_CMS)) . '_';

            if (!class_exists(static::getClassPrefix() . $prefix . $name))
            {
                $path = dirname(dirname(AUTOUPDATER_LIB_PATH)) . '/Cms/' . ucfirst(strtolower(AUTOUPDATER_CMS)) . '/' . $path;

                if (file_exists($path))
                {
                    include_once $path;
                }
            }

            if (class_exists(static::getClassPrefix() . $prefix . $name))
            {
                static::$loaded[$name] = static::$loaded[$prefix . $name] = static::getClassPrefix() . $prefix . $name;
            }
        }

        return static::$loaded[$name];
    }
}