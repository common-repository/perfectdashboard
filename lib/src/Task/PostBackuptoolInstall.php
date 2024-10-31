<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostBackuptoolInstall extends AutoUpdater_Task_Base
{
    /**
     * @throws AutoUpdater_Exception_Response
     *
     * @return array
     */
    public function doTask()
    {
        if (AUTOUPDATER_STAGE != 'app')
        {
            // Get credentials from the request payload
            $dir             = $this->input('directory');
            $login           = $this->input('login');
            $password        = $this->input('password');
            $secret          = $this->input('secret');
            $max_backup_id   = $this->input('max_backup_id');
        }

        if (empty($dir) || empty($login) || empty($password) || empty($secret))
        {
            // Get credentials from Auto-Updater API
            $response = AutoUpdater_Request::api(
                'get',
                'backuptool/credentials',
                null,
                $this->input('site_id')
            );

            $dir             = !empty($response->body->directory) ? $response->body->directory : null;
            $login           = !empty($response->body->login) ? $response->body->login : null;
            $password        = !empty($response->body->password) ? $response->body->password : null;
            $secret          = !empty($response->body->secret) ? $response->body->secret : null;
            $max_backup_id   = !empty($response->body->max_backup_id) ? $response->body->max_backup_id : null;
        }

        if (empty($dir) || empty($login) || empty($password) || empty($secret))
        {
            throw new AutoUpdater_Exception_Response('Failed to get backup tool credentials', 400);
        }

        $options = array(
            'htaccess_disable' => (bool) $this->input('htaccess_disable', false),
            'backup_part_size' => (int) $this->input('backup_part_size', 0),
        );

        return AutoUpdater_Backuptool::getInstance()
            ->install($dir, $login, $password, $secret, $max_backup_id, $options, $this->input('backuptool_url', false));
    }
}