<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Installer extends AutoUpdater_Installer
{
    protected $uninstalled = false;
    protected $old_slug    = 'perfect';
    protected $old_file    = '';
    protected $new_file    = '';

    public function __construct()
    {
        $this->old_slug .= 'dash';
        parent::__construct();

        register_activation_hook(AUTOUPDATER_WP_PLUGIN_FILE, array($this, 'install'));
        register_uninstall_hook(AUTOUPDATER_WP_PLUGIN_FILE, array('AutoUpdater_Cms_Wordpress_Installer', 'hookUninstall'));

        add_action('init', array($this, 'selfUpdate'), 0);

        $this->old_slug .= 'board';
        $this->old_file = WP_PLUGIN_DIR . '/' . $this->old_slug . '/' . $this->old_slug . '.php';
        $this->new_file = WP_PLUGIN_DIR . '/autoupdater/autoupdater.php';
    }

    /**
     * @return bool
     */
    public function install()
    {
        $result = true;

        // TODO 1.16.1 if exists the old and the new plugin then remove the old one during installation and update

        if (file_exists($this->old_file))
        {
            // TODO remove after migration of all clients
            $result = $this->migrateVersion1_13();
        }

        // CLI Tool < 1.0.6
        if (php_sapi_name() === 'cli' && !defined('CMSDETECTOR_VERSION'))
        {
            // Disable the encryption as the AES key won't be send to the Auto-Updater API
            AutoUpdater_Config::set('encryption', 0);
        }

        $result = parent::install() && $result;

        // Disable WordPress core automatic updates
        $this->changeWordPressAutomaticUpdates();

        return $result;
    }

    /**
     * @param bool $disable
     */
    protected function changeWordPressAutomaticUpdates($disable = true)
    {
        // setup file path
        $file        = ABSPATH . 'wp-config.php';
        $filemanager = AutoUpdater_Filemanager::getInstance();

        //check if file exists
        if (!$filemanager->exists($file))
        {
            return;
        }
        // grab content of that file
        $content = $filemanager->get_contents($file);

        $closing_php_position = strrpos($content, '?>');
        if ($closing_php_position !== false)
        {
            $content = substr_replace($content, '', $closing_php_position, strlen('?>'));
        }

        // search for automatic updater
        preg_match('/(?:define\s*\(\s*[\'"]AUTOMATIC_UPDATER_DISABLED[\'"]\s*,\s*)(false|true|1|0)(?:\s*\);)/i', $content, $match);

        // if $match empty we don't have this variable in file
        if (!empty($match))
        {
            if (($disable === true && ($match[1] === 'true' || $match[1] === '1')) ||
                ($disable === false && ($match[1] === 'false' || $match[1] === '0')))
            {
                return;
            }

            // modify this constans : )
            $content = str_replace($match[0],
                'define(\'AUTOMATIC_UPDATER_DISABLED\', ' . ($disable ? 'true' : 'false') . ');',
                $content
            );
        }
        else
        {
            // so lets create this constans : )
            $content = str_replace('/**#@-*/',
                'if (!defined(\'AUTOMATIC_UPDATER_DISABLED\')) define(\'AUTOMATIC_UPDATER_DISABLED\', ' . ($disable ? 'true' : 'false') . ');',
                $content
            );
        }

        // save it to file
        $filemanager->put_contents($file, $content . PHP_EOL);
    }

    /**
     * @param bool $self
     *
     * @return bool
     */
    public function uninstall($self = false)
    {
        // Do not run uninstaller if Auto-Updater is installed and this is another plugin
        if (file_exists($this->old_file) && file_exists($this->new_file))
        {
            return true;
        }

        // Make sure that it would not run twice with WP register_uninstall_hook
        if ($this->uninstalled)
        {
            return true;
        }
        $this->uninstalled = true;

        $result = parent::uninstall();

        // Enable WordPress core automatic updates
        $this->changeWordPressAutomaticUpdates(false);

        // Do not delete the plugin if the uninstaller was triggered by the back-end
        // because the plugin will be deleted by the WP core
        if ($self === false)
        {
            return $result;
        }

        if (is_plugin_active(AUTOUPDATER_WP_PLUGIN_SLUG))
        {
            deactivate_plugins(AUTOUPDATER_WP_PLUGIN_SLUG);
        }

        if (is_uninstallable_plugin(AUTOUPDATER_WP_PLUGIN_SLUG))
        {
            include_once ABSPATH . 'wp-admin/includes/file.php';
            if (delete_plugins(array(AUTOUPDATER_WP_PLUGIN_SLUG)) !== true)
            {
                return false;
            }
        }

        return $result;
    }

    public static function hookUninstall()
    {
        AutoUpdater_Installer::getInstance()->uninstall();
    }

    /**
     * TODO remove after migration of all clients
     * @return bool
     */
    protected function migrateVersion1_13()
    {
        $old_version = get_option($this->old_slug . '_version');

        AutoUpdater_Log::debug('Migrate the old plugin configuration');
        $options = array(
            'read_token',
            'write_token',
            'token_expires_at',
            'backuptool_dir',
            'whitelabel_name',
            'whitelabel_author',
            'whitelabel_child_page',
            'whitelabel_login_page'
        );
        foreach ($options as $option)
        {
            if ($value = get_option($this->old_slug . '_' . $option))
            {
                AutoUpdater_Config::set($option, $value);
            }
        }

        $options = array(
            'hide_child',
            'protect_child',
            'ssl_verify'
        );
        foreach ($options as $option)
        {
            if (($value = get_option($this->old_slug . '_' . $option, null)) !== null)
            {
                AutoUpdater_Config::set($option, $value);
            }
        }


        AutoUpdater_Log::debug('Remove the old plugin configuration');
        global $wpdb;

        $options = $wpdb->get_col('SELECT option_name'
            . ' FROM ' . $wpdb->options
            . ' WHERE option_name LIKE "' . $this->old_slug . '%"'
        );

        foreach ($options as $option)
        {
            delete_option($option);
        }


        if (file_exists($this->old_file))
        {
            // Check if Auto-Updater is installed
            if (file_exists($this->new_file))
            {
                AutoUpdater_Log::debug('Deactivate and remove the old plugin');

                deactivate_plugins(array(plugin_basename($this->old_file)));
                AutoUpdater_Filemanager::getInstance()
                    ->rmdir(dirname($this->old_file), true);
            }
            else
            {
                // It is a new installation, not an update
                return true;
            }
        }

        return parent::migrateVersion1_13(); // create the AES key
    }
}