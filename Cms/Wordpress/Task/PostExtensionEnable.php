<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Task_PostExtensionEnable extends AutoUpdater_Task_PostExtensionEnable
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
            // Activate only given extensions
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
            // Activate all extensions
            $extensions = get_plugins();
            $plugins    = array_keys($extensions);
        }

        // Skip Auto-Updater extension
        if (($key = array_search(AUTOUPDATER_WP_PLUGIN_SLUG, $plugins)) !== false)
        {
            unset($plugins[$key]);
        }

        $result = activate_plugins($plugins);
        if (!is_wp_error($result))
        {
            return array(
                'success' => true,
            );
        }

        /** @var WP_Error $result */
        $data = array(
            'success' => false,
            'error'   => array(
                'code'    => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ),
        );

        if (count($plugins) > 1 && count($messages = $result->get_error_messages()) > 1)
        {
            $data['error']['messages'] = $messages;
        }

        return $data;
    }
}