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

    protected static $CreateField = 'create_at';
    protected static $UpdateField = 'update_at';
    protected static $timeFormat = 'Y-m-d H:i:s';

    public function __construct(){}

    /**
     * @return Builder
     */
    public static function Builder()
    {
        $Class = get_called_class();
        $builder = Builder::getInstance($Class::$Db, $Class::$Table);

        $builder->setSaveFields($Class::$SaveFields);
        $builder->createField = $Class::$CreateField;
        $builder->updateField = $Class::$UpdateField;
        $builder->timeFormat = $Class::$timeFormat;
        return $builder;
    }

    public static function GetSaveFields()
    {
        $Class = get_called_class();
        return $Class::$SaveFields;
    }
}
