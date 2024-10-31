<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Api extends AutoUpdater_Api
{
    protected function init()
    {
        parent::init();

        if (is_admin())
        {
            if (defined('DOING_AJAX'))
            {
                add_action('wp_ajax_autoupdater_api', array($this, 'handle'), 10);
                add_action('wp_ajax_nopriv_autoupdater_api', array($this, 'handle'), 10);
            }
        }
        else
        {
            $priority = (isset($_REQUEST['pd_endpoint']) && $_REQUEST['pd_endpoint'] === 'extension/update') ? 10 : 1;
            add_action('init', array($this, 'handle'), $priority);
        }
    }

    public function handle()
    {
        if (!$this->initialized)
        {
            return;
        }

        // set the en_US as a default
        load_default_textdomain('en_US');

        if (!AutoUpdater_Config::get('ssl_verify', 0))
        {
            add_filter('http_request_args', array($this, 'hookDisableSslVerification'), 10, 2);
        }

        return parent::handle();
    }

    /**
     * @param array  $r
     * @param string $url
     *
     * @return array
     */
    public function hookDisableSslVerification($r, $url)
    {
        $r['sslverify'] = false;

        return $r;
    }
}