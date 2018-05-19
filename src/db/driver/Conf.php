<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/13
 * Time: 00:25
 */

namespace FuPdo\driver;


class Conf
{
    /**
     * @var array
     *
    //        [
    //            "default"=>[
    //                "dbType"=>"Mysql",
    //                "mysqlAttrInitCommand"=>"SET NAMES UTF8",
    //                "read"=>[
    //                    "host"=>"127.0.0.1",
    //                    "port"=>"3306",
    //                    "user"=>"root",
    //                    "password"=>"",
    //                    "dbname"=>"test"
    //                ],
    //                "write"=>[
    //                    "host"=>"127.0.0.1",
    //                    "port"=>"3306",
    //                    "user"=>"root",
    //                    "password"=>"",
    //                    "dbname"=>"test"
    //                ],
    //            ],
    //        ];
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

//        [
//            "dbType"=>"Mysql",
//            "default"=>[
//                "read"=>[
//                    "host"=>"127.0.0.1",
//                    "port"=>"3306",
//                    "user"=>"root",
//                    "password"=>"",
//                    "dbname"=>"test"
//                ],
//                "write"=>[
//                    "host"=>"127.0.0.1",
//                    "port"=>"3306",
//                    "user"=>"root",
//                    "password"=>"",
//                    "dbname"=>"test"
//                ],
//            ],
//        ];

    }

    /**
     * @return array
     */
    public static function GetConf()
    {
        return self::getInstance()->config;
    }


}