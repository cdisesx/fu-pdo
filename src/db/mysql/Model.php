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
    protected static $DB;
    protected static $TABLE;

    public function __construct(){}

    public static function Builder()
    {
        return Builder::getInstance(self::$DB, self::$TABLE);
    }

}