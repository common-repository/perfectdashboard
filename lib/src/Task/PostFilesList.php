<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostFilesList extends AutoUpdater_Task_Base
{
    /**
     * @throws AutoUpdater_Exception_Response
     *
     * @return array
     */
    public function doTask()
    {
        $path = $this->getListPath();
        if (!$path)
        {
            throw new AutoUpdater_Exception_Response('Could not create a list file', 404);
        }

        $filemanager = AutoUpdater_Filemanager::getInstance();
        $exclusions  = array_merge(
            (array) $this->input('exclusions', array()),
            $this->getDefaultExclusions()
        );
        $files_count = 0;
        $content     = '<?php die(); ?>';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                AUTOUPDATER_SITE_PATH,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($files as $fileName)
        {
            // Remove double slashes and backslashes and convert all backslashes to slashes
            $fileName      = preg_replace('#[/\\\\]+#', '/', $fileName);
            $relative_path = substr_replace($fileName, '', 0, strlen(AUTOUPDATER_SITE_PATH));
            $relative_path = ltrim($relative_path, '/');
            // Don't include in file list excluded files/folders
            foreach ($exclusions as $exclusion)
            {
                if (!empty($exclusion) && strpos($relative_path, $exclusion) === 0)
                {
                    continue 2;
                }
            }
            ++$files_count;
            $content .= "\n" . $relative_path;
        }

        if (!$files_count)
        {
            return array(
                'success' => false,
                'message' => 'No file has been found',
            );
        }

        $filemanager->put_contents($path, $content);

        return array(
            'success'     => true,
            'files_count' => $files_count,
            'return'      => array(
                'files_list_path' => $filemanager->trimPath($path),
            ),
        );
    }

    /**
     * Determine a writable path for the temporary file list
     *
     * @return  mixed
     */
    protected function getListPath()
    {
        $filemanager = AutoUpdater_Filemanager::getInstance();
        if ($path = $filemanager->getTempPath())
        {
            //Add a random suffix so that if this lands in the site root or otherwise publicly accessible path,
            //it wouldn't be that easy to guess how to a complete list of files
            return $path . 'pd_fileslist' . $filemanager->getRandomName() . '.php';
        }

        return '';
    }

	/**
	 * Get an array of realative paths to exclude
	 *
	 * @return array
	 */
	protected function getDefaultExclusions()
	{
		return array(
			'.git',
			'.svn',
			'cache',
			'logs',
			'tmp',
			'temp',
			'autoupdater_backup_',
			'autoupdater_oldfiles',
			'perfectdashboard_backup_',
			'perfectdashboard_oldfiles',
			'cgi-bin',
		);
	}
}