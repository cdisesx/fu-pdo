<?php
namespace test\Model;

use fuPdo\mysql\Model;

class UserModel extends Model
{
    public static $Table = 'user';
    public static $Db = 'club';

    public static $SaveFields = [
        "user_id",
        "name",
        "update_at",
    ];
}