<?php
namespace fuPdo\mysql;

class Searcher
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
    public static function GetParams($params, $rules, Error &$error)
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

    public static function GetWhere($searchParams)
    {
        $resWhere = new Where();

        foreach ($searchParams as $fieldList=>$param) {
            $fields = explode(',', $fieldList);
            $orWhere = new Where();
            foreach ($fields as $field) {
                $_w = true;
                if(is_string($param)){
                    $_w = Where::NewAssignWhere($field, '=', $param);
                }
                if(is_array($param)){
                    $paramLen = count($param);
                    if ($paramLen == 1){
                        $_w = Where::NewAssignWhere($field, "=", $param[0]);
                    }elseif ($paramLen > 1){
                        $_w = Where::NewAssignWhere($field, $param[0], $param[1]);
                    }
                }
                if($_w === true){
                    continue;
                }
                if($_w === false){
                    $resWhere->emptyReturn = true;
                }
                if($_w instanceof Where){
                    $orWhere->mergeWhereOr($_w);
                }
            }
            $resWhere->mergeWhere($orWhere);
        }

        return $resWhere;
    }

    public static function GetWhereByRule($params, $rule, Error &$error)
    {
        $searchParams = self::GetParams($params, $rule, $error);
        if($error->getErrorMessage()){
            return false;
        }
        return self::GetWhere($searchParams);
    }
}