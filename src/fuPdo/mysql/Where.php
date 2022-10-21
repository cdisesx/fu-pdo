<?php
namespace fuPdo\mysql;

class Where
{
    private $and = [];
    private $createAnd = false;
    private $or = [];
    private $createOr = false;
    private $emptyWhere = true;
    public $emptyReturn = false;

    /**
     * @var SqlBind
     */
    private $sqlBind = null;

    public function __construct()
    {
        $this->sqlBind = new SqlBind();
    }

    /**
     * @param $sql
     * @param array|max $bindParams
     * @param string $logic
     * @return $this
     */
    public function where($sql, $bindParams = [], $logic = "and")
    {
        if(empty($sql)){
            return $this;
        }
        $this->emptyWhere = false;
        
        if(!is_array($bindParams)){
            $bindParams = [$bindParams];
        }
        $logic = strtolower($logic);
        switch ($logic){
            case "and":
                $this->createAnd = true;
                $this->and[] = [
                    'sql'=>$sql,
                    'bind'=>$bindParams
                ];
                break;
            case "or":
                $this->createOr = true;
                $this->or[] = [
                    'sql'=>$sql,
                    'bind'=>$bindParams
                ];
                break;
        }

        return $this;
    }

    public function isEmptyWhere()
    {
        return $this->emptyWhere;
    }

    public function getSqlBind()
    {
        $this->mergeOrWhereSql();
        if ($this->createAnd){
            $this->sqlBind->setSql('(' . join(') AND (', array_column($this->and, 'sql')) . ')');
            $this->sqlBind->setBindValues([]);
            foreach ($this->and as $item) {
                $this->sqlBind->addBindValues($item['bind'], substr_count($item['sql'], '?'));
            }
        }

        return $this->sqlBind;
    }

    protected function mergeOrWhereSql()
    {
        if ($this->createOr){
            $sql = '(' . join(') OR (', array_column($this->or, 'sql')) . ')';
            $bindValues = [];
            foreach ($this->or as $item) {
                $bindValues = array_merge($bindValues, $item['bind']);
            }
            $this->where($sql, $bindValues);
            $this->createOr = false;
            $this->or = [];
        }
        return $this;
    }

    public function mergeWhere(Where $where)
    {
        if($where->isEmptyWhere()){
            return $this;
        }
        $this->emptyReturn = ($this->emptyReturn || $where->emptyReturn);

        $where->mergeOrWhereSql();
        foreach ($where->and as $and) {
            $this->where($and['sql'], $and['bind']);
        }

        return $this;
    }

    public function mergeWhereOr(Where $where)
    {
        if($where->isEmptyWhere()){
            return $this;
        }
        if($where->emptyReturn){
            return $this;
        }

        $mergeBind = $where->getSqlBind();
        $this->where($mergeBind->getSql(), $mergeBind->getBindValues(), 'or');
        return $this;
    }

    protected static $allowAssign = [
        "=",">","<","<=",">=","!=",
    ];

    protected static $allowMultiAssign = [
        "in","not int"
    ];

    protected static $allowSpecialAssign = [
        "keyword",
    ];

    /**
     * @param $field
     * @param string $assign
     * @param $params
     * @return Where | bool
     */
    public static function NewAssignWhere($field, string $assign, $params)
    {
        $newWhere = new self();
        if(empty($params)){
            $newWhere->emptyReturn = true;
            return false;
        }

        $assign = strtolower(trim(addslashes($assign)));
        $hasAllowAssign = false;
        if(in_array($assign, self::$allowAssign)){
            $hasAllowAssign = true;
            $newWhere->where("{$field} {$assign} ?", $params);
        }

        if(in_array($assign, self::$allowMultiAssign)){
            $hasAllowAssign = true;
            if(!is_array($params)){
                $params = [$params];
            }

            if(count($params) == 1){
                switch ($assign){
                    case "in":
                        $newWhere->where("{$field} = ?", $params[0]);
                        break;
                    case "not in":
                        $newWhere->where("{$field} != ?", $params[0]);
                        break;
                }
            }else{
                $marks = SqlBind::GetMarks($params);
                $newWhere->where("{$field} {$assign} ($marks)", $params);
            }
        }

        if(in_array($assign, self::$allowSpecialAssign)) {
            $hasAllowAssign = true;

            switch ($assign){
                case "keyword":
                    $fieldList = explode(',', $field);
                    $keywordField = $fieldList[0];
                    $idField = $fieldList[1] ?? '';
                    if(is_array($params)){
                        $params = join(' ', $params);
                    }
                    $whereKeyword = self::NewKeywordWhere($keywordField, $idField, $params);
                    if($whereKeyword === false){
                        return false;
                    }
                    $newWhere->mergeWhere($whereKeyword);
            }
        }

        if(!$hasAllowAssign){
            $newWhere->emptyReturn = true;
        }

        return $newWhere;
    }

    public static function NewKeywordWhere($keywordField, $idField, $word)
    {
        if(!is_array($word)){
            return false;
        }

        $searchWords = explode(' ', strval($word));
        foreach ($searchWords as $k=>$searchWord) {
            $searchWord = trim($searchWord);
            if($searchWord === ''){
                unset($searchWords[$k]);
            }
        }
        if(empty($searchWords)){
            return false;
        }

        $newWhere = new self();
        foreach ($searchWords as $searchWord) {
            $newWhere->where("{$keywordField} like %?%", $searchWord, 'and');

            if(empty($idField)){
                continue;
            }
            $betweenKeyword = explode('~', $searchWord);
            if(count($betweenKeyword) == 2){
                $newWhere->where("{$idField} between ? and ?", $betweenKeyword, 'or');
            }else{
                $newWhere->where("{$idField} = ?", $searchWord, 'or');
            }
        }

        return $newWhere;
    }
}