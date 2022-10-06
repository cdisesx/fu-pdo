<?php

namespace fuPdo\driver;


class Conf
{
    /**
     * @var array
     */
    private $config = [];

    private function __construct(){}

    /**
     * @var Conf
     */
    static private $instance;

    /**
     * @param $conf
     */
    private function setConf($conf)
    {
        $this->config = $conf;
    }

    /**
     * @return Conf
     */
    private static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $conf
     */
    public static function InitConf($conf)
    {
        self::getInstance()->setConf($conf);
    }

    /**
     * @return array
     */
    public static function GetConf()
    {
        return self::getInstance()->config;
    }


}