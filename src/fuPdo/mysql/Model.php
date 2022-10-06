<?php

namespace fuPdo\mysql;

class Model
{
    public function __construct(){}

    public static function Builder()
    {
        $Class = get_called_class();
        return Builder::getInstance($Class::$DB, $Class::$TABLE);
    }

}
