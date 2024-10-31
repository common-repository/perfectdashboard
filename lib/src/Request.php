<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Request
{
    protected static $instance = null;
    protected static $timeout  = 5;

    /**
     * @return static
     */
    protected static function getInstance()
    {
        if (!is_null(static::$instance))
        {
            return static::$instance;
        }

        $class_name = AutoUpdater_Loader::loadClass('Request');

        static::$instance = new $class_name();

        return static::$instance;
    }

    /**
     * @param string     $url
     * @param null|array $data
     * @param null|array $headers
     * @param null|int   $timeout
     *
     * @return AutoUpdater_Response
     */
    public static function get($url, $data = null, $headers = null, $timeout = null)
    {
        return static::getInstance()->makeGetRequest($url, $data, $headers, $timeout);
    }

    /**
     * @param string            $url
     * @param null|array|string $data
     * @param null|array        $headers
     * @param null|int          $timeout
     *
     * @return AutoUpdater_Response
     *
     * @throws AutoUpdater_Exception_Response
     */
    public static function post($url, $data = null, $headers = null, $timeout = null)
    {
        return static::getInstance()->makePostRequest($url, $data, $headers, $timeout);
    }

    /**
     * @param string     $method
     * @param string     $endpoint The endpoint is always prefixed with /site/ID/ by this method
     * @param null|array $data
     * @param int        $site_id
     *
     * @return AutoUpdater_Response
     *
     * @throws AutoUpdater_Exception_Response
     */
    public static function api($method, $endpoint, $data = null, $site_id = 0)
    {
        if (!in_array($method, array('get', 'post')))
        {
            throw new AutoUpdater_Exception_Response(sprintf('Invalid request method: %s', $method), 400);
        }

        $site_id = (int) $site_id ? $site_id : AutoUpdater_Config::get('site_id');
        $query   = array();

        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if (strpos($key, 'pd_') !== 0)
                {
                    continue;
                }
                $query[$key] = $value;
                unset($data[$key]);
            }
        }

        // Sign with the read token
        $query['pd_signature'] = AutoUpdater_Authentication::getInstance()
            ->getSignature(empty($data) ? $query :
                array_merge($query, array('json' => json_encode($data))), 'get'
            );
        if (!$site_id || !$query['pd_signature'])
        {
            throw new AutoUpdater_Exception_Response('Missing required parameters', 400);
        }

        foreach ($query as $key => $value)
        {
            $query[$key] = $key . '=' . urlencode($value);
        }

        $url = AutoUpdater_Config::getAutoUpdaterUrl()
            . 'api/1.0/child/site/' . $site_id . '/'
            . trim($endpoint, '/')
            . '?' . implode('&', $query);

        return static::$method($url, $data, array(
            'Content-Type' => 'application/json',
        ));
    }

    /**
     * @param string     $url
     * @param null|array $data
     * @param null|array $headers
     * @param null|int   $timeout
     *
     * @return AutoUpdater_Response
     */
    protected function makeGetRequest($url, $data = null, $headers = null, $timeout = null)
    {
        return AutoUpdater_Response::getInstance();
    }

    /**
     * @param string            $url
     * @param null|array|string $data
     * @param null|array        $headers
     * @param null|int          $timeout
     *
     * @return AutoUpdater_Response
     */
    protected function makePostRequest($url, $data = null, $headers = null, $timeout = null)
    {
        return AutoUpdater_Response::getInstance();
    }
}