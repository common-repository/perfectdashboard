<?php
function_exists('add_action') or die;

class AutoUpdater_WP_Admin
{
    protected static $instance  = null;
    protected        $menu_slug = 'autoupdater-config';

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        static::$instance = new AutoUpdater_WP_Admin();

        return static::$instance;
    }

    public function __construct()
    {
        if (is_admin())
        {
            if (defined('DOING_AJAX'))
            {
                add_action('wp_ajax_autoupdater_save', array($this, 'ajaxSaveConfiguration'));
                add_action('wp_ajax_autoupdater_notification_close', array($this, 'ajaxCloseNotification'));
            }
            else
            {
                $whitelabelling = AutoUpdater_WP_Whitelabelling::getInstance();
                if (!$whitelabelling->isPluginHidden())
                {
                    add_action('admin_init', array($this, 'addMediaFiles'));
                    add_action('admin_menu', array($this, 'addMenuEntry'));
                    if (AUTOUPDATER_WP_PLUGIN_BASENAME !== 'autoupdater')
                    {
                        add_action('admin_notices', array($this, 'displayConfigurationNotice'), 0);
                    }
                    else
                    {
                        // Admin dashboard page only
                        add_action('load-index.php', array($this, 'loadDashboardContent'), 0);
                    }
                }
            }
        }
    }

    /**
     * Add menu entry with plug-in settings page.
     */
    public function addMenuEntry()
    {
        $name = AutoUpdater_WP_Whitelabelling::getInstance()
            ->getWhiteLabeledName();
        if (empty($name) || $name == AUTOUPDATER_WP_PLUGIN_NAME)
        {
            add_menu_page(
                __(AUTOUPDATER_WP_PLUGIN_NAME, 'autoupdater'),
                __(AUTOUPDATER_WP_PLUGIN_NAME, 'autoupdater'),
                'manage_options',
                $this->menu_slug,
                array($this, 'displayConfigurationPage')
            );
        }
        else
        {
            add_submenu_page(
                'tools.php',
                $name,
                $name,
                'manage_options',
                $this->menu_slug,
                array($this, 'displayConfigurationPage')
            );
        }
    }

    public function addMediaFiles()
    {
        if (!isset($_GET['page']) || $_GET['page'] != $this->menu_slug)
        {
            return;
        }

        wp_register_style('autoupdater-style',
            plugins_url('media/css/style.css', AUTOUPDATER_WP_PLUGIN_FILE), array(), AUTOUPDATER_VERSION);
        wp_enqueue_style('autoupdater-style');

        wp_register_script('autoupdater-script',
            plugins_url('media/js/script.js', AUTOUPDATER_WP_PLUGIN_FILE), array('jquery'), AUTOUPDATER_VERSION);
        wp_enqueue_script('autoupdater-script');
    }

    public function displayConfigurationPage()
    {
        global $user_email;

        AutoUpdater_Config::loadAutoUpdaterConfigByApi();

        $autoupdater_available = AutoUpdater_Config::get('autoupdater_available');

        if ($autoupdater_available)
        {
            $autoupdater_enabled            = AutoUpdater_Config::get('autoupdater_enabled');
            $update_cms                     = AutoUpdater_Config::get('update_cms', 1);
            $update_cms_stage               = AutoUpdater_Config::get('update_cms_stage', 'newest_stable');
            $update_extensions              = AutoUpdater_Config::get('update_extensions', 1);
            $excluded_extensions_unprepared = AutoUpdater_Config::get('excluded_extensions');
            $update_themes                  = AutoUpdater_Config::get('update_themes', 0);
            $excluded_themes_unprepared     = AutoUpdater_Config::get('excluded_themes');
            $time_of_day                    = AutoUpdater_Config::get('time_of_day', 'night');
            $notification_end_user_email    = AutoUpdater_Config::get('notification_end_user_email');
            $notification_on_success        = AutoUpdater_Config::get('notification_on_success', 1);
            $notification_on_failure        = AutoUpdater_Config::get('notification_on_failure', 1);

            $excluded_extensions = array();

            if ($excluded_extensions_unprepared)
            {
                foreach ($excluded_extensions_unprepared as $ee)
                {
                    $type_slug = explode('::', $ee, 2);
                    if (!empty($type_slug[1]))
                    {
                        $excluded_extensions[$type_slug[1]] = true;
                    }
                }
            }

            $extensions_list_unprepared = get_plugins();
            $extensions_list            = array();

            foreach ($extensions_list_unprepared as $slug => $extension)
            {
                if ($slug !== AUTOUPDATER_WP_PLUGIN_SLUG)
                {
                    $extensions_list[$slug] = array(
                        'name'     => $extension['Name'],
                        'excluded' => !empty($excluded_extensions[$slug]),
                    );
                }
            }

            $extensions_list_count = count($extensions_list);

            $excluded_themes = array();

            if ($excluded_themes_unprepared)
            {
                foreach ($excluded_themes_unprepared as $et)
                {
                    $type_slug = explode('::', $et, 2);
                    if (!empty($type_slug[1]))
                    {
                        $excluded_themes[$type_slug[1]] = true;
                    }
                }
            }

            $themes_list_unprepared = version_compare(AUTOUPDATER_WP_VERSION, '3.4.0', '>=')
                ? wp_get_themes() : get_allowed_themes();
            $themes_list            = array();

            foreach ($themes_list_unprepared as $slug => $theme)
            {
                $legacy             = !($theme instanceof WP_Theme);
                $slug               = $legacy ? $theme['Template'] : pathinfo($slug, PATHINFO_FILENAME);
                $themes_list[$slug] = array(
                    'name'     => $legacy ? $theme['Name'] : $theme->get('Name'),
                    'excluded' => !empty($excluded_themes[$slug]),
                );
            }

            $themes_list_count = count($themes_list);

            $current_offset = get_option('gmt_offset');
            $offset         = $time_zone = get_option('timezone_string');

            if (strpos($time_zone, 'Etc/GMT') !== false)
            {
                $time_zone = null;
            }

            $date = date_create(null, new DateTimeZone('UTC'));
            $date->setTime(0, 0, 0);

            if (!$time_zone)
            {
                $offset = $time_zone = 'UTC';
                if ($current_offset < 0)
                {
                    $date->modify('-' . $current_offset . ' hours');
                    $offset .= '-' . $current_offset;
                }
                else
                {
                    $date->modify('+' . $current_offset . ' hours');
                    $offset .= '+' . $current_offset;
                }
            }

            $date->setTimezone(new DateTimeZone($time_zone));

            $time_of_day_options = array(
                'night'     => $date->format('H:i - ')
                    . $date->modify('+6 hours')->format('H:i ') . $offset,
                'morning'   => $date->format('H:i - ')
                    . $date->modify('+6 hours')->format('H:i ') . $offset,
                'afternoon' => $date->format('H:i - ')
                    . $date->modify('+6 hours')->format('H:i ') . $offset,
                'evening'   => $date->format('H:i - ')
                    . $date->modify('+6 hours')->format('H:i ') . $offset
            );

            $template_active   = $autoupdater_enabled ?
                AutoUpdater_Config::get('page_enabled_template') :
                $this->checkAutoupdaterEnableButtonPresence();
            $template_inactive = $autoupdater_enabled ?
                $this->checkAutoupdaterEnableButtonPresence() :
                AutoUpdater_Config::get('page_enabled_template');
        }
        else
        {
            $template_active = AutoUpdater_Config::get('page_unavailable_template');
        }

        $site_connected      = AutoUpdater_Config::get('ping');
        $white_labelling     = AutoUpdater_WP_Whitelabelling::getInstance();
        $white_labelled_name = $white_labelling->getWhiteLabeledName();
        $site_white_labelled = $white_labelled_name && $white_labelled_name !== AUTOUPDATER_WP_PLUGIN_NAME;

        $read_token  = AutoUpdater_Config::get('read_token');
        $write_token = AutoUpdater_Config::get('write_token');
        $aes_key     = AutoUpdater_Config::get('aes_key');
        $offline     = AutoUpdater_Config::get('offline', 0);
        $ssl_verify  = AutoUpdater_Config::get('ssl_verify', 0);
        $encryption  = AutoUpdater_Config::get('encryption', 1);
        $debug       = AutoUpdater_Config::get('debug', 0);
        $protect     = AutoUpdater_Config::get('protect_child', 0);

        require_once AUTOUPDATER_WP_PLUGIN_PATH . 'tmpl/' . AUTOUPDATER_WP_PLUGIN_BASENAME . '/configuration.tmpl.php';
    }

    private function checkAutoupdaterEnableButtonPresence()
    {
        $page_disabled_template = AutoUpdater_Config::get('page_disabled_template');
        if (strpos($page_disabled_template, 'autoupdater-enable') === false)
        {
            $page_disabled_template .= '<button type="button"  class="autoupdater-enable button button-primary">' .
                translate('Enable automatic updates', 'autoupdater') .
                '</button>';
        }

        return $page_disabled_template;
    }

    public function ajaxSaveConfiguration()
    {
        $response = AutoUpdater_Response::getInstance();
        $result   = check_ajax_referer('save-configuration');
        $protect  = AutoUpdater_Config::get('protect_child', 0);

        if (!$result)
        {
            $response->setCode(400)->send();
            return;
        }

        if (isset($_POST['offline']))
        {
            $offline = (int) $_POST['offline'] ? 1 : 0;
            $result  = AutoUpdater_Config::set('offline', $offline) && $result;
        }

        if (isset($_POST['ssl_verify']))
        {
            $ssl_verify = (int) $_POST['ssl_verify'] ? 1 : 0;
            $result     = AutoUpdater_Config::set('ssl_verify', $ssl_verify) && $result;
        }

        if (isset($_POST['encryption']))
        {
            $encryption = (int) $_POST['encryption'] ? 1 : 0;
            $result     = AutoUpdater_Config::set('encryption', $encryption) && $result;
        }

        if (isset($_POST['debug']))
        {
            $debug  = (int) $_POST['debug'] ? 1 : 0;
            $result = AutoUpdater_Config::set('debug', $debug) && $result;
        }

        if (!$result)
        {
            $response->setCode(400)->send();
            return;
        }

        if (!empty($_POST['notification_end_user_email'])
            && filter_var($_POST['notification_end_user_email'], FILTER_VALIDATE_EMAIL) === false) {
            $response->setCode(400)->setBody('error_email')->send();
            return;
        }

        $settings = array();
        if (AutoUpdater_Config::get('autoupdater_available'))
        {
            $settings = array(
                'autoupdater_enabled'         => isset($_POST['autoupdater_enabled']) ? $_POST['autoupdater_enabled'] : 1,
                'update_cms'                  => isset($_POST['update_cms']) ? $_POST['update_cms'] : 1,
                'update_extensions'           => isset($_POST['update_extensions']) ? $_POST['update_extensions'] : 1,
                'excluded_extensions'         => isset($_POST['excluded_extensions']) ? $_POST['excluded_extensions'] : array(),
                'update_themes'               => isset($_POST['update_themes']) ? $_POST['update_themes'] : 0,
                'excluded_themes'             => isset($_POST['excluded_themes']) ? $_POST['excluded_themes'] : array(),
                'time_of_day'                 => isset($_POST['time_of_day']) ? $_POST['time_of_day'] : 'night',
                'notification_end_user_email' => isset($_POST['notification_end_user_email']) ? $_POST['notification_end_user_email'] : '',
                'notification_on_success'     => isset($_POST['notification_on_success']) ? $_POST['notification_on_success'] : 1,
                'notification_on_failure'     => isset($_POST['notification_on_failure']) ? $_POST['notification_on_failure'] : 1,
            );
        }

        if (!$protect && isset($_POST['read_token']))
        {
            $settings['read_token'] = preg_replace('/[^a-z0-9]/i', '', $_POST['read_token']);
        }
        if (!$protect && isset($_POST['write_token']))
        {
            $settings['write_token'] = preg_replace('/[^a-z0-9]/i', '', $_POST['write_token']);
        }
        if (!$protect && isset($_POST['aes_key']))
        {
            $settings['aes_key'] = preg_replace('/[^a-z0-9]/i', '', $_POST['aes_key']);
        }

        $result = AutoUpdater_Config::saveAutoUpdaterConfigByApi($settings);
        foreach ($settings as $field_name => $field_value)
        {
            if (!$result)
            {
                break;
            }

            $result = AutoUpdater_Config::set($field_name, $field_value);
        }

        if (!$result)
        {
            $response->setCode(400);
        }
        $response->send();
    }

    public function displayConfigurationNotice()
    {
        global $hook_suffix, $user_email; // variable is used in the included template file bellow

        if ($hook_suffix != 'plugins.php' || AutoUpdater_Config::get('ping'))
        {
            return;
        }

        $plugins = get_option('active_plugins');
        $active  = false;
        foreach ($plugins as $i => $plugin)
        {
            if ($plugin == AUTOUPDATER_WP_PLUGIN_SLUG)
            {
                $active = true;
                break;
            }
        }
        if (!$active)
        {
            return;
        }

        include AUTOUPDATER_WP_PLUGIN_PATH . 'tmpl/' . AUTOUPDATER_WP_PLUGIN_BASENAME . '/connect_notice.tmpl.php';
    }

    public function loadDashboardContent()
    {
        add_action('admin_notices', array($this, 'displayNotification'), 0);
    }

    public function displayNotification()
    {
        $notification = AutoUpdater_Application::getInstance()->getNotification();

        if (empty($notification))
        {
            return;
        }

        $notification = str_replace('[AUTO_UPDATER_PLUGIN_CONFIG_URL]', admin_url('admin.php?page=' . $this->menu_slug), $notification);

        $url = admin_url('admin-ajax.php');

        include AUTOUPDATER_WP_PLUGIN_PATH . 'tmpl/' . AUTOUPDATER_WP_PLUGIN_BASENAME . '/notification.tmpl.php';
    }

    public function ajaxCloseNotification()
    {
        $result   = AutoUpdater_Application::getInstance()->closeNotification();
        $response = AutoUpdater_Response::getInstance();

        if ($result)
        {
            $response->setCode(200)->setMessage('OK');
        }
        else
        {
            $response->setCode(400)->setMessage('Bad Request');
        }
        $response->send();
    }
}