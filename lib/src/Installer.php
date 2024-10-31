<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Installer
{
    protected static $instance = null;
    protected        $options  = array();

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        $class_name = AutoUpdater_Loader::loadClass('Installer');

        static::$instance = new $class_name();

        return static::$instance;
    }

    public function __construct()
    {

    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        if (isset($this->options[$key]))
        {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * @return bool
     */
    public function install()
    {
        AutoUpdater_Log::debug(sprintf('Installing Child %s', AUTOUPDATER_VERSION));

        if (!AutoUpdater_Config::get('version'))
        {
            AutoUpdater_Config::set('version', AUTOUPDATER_VERSION);
        }

        $this->createTokens();

        AutoUpdater_Log::debug(sprintf('Child %s has been installed.', AUTOUPDATER_VERSION));

        return true;
    }

    public function selfUpdate()
    {
        if (isset($_REQUEST['pd_endpoint']) && in_array($_REQUEST['pd_endpoint'], array('child/update/after', 'child/verify')))
        {
            // Do not run self-update as we are running it through API
            return;
        }

        $version = AutoUpdater_Config::get('version', '1.0');
        if (version_compare($version, AUTOUPDATER_VERSION, '<'))
        {
            AutoUpdater_Log::debug("Self update from version $version to " . AUTOUPDATER_VERSION);
            $this->update();
        }
    }

    /**
     * @return bool
     */
    public function update()
    {
        $current_version = AutoUpdater_Config::get('version', '1.0');
        $new_version     = $this->getOption('version', AUTOUPDATER_VERSION);
        AutoUpdater_Log::debug(sprintf('Updating Child from version %s to %s', $current_version, $new_version));

        if (version_compare($current_version, '1.16', '<'))
        {
            if (!$this->migrateVersion1_13())
            {
                return false;
            }
        }

        if (version_compare($current_version, $new_version, '<'))
        {
            AutoUpdater_Config::set('version', $new_version);
        }

        AutoUpdater_Log::debug(sprintf('Child has been updated from version %s to %s', $current_version, $new_version));

        return true;
    }

    /**
     * @return bool
     */
    protected function migrateVersion1_13()
    {
        $aes_key = AutoUpdater_Config::get('aes_key');
        if (!$aes_key)
        {
            AutoUpdater_Config::set('aes_key', $this->generateToken());
            if (!(php_sapi_name() === 'cli' && (defined('CMSDETECTOR_VERSION') || defined('WP_CLI'))))
            {
                // Disable the encryption as the AES key won't be send to the Auto-Updater API
                AutoUpdater_Config::set('encryption', 0);
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function createTokens()
    {
        if (!AutoUpdater_Config::get('read_token'))
        {
            AutoUpdater_Config::set('read_token', $this->generateToken());

            if (!AutoUpdater_Config::get('write_token'))
            {
                AutoUpdater_Config::set('write_token', $this->generateToken());
            }
        }
        if (!AutoUpdater_Config::get('aes_key'))
        {
            AutoUpdater_Config::set('aes_key', $this->generateToken());
        }

        return true;
    }

    /**
     * @return string
     */
    protected function generateToken()
    {
        $key   = '';
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max   = mb_strlen($chars, '8bit') - 1;
        for ($i = 0; $i < 32; ++$i)
        {
            $key .= $chars[random_int(0, $max)];
        }

        return $key;
    }

    /**
     * @param bool $self
     *
     * @return bool
     */
    public function uninstall($self = false)
    {
        AutoUpdater_Log::debug(sprintf('Uninstalling Child %s', AUTOUPDATER_VERSION));

        AutoUpdater_Backuptool::getInstance()
            ->uninstall();

        AutoUpdater_Config::removeAll();

        AutoUpdater_Log::debug(sprintf('Child %s has been uninstalled.', AUTOUPDATER_VERSION));

        return true;
    }
}