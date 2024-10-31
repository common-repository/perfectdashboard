<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostFileDownload extends AutoUpdater_Task_Base
{
    /**
     * @throws AutoUpdater_Exception_Response
     * @throws Exception
     *
     * @return array
     */
    public function doTask()
    {
        $url = $this->input('file_url');
        if (!$url)
        {
            throw new AutoUpdater_Exception_Response('Nothing to download', 400);
        }

        $filemanager = AutoUpdater_Filemanager::getInstance();
        $path        = $filemanager->download($url);

        return array(
            'success' => $path ? true : false,
            'return'  => array(
                'file_path' => $filemanager->trimPath($path),
            ),
        );
    }
}