<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Response extends AutoUpdater_Response
{
    /**
     * @param WP_Error|array $data
     *
     * @return $this
     */
    public function bind($data)
    {
        if (is_wp_error($data))
        {
            /** @var WP_Error $data */
            $this->code    = $data->get_error_code();
            $this->message = $data->get_error_message();
        }
        else
        {
            $this->code    = $data['response']['code'];
            $this->message = $data['response']['message'];
            $this->headers = $data['headers'];
            $this->body    = $data['body'];


            if (isset($this->headers['content-type']) &&
                strpos($this->headers['content-type'], 'application/json') !== false &&
                is_scalar($data['body']))
            {
                try
                {
                    $this->body = json_decode($data['body']);
                }
                catch (Exception $e)
                {

                }
            }
        }

        if (AutoUpdater_Config::get('debug'))
        {
            $response = get_object_vars($this);
            if (isset($this->headers['content-type']) &&
                strpos($this->headers['content-type'], 'application/json') !== 0 &&
                strpos($this->headers['content-type'], 'application/xml') !== 0 &&
                strpos($this->headers['content-type'], 'text/') !== 0
            )
            {
                // Do not log downloaded file content
                $response['body'] = 'Truncated...';
            }

            AutoUpdater_Log::debug('Response ' . print_r($response, true));
        }

        return $this;
    }
}