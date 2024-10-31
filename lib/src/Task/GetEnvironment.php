<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_GetEnvironment extends AutoUpdater_Task_Base
{
    /**
     * @return array
     */
    public function doTask()
    {
        $data = array(
            'success'          => true,
            'cms_type'         => AUTOUPDATER_CMS,
            'cms_version'      => null,
            'cms_language'     => AutoUpdater_Config::getSiteLanguage(),
            'php_version'      => php_sapi_name() !== 'cli' ? PHP_VERSION : '',
            'os'               => php_uname('s'),
            'server'           => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '',
            'database_name'    => null,
            'database_version' => null,
        );

        if (!$this->input('refresh'))
        {
            $data['success'] = true;
        }

        return $data;
    }
}