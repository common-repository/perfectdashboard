<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Task_GetExtensions extends AutoUpdater_Task_Base
{
    /**
     * @return array
     */
    public function doTask()
    {
        if (($site_id = (int) $this->input('pd_site_id')))
        {
            AutoUpdater_Config::set('site_id', $site_id);
        }

        $all_extensions = filter_var($this->input('pd_all'), FILTER_VALIDATE_BOOLEAN);

        if ($refreshed_at = $this->skipRefresh($all_extensions)) {
            return array(
                'success' => true,
                'refreshed_at' => $refreshed_at,
            );
        }

        $extensions = $this->getExtensions();

        $environment = AutoUpdater_Task::getInstance('GetEnvironment', array(
            'refresh' => 1
        ))->doTask();

        // set defaults
        $checksum = sha1(json_encode($extensions));
        $checksum_cached = null;
        $diff = array(
            'changed' => $extensions,
            'deleted' => array()
        );

        // get the cache to compare with the current state
        $cache = $all_extensions ? json_encode($extensions) : AutoUpdater_Config::get('extensions_cache', null);

        if ($cache !== null && !$all_extensions)
        {
            // make checksum
            $checksum_cached = sha1($cache);

            $cache = json_decode($cache);

            // compare the extension array with the cache to get changed and removed extensions
            if (is_array($cache))
            {
                $diff = $this->diff($extensions, $cache);
            }
        }

        // save cache & checksum
        AutoUpdater_Config::set('extensions_cache', json_encode($extensions));

        // save time of last cache
        $now = new DateTime();
        AutoUpdater_Config::set('extensions_cached_at', $now->format('Y-m-d H:i:s'));

        return array(
            'success'     => true,
            'extensions'  => array_merge(
                $diff, array(
                    'checksum' => $checksum,
                    'checksum_cached' => $checksum_cached
                )
            ),
            'environment' => $environment
        );
    }

    /**
     * @param array $extensions
     * @param array $cache
     *
     * @return array
     */
    protected function diff($extensions, $cache)
    {
        $changed = array();

        foreach ($extensions as $extension)
        {
            // look for this extension in the cache
            $cache_key = $this->findExtensionInCache($extension->type, $extension->slug, $cache);

            // this is a newly installed extension
            if ($cache_key === false)
            {
                $changed[] = $extension;
                continue;
            }
            else
            {
                $cache_extension = $cache[$cache_key];
                unset($cache[$cache_key]);

                // the status of the extension has changed
                if (sha1(json_encode($extension)) != sha1(json_encode($cache_extension)))
                {
                    $changed[] = $extension;
                    continue;
                }
            }
        }

        return array(
            'changed' => $changed,
            'deleted' => array_values($cache)
        );
    }

    /**
     * @param string $type
     * @param string $slug
     * @param array $cache
     *
     * @return int|bool
     */
    protected function findExtensionInCache($type, $slug, $cache)
    {
        foreach ($cache as $i => $extension)
        {
            if ($extension->type == $type && $extension->slug == $slug)
            {
                return $i;
            }
        }

        return false;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function filterHTML($string)
    {
        return utf8_encode(trim(strip_tags(html_entity_decode($string))));
    }

    /**
     * @param $all_extensions
     *
     * @return bool|string
     */
    private function skipRefresh($all_extensions)
    {
        if (!defined('CMSDETECTOR_VERSION')) {
            return false;
        }

        $extensions_cached_at = AutoUpdater_Config::get('extensions_cached_at');

        if (!$extensions_cached_at || $all_extensions) {
            return false;
        }

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $extensions_cached_at = new DateTime($extensions_cached_at, new DateTimeZone('UTC'));
        $diff_in_hours = ($now->getTimestamp() - $extensions_cached_at->getTimestamp()) / 3600;

        if ($diff_in_hours > 25) {
                return false;
        }

        return $extensions_cached_at->format('Y-m-d H:i:s');
    }
}