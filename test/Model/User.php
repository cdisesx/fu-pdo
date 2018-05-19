<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/20
 * Time: 02:04
 */

namespace test\Model;

use FuPdo\mysql\Model;

class UserModel extends Model
{
    public static $TABLE = 'user';
    public static $DB = 'club';
}