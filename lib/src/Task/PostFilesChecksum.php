<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_PostFilesChecksum extends AutoUpdater_Task_Base
{
    /**
     * @throws AutoUpdater_Exception_Response
     *
     * @return array
     */
    public function doTask()
    {
        $startZeit = microtime(true); //Uuu, ja!
        if (function_exists('ini_get'))
        { //could be disabled for security reasons
            $maxZeit = ini_get('max_execution_time') * 6 / 7; //some time would be spent for processing the request, authorization, yada yada yada
            $maxZeit = $maxZeit ?: 120; //If there is no limit, let's say... do it in 120s chunks, just because I say so
        }
        else
        {
            //Aaaand pull a number out of our asses
            $maxZeit = 10;
        }
        // Let's not get crazy, though
        $maxZeit = min($maxZeit, 60);

        $files_list_path = $this->input('files_list_path');
        if (empty($files_list_path))
        {
            throw new AutoUpdater_Exception_Response('List file was not given', 400);
        }

        $filemanager = AutoUpdater_Filemanager::getInstance();
        if (!$filemanager->is_file($files_list_path))
        {
            $files_list_path = AUTOUPDATER_SITE_PATH . $files_list_path;
            if (!$filemanager->is_file($files_list_path))
            {
                throw new AutoUpdater_Exception_Response('List file was not found', 404);
            }
        }
        $files_list_path = realpath($files_list_path);

        /* Start from the second row, as the first one is '<?php die(); ?>' */
        $offset = (int) $this->input('offset', 1);
        $speed  = preg_replace('/[^a-z]/', '', $this->input('speed', 'fast'));
        $speed  = $this->textSpeedToBytesPerSecond($speed);

        $files   = array();
        $skipped = array();

        $index = new \SplFileObject($files_list_path);
        $index->seek($offset);
        while (!$index->eof() && ($leftZeit = $maxZeit - (microtime(true) - $startZeit)) > 0)
        {
            // Is this file small enough to be processed in time?
            $filename = trim($index->current());
            if (empty($filename) || !$filemanager->is_file(AUTOUPDATER_SITE_PATH . $filename))
            {
                $index->next();
                continue;
            }

            $filesize = $filemanager->size(AUTOUPDATER_SITE_PATH . $filename);
            // Let's dumbly estimate the read time
            if ($filesize / $speed > $leftZeit)
            {
                // Oops, we won't make it - are we at the start of the loop?
                if (!empty($files))
                {
                    // maybe we can make it if we start from that file
                    break;
                }
                else
                {
                    // skip it, there's no way we can ever make it
                    $skipped[] = array($filename, $filesize);
                    $index->next();
                    continue;
                }
            }
            $files[] = array($filename, md5_file(AUTOUPDATER_SITE_PATH . $filename));
            $index->next();
        }

        $result = array(
            'success' => true,
            'files'   => $files,
            'skipped' => $skipped,
        );

        //If we're done - delete the temporary file list cache
        if ($index->eof())
        {
            $filemanager->delete($files_list_path);
        }
        else
        {
            $result['return'] = array(
                'files_list_path' => $filemanager->trimPath($files_list_path),
                'offset'          => $index->key(),
            );
        }

        return $result;
    }

    /**
     * Transform a test speed setting into a value of estimated bytes that can be read in a second
     *
     * @param string $speed
     *
     * @return int
     */
    protected function textSpeedToBytesPerSecond($speed)
    {
        $MB = 1024 * 1024;
        switch ($speed)
        {
            case 'slowest':
                // A mother-fucking USB flash drive
                return 50 * $MB;
            case 'slow':
                return 100 * $MB;
            case 'normal':
                return 250 * $MB;
            case 'fast':
                return 600 * $MB;
            // Not really unlimited, can't load files instantly
            // let's assume a pretty fucking speedy disk though - like a Fibre Channel
            case 'unlimited':
            default:
                return 8096 * $MB;
        }
    }

}