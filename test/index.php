<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/20
 * Time: 01:57
 */

$config = [
    "club"=>[
        "dbType"=>"Mysql",
        "dbOptions"=>[
            'attrInitCommand'=>'SET NAMES UTF8'
        ],
        "read"=>[
            "host"=>"192.168.33.10",
            "port"=>"3306",
            "user"=>"root",
            "password"=>"",
            "dbname"=>"club"
        ],
        "write"=>[
            "host"=>"127.0.0.1",
            "port"=>"3306",
            "user"=>"root",
            "password"=>"",
            "dbname"=>"club"
        ],
    ],
];

require '../autoload.php';
require './Controller/User.php';
require './Model/User.php';
require './Helper.php';

\FuPdo\driver\Conf::InitConf($config);

$controller = new \test\Controller\UserController();

//$detail = $controller->getDetail();
//p($detail, 0);


//$id = $controller->doInsert();
//p($id,0);
//
//
$data = $controller->getList();
p($data, 0);

