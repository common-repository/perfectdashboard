<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Db
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

        $class_name = AutoUpdater_Loader::loadClass('Db');

        static::$instance = new $class_name();

        return static::$instance;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return array(
            'name'     => null,
            'user'     => null,
            'password' => null,
            'host'     => null,
            'prefix'   => null,
            'driver'   => null,
        );
    }

    /**
     * @param string $sql
     *
     * @return null|bool
     */
    public function doQuery($sql)
    {
        return null;
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    public function doQueryWithResults($sql)
    {
        return array();
    }
}