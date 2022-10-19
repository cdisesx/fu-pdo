<?php
namespace fuPdo\mysql;

class QueryField
{

    private static $ruleCase = [
        "id"=> [ "a.id" , "in" ],
        "name"=> [ "a.name" , '=', 'allowEmpty'],
    ];

    /**
     * 用于SqlCreator中 WhereFields的参数生成
     * @param $params
     * @param $rules
     * @param Error $error
     * @return array|false
     */
    public static function GetParams($params, $rules,Error &$error)
    {
        $queryFieldParams = [];
        foreach ($rules as $field => $rule) {
            $param = $params[$field] ?? '';

            $queryField = array_shift($rule);
            $queryAssign = '=';
            $queryOptions = [];
            if(!empty($rule)){
                $queryAssign = array_shift($rule);
            }
            if(!empty($rule)){
                foreach ($rule as $op) {
                    $queryOptions[$op] = true;
                }
            }

            if($queryOptions['required']??false && $param === ''){
                $error->setErrorMessage("{$field} is now allow empty");
                return false;
            }

            // 忽略空值
            if(!$queryOptions['allowEmpty']??false && empty($param)){
                continue;
            }

            $queryFieldParams[$queryField] = [$queryAssign, $param];
        }
        
        return $queryFieldParams;
    }
}