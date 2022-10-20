<?php

namespace fuPdo\mysql;

class Error
{

    public function __construct(\Exception $error)
    {
        $this->error_code = $error->getCode();
        $this->error_message= $error->getMessage();
    }

    /**
     * @var int
     */
    private $error_code = 0;

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param int $error_code
     */
    public function setErrorCode(int $error_code)
    {
        $this->error_code = $error_code;
    }

    /**
     * @var string
     */
    private $error_message = '';

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param string $error_message
     */
    public function setErrorMessage(string $error_message)
    {
        $this->error_message = $error_message;
    }

}