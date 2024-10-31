<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Request extends AutoUpdater_Request
{
    protected function makeGetRequest($url, $data = null, $headers = null, $timeout = null)
    {
        if (is_array($data))
        {
            $query = array();
            foreach ($data as $key => $value)
            {
                $query[] = $key . '=' . urlencode($value);
            }

            if (!empty($query))
            {
                $url .= (strpos($url, '?') === false ? '?' : '&') . implode('&', $query);
            }
        }

        $args = array(
            'sslverify' => AutoUpdater_Config::get('ssl_verify', 0) ? true : false,
            'timeout'   => $timeout ? $timeout : static::$timeout
        );

        if (!empty($headers))
        {
            $args['headers'] = $headers;
        }

        AutoUpdater_Log::debug("GET $url\nArgs " . print_r($args, true));
        $result = wp_remote_get($url, $args);

        return AutoUpdater_Response::getInstance()
            ->bind($result);
    }

    protected function makePostRequest($url, $data = null, $headers = null, $timeout = null)
    {
        $args = array(
            'sslverify' => AutoUpdater_Config::get('ssl_verify', 0) ? true : false,
            'timeout'   => $timeout ? $timeout : static::$timeout
        );

        if (!empty($headers))
        {
            $args['headers'] = $headers;
        }

        if (!empty($data))
        {
            if (isset($headers['Content-Type']) &&
                strpos($headers['Content-Type'], 'application/json') !== false &&
                !is_scalar($data))
            {
                $args['body'] = json_encode($data);
            }
            else
            {
                $args['body'] = $data;
            }
        }

        AutoUpdater_Log::debug("POST $url\nArgs " . print_r($args, true));
        $result = wp_remote_post($url, $args);

        return AutoUpdater_Response::getInstance()
            ->bind($result);
    }
}