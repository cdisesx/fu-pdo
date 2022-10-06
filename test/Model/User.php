<?php
namespace test\Model;

use fuPdo\mysql\Model;

class UserModel extends Model
{
    public static $TABLE = 'user';
    public static $DB = 'club';
}