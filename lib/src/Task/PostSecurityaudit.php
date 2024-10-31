<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostSecurityaudit extends AutoUpdater_Task_Base
{
    protected $popular_passwords = array('123456', 'password', '12345678', 'qwerty', '12345', '123456789', 'football',
                                         '1234', '1234567', 'baseball', 'welcome', '1234567890', 'abc123', '111111', '1qaz2wsx', 'dragon',
                                         'master', 'monkey', 'letmein', 'login', 'princess', 'qwertyuiop', 'solo', 'passw0rd', 'starwars',
    );

    protected $readme_files = array(
        'readme.txt',
        'README.txt',
        'readme.html',
        'README.html',
        'license.txt',
    );

    /**
     * @return array
     */
    public function doTask()
    {
        $data = AutoUpdater_Task::getInstance('GetEnvironment', $this->payload)
            ->doTask();

        $skip_passwords = (int) $this->input('skip_passwords', 0);

        $data['additional_data'] = array(
            'error_reporting'         => $this->isErrorReportingDisabled(),
            'expose_php'              => ini_get('expose_php') ? 0 : 1,
            'allow_url_include'       => ini_get('allow_url_include') ? 0 : 1,
            'database_prefix'         => null,
            'database_user'           => null,
            'debug_mode'              => null,
            'readme_file'             => $this->isReadmeFileRemoved(),
            'admin_user'              => $this->isAdminUsernameNotUsed(),
            'popular_password'        => $skip_passwords ? null : $this->isPopularPasswordNotUsed(),
            'backups_http_accessible' => $this->getBackupFileUnprotectedDirectories(),
        );

        return $data;
    }

    /**
     * @return null|int
     */
    protected function isAdminUsernameNotUsed()
    {
        return null;
    }

    /**
     * @return int
     */
    protected function isErrorReportingDisabled()
    {
        $statusInPHP        = ini_get('error_reporting');
        $statusInPHPdisplay = ini_get('display_errors');

        if ($statusInPHPdisplay == 0)
        {
            return 1;
        }
        elseif ($statusInPHPdisplay > 0 && $statusInPHP == 0)
        {
            return 1;
        }

        return 0;
    }

    /**
     * @return null|int
     */
    protected function isPopularPasswordNotUsed()
    {
        return null;
    }

    /**
     * @return int
     */
    protected function isReadmeFileRemoved()
    {
        foreach ($this->readme_files as $file)
        {
            if (file_exists(AUTOUPDATER_SITE_PATH . $file))
            {
                return 0;
            }
        }

        return 1;
    }

    /**
     * @return array
     */
    protected function getBackupFileUnprotectedDirectories()
    {
        $paths = array();

        $backuptool_dir = AutoUpdater_Backuptool::getInstance()->getDir();
        if ($backuptool_dir)
        {
            $backuptool_dir .= 'backups/';
            if (is_dir(AUTOUPDATER_SITE_PATH . $backuptool_dir))
            {
                if ($this->hasRemoteAccess($backuptool_dir))
                {
                    $paths[] = $backuptool_dir;
                }
            }
        }

        return $paths;
    }

    /**
     * @param $path The relative path to the site root
     *
     * @return bool
     */
    protected function hasRemoteAccess(&$path)
    {
        $root_path = rtrim(AUTOUPDATER_SITE_PATH, '/\\');
        $root_path = str_replace('\\', '/', $root_path);
        $path      = str_replace('\\', '/', $path);
        $path      = str_replace($root_path, '', $path);

        $site_url = AutoUpdater_Config::getSiteUrl();
        $response = AutoUpdater_Request::get($site_url . ltrim($path, '/'), null, null, 3);

        return ($response->code < 400);
    }
}