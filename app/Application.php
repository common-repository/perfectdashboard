<?php
function_exists('add_action') or die;

include_once AUTOUPDATER_WP_PLUGIN_PATH . 'app/Admin.php';
include_once AUTOUPDATER_WP_PLUGIN_PATH . 'app/Whitelabelling.php';

class AutoUpdater_WP_Application
{
    protected static $instance        = null;
    protected        $slug            = '';
    protected        $plugin_filename = '';

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        static::$instance = new AutoUpdater_WP_Application();

        return static::$instance;
    }

    public function __construct()
    {
        add_action('init', array($this, 'siteOffline'));
        add_action('plugins_loaded', array($this, 'loadLanguages'));
        add_filter('mod_rewrite_rules', array($this, 'setHtaccessRules'));

        AutoUpdater_WP_Whitelabelling::getInstance();
        AutoUpdater_WP_Admin::getInstance();
    }

    public function loadLanguages()
    {
        load_plugin_textdomain('autoupdater', false, 'autoupdater/lang');
    }

    /**
     * @param string $rules Rewrite rules formatted for .htaccess.
     *
     * @return string
     */
    public function setHtaccessRules($rules)
    {
        $backuptool_dir = AutoUpdater_Backuptool::getInstance()->getDir();
        if (empty($backuptool_dir))
        {
            return $rules;
        }
        $backuptool_rule = 'RewriteRule ^autoupdater_(backup_[a-zA-Z0-9]+|restore)/ - [L]';
        $lines           = explode("\n", $rules);

        // The Backup Tool rule already exists
        if (array_search($backuptool_rule, $lines) !== false)
        {
            return $rules;
        }

        // Add the Backup Tool rule before a rule we are searching for
        if (($index = array_search('RewriteRule ^index\.php$ - [L]', $lines)) !== false)
        {
            array_splice($lines, $index, 0, $backuptool_rule);
        }
        elseif (($index = array_search('</IfModule>', $lines)) !== false)
        {
            array_splice($lines, $index, 0, $backuptool_rule);
        }

        return implode("\n", $lines);
    }

    public function siteOffline()
    {
        global $pagenow;

        if (is_admin())
        {
            return;
        }

        $is_offline = (bool) AutoUpdater_Config::get('offline');
        if (!$is_offline && defined('SITE_OFFLINE') && SITE_OFFLINE)
        {
            $is_offline = true;
        }

        if ($is_offline && !current_user_can('edit_posts') && !in_array($pagenow, array('wp-login.php', 'wp-register.php')))
        {
            ob_start();
            include AUTOUPDATER_WP_PLUGIN_PATH . 'tmpl/offline.tmpl.php';
            $body = ob_get_clean();

            AutoUpdater_Response::getInstance()
                ->setCode(503)
                ->setMessage('Service Unavailable')
                ->setHeader('Retry-After', '3600')
                ->setBody($body)
                ->send();
        }
    }
}