<?php
defined('AUTOUPDATER_LIB') or die;

abstract class AutoUpdater_Task_Base
{
    /**
     * API request payload
     * @var array $payload
     */
    protected $payload;

    /**
     * Encrypt the task response if the website is not secured with TLS
     * @var bool
     */
    protected $encrypt = true;

    public function __construct($payload)
    {
        $this->payload = (array) $payload;
    }

    /**
     * Handle the task and return the data for the API response
     *
     * @throws Exception
     * @throws AutoUpdater_Exception_Response
     *
     * @return array
     */
    abstract public function doTask();

    public function getName()
    {
        $parts = explode('_', get_class($this));

        return end($parts);
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    protected function input($key, $default = null)
    {
        if (isset($this->payload[$key]))
        {
            return $this->payload[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setInput($key, $value)
    {
        $this->payload[$key] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEncryptionRequired()
    {
        return $this->encrypt;
    }
}