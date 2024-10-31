<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostExtensionDisable extends AutoUpdater_Task_PostExtensionDisable
{
    /**
     * @return array
     */
    public function doTask()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $extensions = (array) $this->input('extensions', array());
        $slug       = (string) $this->input('slug');

        $plugins = array();
        if (!empty($extensions))
        {
            // Deactivate only given extensions
            foreach ($extensions as $extension)
            {
                $plugins[] = $extension['slug'];
            }
        }
        elseif (!empty($slug))
        {
            $plugins[] = $slug;
        }
        else
        {
            // Deactivate all extensions
            $extensions = get_plugins();
            $plugins    = array_keys($extensions);
        }

        // Skip Auto-Updater extension
        if (($key = array_search(AUTOUPDATER_WP_PLUGIN_SLUG, $plugins)) !== false)
        {
            unset($plugins[$key]);
        }

        deactivate_plugins($plugins, true);

        return array(
            'success' => true,
        );
    }
}