<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostSecurityaudit extends AutoUpdater_Task_PostSecurityaudit
{
    /**
     * @return array
     */
    public function doTask()
    {
        global $wpdb;

        $data = parent::doTask();

        $data['additional_data']['database_prefix'] = (int) ($wpdb->prefix != 'wp_');
        $data['additional_data']['database_user']   = (int) ($wpdb->dbuser != 'root');
        $data['additional_data']['debug_mode']      = (int) !(defined('WP_DEBUG') && WP_DEBUG);

        return $data;
    }

    /**
     * @return int
     */
    protected function isAdminUsernameNotUsed()
    {
        global $wpdb;

        $admin_user = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->users . ' WHERE user_login = "admin"');

        return (int) empty($admin_user);
    }

    /**
     * @return int
     */
    protected function isErrorReportingDisabled()
    {
        if (defined('WP_DEBUG'))
        {
            return WP_DEBUG ? 0 : 1;
        }

        if (defined('WP_DEBUG_DISPLAY'))
        {
            return WP_DEBUG_DISPLAY ? 0 : 1;
        }

        return parent::isErrorReportingDisabled();
    }

    /**
     * @return int
     */
    protected function isPopularPasswordNotUsed()
    {
        global $wpdb;
        $users_passwords = $wpdb->get_col('SELECT user_pass FROM ' . $wpdb->users);

        if (!empty($users_passwords))
        {
            foreach ($users_passwords as $user_password)
            {
                foreach ($this->popular_passwords as $popular_password)
                {
                    if (wp_check_password($popular_password, $user_password) === true)
                    {
                        return 0;
                    }
                }
            }
        }

        return 1;
    }

    /**
     * @return array
     */
    protected function getBackupFileUnprotectedDirectories()
    {
        $paths = parent::getBackupFileUnprotectedDirectories();

        $this->getBackupFileUnprotectedDirForAkeeba($paths);
        $this->getBackupFileUnprotectedDirForBackUpWordPress($paths);
        $this->getBackupFileUnprotectedDirForDuplicator($paths);
        $this->getBackupFileUnprotectedDirForUpdraftPlus($paths);
        $this->getBackupFileUnprotectedDirForXCloner($paths);

        return $paths;
    }

    /**
     * @param array $paths
     */
    protected function getBackupFileUnprotectedDirForAkeeba(&$paths)
    {
        if (version_compare(PHP_VERSION, '5.3', '<'))
        {
            return;
        }

        try
        {
            $akeeba_path     = WP_PLUGIN_DIR . '/akeebabackupwp';
            $akeeba_app_path = $akeeba_path . '/app';

            if (!file_exists($akeeba_app_path . '/defines.php') ||
                !file_exists($akeeba_app_path . '/Awf/Autoloader/Autoloader.php') ||
                !file_exists($akeeba_app_path . '/Solo/engine/Factory.php'))
            {
                return;
            }

            include_once $akeeba_app_path . '/Awf/Autoloader/Autoloader.php';

            // Add our app to the autoloader
            $class_autoloader = 'Awf\Autoloader\Autoloader';
            if (!class_exists($class_autoloader))
            {
                return;
            }

            $class_autoloader::getInstance()->addMap('Solo\\', array(
                $akeeba_path . '/helpers/Solo',
                $akeeba_app_path . '/Solo',
            ));

            // Load the platform defines
            if (!defined('APATH_BASE'))
            {
                include_once $akeeba_app_path . '/defines.php';
            }

            if (!defined('AKEEBAENGINE'))
            {
                define('AKEEBAENGINE', 1);
            }

            include_once $akeeba_app_path . '/Solo/engine/Factory.php';
            if (file_exists($akeeba_app_path . '/Solo/alice/factory.php'))
            {
                include_once $akeeba_app_path . '/Solo/alice/factory.php';
            }

            $class_application = 'Awf\Application\Application';
            $class_factory     = 'Akeeba\Engine\Factory';
            $class_platform    = 'Akeeba\Engine\Platform';
            if (!class_exists($class_application) || !class_exists($class_factory) || !class_exists($class_platform))
            {
                return;
            }

            $class_platform::addPlatform('Wordpress', $akeeba_path . '/helpers/Platform/Wordpress');
            $application = $class_application::getInstance('Solo');
            $application->initialise();
            $class_platform::getInstance()->load_configuration();
            $akeeba_engine_config   = $class_factory::getConfiguration();
            $backups_directory_path = $akeeba_engine_config->get('akeeba.basic.output_directory');

            if (!empty($backups_directory_path) && file_exists($backups_directory_path))
            {
                if ($this->hasRemoteAccess($backups_directory_path))
                {
                    $paths[] = $backups_directory_path;
                }
            }
        }
        catch (Exception $e)
        {
        }
    }

    /**
     * @param array $paths
     */
    protected function getBackupFileUnprotectedDirForBackUpWordPress(&$paths)
    {
        if (version_compare(PHP_VERSION, '5.3', '<'))
        {
            return;
        }

        try
        {
            $init_path = WP_PLUGIN_DIR . '/backupwordpress/classes/class-path.php';
            if (!file_exists($init_path))
            {
                return;
            }

            include_once $init_path;

            $class_name = 'HM\BackUpWordPress\Path';
            if (!class_exists($class_name))
            {
                return;
            }

            $backups_directory_path = $class_name::get_path();
            if (!empty($backups_directory_path) && file_exists($backups_directory_path))
            {
                if ($this->hasRemoteAccess($backups_directory_path))
                {
                    $paths[] = $backups_directory_path;
                }
            }
        }
        catch (Exception $e)
        {
        }
    }

    /**
     * @param array $paths
     */
    protected function getBackupFileUnprotectedDirForDuplicator(&$paths)
    {
        try
        {
            $duplicator_defines = WP_PLUGIN_DIR . '/duplicator/define.php';
            if (!file_exists($duplicator_defines))
            {
                return;
            }

            if (!defined('DUPLICATOR_SSDIR_PATH'))
            {
                include_once $duplicator_defines;
            }

            if (defined('DUPLICATOR_SSDIR_PATH') && file_exists(DUPLICATOR_SSDIR_PATH))
            {
                $backups_directory_path = DUPLICATOR_SSDIR_PATH;
                if ($this->hasRemoteAccess($backups_directory_path))
                {
                    $paths[] = $backups_directory_path;
                }
            }
        }
        catch (Exception $e)
        {
        }
    }

    /**
     * @param array $paths
     */
    protected function getBackupFileUnprotectedDirForUpdraftPlus(&$paths)
    {
        try
        {
            if (!file_exists(WP_PLUGIN_DIR . '/updraftplus/class-updraftplus.php') ||
                !file_exists(WP_PLUGIN_DIR . '/updraftplus/options.php'))
            {
                return;
            }

            if (!defined('UPDRAFTPLUS_DIR'))
            {
                define('UPDRAFTPLUS_DIR', 1);
            }

            include_once WP_PLUGIN_DIR . '/updraftplus/class-updraftplus.php';
            include_once WP_PLUGIN_DIR . '/updraftplus/options.php';

            if (!class_exists('UpdraftPlus'))
            {
                return;
            }
            $updraft_plus           = new UpdraftPlus();
            $backups_directory_path = $updraft_plus->backups_dir_location();

            if (!empty($backups_directory_path) && file_exists($backups_directory_path))
            {
                if ($this->hasRemoteAccess($backups_directory_path))
                {
                    $paths[] = $backups_directory_path;
                }
            }
        }
        catch (Exception $e)
        {
        }
    }

    /**
     * @param array $paths
     */
    protected function getBackupFileUnprotectedDirForXCloner(&$paths)
    {
        try
        {
            $config_file = WP_PLUGIN_DIR . '/xcloner-backup-and-restore/cloner.config.php';
            if (!file_exists($config_file))
            {
                return;
            }

            include_once $config_file;
            if (!isset($_CONFIG['backup_path']))
            {
                return;
            }

            // Taken from wp-content/plugins/xcloner-backup-and-restore/common.php
            $backups_dir = str_replace('//administrator', '/administrator', $_CONFIG['backup_path'] . '/administrator/backups');
            $backups_dir = str_replace('\\', '/', $backups_dir);

            $backups_directory_path = $backups_dir;
            if (file_exists($backups_directory_path))
            {
                if ($this->hasRemoteAccess($backups_directory_path))
                {
                    $paths[] = $backups_directory_path;
                }
            }
        }
        catch (Exception $e)
        {
        }
    }
}