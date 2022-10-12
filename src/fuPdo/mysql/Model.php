<?php

namespace fuPdo\mysql;

class Model
{
    public static $Table = '';
    public static $Db = '';

    /**
     * @var array | bool
     */
    public static $SaveFields = true;

    public function __construct(){}

    public static function Builder()
    {
        $Class = get_called_class();
        return Builder::getInstance($Class::$Db, $Class::$Table, $Class::$SaveFields);
    }

}
