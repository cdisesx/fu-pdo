<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/13
 * Time: 01:00
 */

namespace FuPdo\mysql;

class Model
{
    public function __construct(){}

    public static function Builder()
    {
        $Class = get_called_class();
        return Builder::getInstance($Class::$DB, $Class::$TABLE);
    }

}
