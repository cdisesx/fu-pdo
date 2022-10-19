<?php

namespace fuPdo\mysql;

// 加入批量Insert
// 加入Update

class SqlCreator
{
    use Log;

    /**
     * @var string 准备连接的库名
     */
    protected $db;

    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var SqlBind
     */
    protected $sqlBind = null;

    /**
     * @var Where
     */
    protected $whereBilder = null;

    /**
     * @var bool | array
     */
    protected $saveFields = true;

    public function __construct()
    {
        $this->sqlBind = new SqlBind();
        $this->whereBilder = new Where();
    }

    public function getSqlBind()
    {
        return $this->sqlBind;
    }

    /**
     * @param $saveFields
     * @return $this
     */
    public function SetSaveFields($saveFields)
    {
        $this->saveFields = $saveFields;
        return $this;
    }

    /**
     * @var bool | string
     */
    protected $createField = false;
    /**
     * @return $this
     */
    public function SetCreateField($createField)
    {
        $this->createField = $createField;
        return $this;
    }

    /**
     * @var bool | string
     */
    protected $updateField = false;
    /**
     * @return $this
     */
    public function SetUpdateField($updateField)
    {
        $this->updateField = $updateField;
        return $this;
    }


    /**
     * @var string
     */
    protected $timeFormat = "Y-m-d H:i:s";
    /**
     * @return $this
     */
    public function SetTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
        return $this;
    }

    /**
     * @var bool
     */
    protected $emptyReturn = false;

    /**
     * @var array ['id','name']
     */
    private $field = [];
    public function field($field)
    {
        if (is_string($field)){
            $this->field[] = $field;
        }
        if (is_array($field)){
            $this->field = array_merge($this->field, $field);
        }
        return $this;
    }

    protected function _initFieldSql()
    {
        if (count($this->field) == 0){
            $this->sqlBind->addSql(' *');
        }else{
            $this->sqlBind->addSql(join(',', $this->field));
        }
    }

    /**
     * @var string 'user as u'
     */
    private $table = '';

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    protected function _initTableSql()
    {
        $this->sqlBind->addSql(' '.$this->table);
    }

    /**
     * @param $sql
     * @param array|max $bindParams
     * @return $this
     */
    public function where($sql, $bindParams = [], $logic = "and")
    {
        return $this->whereBilder->where($sql, $bindParams, $logic);
    }

    public function whereFields($params)
    {
        foreach ($params as $field=>$param) {
            if(!is_string($field)){
                if(is_string($params)){
                    $this->where($param);
                }
                continue;
            }

            if(is_string($param)){
                $this->whereAssign($field, "=", $param);
            }

            if(is_array($param)){
                $paramLen = count($param);
                if ($paramLen == 1){
                    $this->whereAssign($field, "=", $param[0]);
                }elseif ($paramLen > 1){
                    $this->whereAssign($field, $params[0], $param[1]);
                }
            }
        }
        return $this;
    }

    protected static $allowAssign = [
        "=",">","<","<=",">=","!=",
    ];

    protected static $allowMutlAssign = [
        "in","not int"
    ];

    protected static $allowSpecalAssign = [
        "keyword",
    ];

    /**
     * @param $field
     * @param array $params
     * @return $this
     */
    public function whereAssign($field, string $assign, $params, $logic = 'and')
    {
        if(empty($params)){
            $this->emptyReturn = true;
            return $this;
        }

        $newWhere = new Where();

        $assign = strtolower(trim(addslashes($assign)));
        $hasAllowAssign = false;
        if(in_array($assign, self::$allowAssign)){
            $hasAllowAssign = true;
            $this->where("{$field} {$assign} ?", $params, $logic);
        }

        if(in_array($assign, self::$allowMutlAssign)){
            $hasAllowAssign = true;
            if(!is_array($params)){
                $params = [$params];
            }

            if(count($params) == 1){
                switch ($assign){
                    case "in":
                        $this->where("{$field} = ?", $params[0], $logic);
                        break;
                    case "not in":
                        $this->where("{$field} != ?", $params[0], $logic);
                        break;
                }
            }else{
                $ps = $this->getPsSql($params);
                $this->where("{$field} {$assign} ($ps)", $params, $logic);
            }
        }

        if(in_array($assign, self::$allowSpecalAssign)) {
            $hasAllowAssign = true;

            switch ($assign){
                case "keyword":
                    $fieldList = explode(',', $field);
                    $keywordField = $fieldList[0];
                    $idField = $fieldList[1] ?? '';
                    if(is_array($params)){
                        $params = join(' ', $params);
                    }
                    $this->whereKeyword($keywordField, $idField, $params, $logic);
            }
        }

        if(!$hasAllowAssign){
            $this->emptyReturn = true;
        }
        return $this;
    }

    public function whereKeyword($keywordField, $idField, $word)
    {
        $searchWords = [];
        if(!is_array($word)){
            $this->emptyReturn = true;
            return $this;
        }
        $searchWords = explode(' ', $word);
        foreach ($searchWords as $k=>$searchWord) {
            $searchWord = trim($searchWord);
            if($this->emptyReturn === ''){
                unset($searchWords[$k]);
            }
        }

        if(empty($searchWords)){
            $this->emptyReturn = true;
            return $this;
        }

        $whereOr = [];
        $whereOrBindPrams = [];
        foreach ($searchWords as $searchWord) {
            $this->where("{$keywordField} like %?%", $searchWord, $logic);

            if(empty($idField)){
                continue;
            }
            $betweenKeyword = explode('~', $searchWord);
            if(count($betweenKeyword) == 2){
                $whereOr[] = "{$idField} between ? and ?";
                $whereOrBindPrams[] = $betweenKeyword[0];
                $whereOrBindPrams[] = $betweenKeyword[1];
            }else{
                $whereOr[] = "{$idField} = ?";
                $whereOrBindPrams[] = $searchWord;
            }
        }

        if(!empty($whereOr)){
            $orSql = '('.join(') or (', $whereOr).')';
            $this->where($orSql, $whereOrBindPrams);
        }

        return $this;
    }

    /**
     * @param $field
     * @param array $params
     * @return $this
     */
    public function whereIn($field, $params = [])
    {
        return $this->whereAssign($field, "in", $params);
    }

    public function getPsSql($params)
    {
        $pS = [];
        if(!is_array($params)){
            return '?';
        }

        $paramsLen = count($params);
        for($i=0;$i<$paramsLen;$i++){
            $pS[] = '?';
        }
        return join($pS, ",");
    }

    protected function _initWhereSql()
    {
        $this->sqlBind->mergeBind($this->whereBilder->getSqlBind(), ' WHERE ');
    }

    /**
     * @var array
    //    [
    //        [
    //            'type'=>'LEFT JOIN'
    //            'table'=>'goods as g',
    //            'on'=>'a.id = g.id and g.status = ?',
    //            'bind'=>[0],
    //        ],
    //    ]
     */
    private $join = [];
    private $createJoin = false;

    /**
     * @param $table
     * @param $on
     * @param array $bindParams
     * @return $this
     */
    public function join($table, $on, $bindParams = [], $logic = 'left')
    {
        $logic = strtoupper($logic);

        $this->createJoin = true;
        $this->join[] = [
            'type'=>"{$logic} JOIN",
            'table'=>$table,
            'on'=>$on,
            'bind'=>$bindParams
        ];
        return $this;
    }

    protected function _initJoinSql()
    {
        if ($this->createJoin){
            foreach ($this->join as $item) {
                $bindNum = substr_count($item['table'], '?') + substr_count($item['on'], '?');
                $this->sqlBind->addBindValues($item['bind'], $bindNum);
                $this->sqlBind->addSql(" {$item['type']} {$item['table']} ON {$item['on']}");
            }
        }
    }

    /**
     * @var array ['']
     */
    private $group = [];
    private $createGroup = false;

    /**
     * @param string $groupBy
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $this->createGroup = true;
        $this->group[] = addslashes($groupBy);
        return $this;
    }
    protected function _initGroupSql()
    {
        if ($this->createGroup){
            $this->sqlBind->addSql(' GROUP BY ' . join(',', $this->group));
        }
    }

    private $limit = 1;
    private $offset = 20;
    private $createLimit = false;

    /**
     * @param $page 当前页码
     * @param $pageSize 每页行数
     * @return $this
     */
    public function page($page, $pageSize)
    {
        $this->createLimit = true;
        $page = intval($page);
        $pageSize = intval($pageSize);
        $this->offset = max(0, ( ($page - 1) * $pageSize ));
        $this->limit = max(0, $pageSize);
        return $this;
    }

    protected function _initLimitSql()
    {
        if ($this->createLimit){
            $this->sqlBind->addSql(" LIMIT {$this->offset},{$this->limit}");
        }
    }

    /**
     * @var array ['id desc']
     */
    private $order = [];
    private $createOrder = false;

    /**
     * @param string $orderBy name asc id,create_time desc
     * @return $this
     */
    public function orderBy($orderBy)
    {
        $this->createOrder = true;
        $this->order[] = addslashes($orderBy);
        return $this;
    }

    protected function _initOrderSql()
    {
        if ($this->createOrder){
            $this->sqlBind->addSql(' ORDER BY ' . join(',', $this->order));
        }
    }

    /**
     * 生成 Select Sql
     */
    protected function createSelectSql()
    {
        $this->bindValues = [];
        $this->sql = 'SELECT ';
        $this->_initFieldSql();

        $this->sql .= ' FROM';
        $this->_initTableSql();

        $this->_initJoinSql();
        $this->_initWhereSql();
        $this->_initGroupSql();
        $this->_initOrderSql();
        $this->_initLimitSql();
    }

    /**
     * 生成 Select Sql
     */
    protected function createCountSql()
    {
        $this->bindValues = [];
        $this->sqlBind->setSql('SELECT count(1) FROM');
        $this->_initTableSql();
        $this->_initJoinSql();
        $this->_initWhereSql();

        if ($this->createGroup){
            $this->_initGroupSql();
            $this->sqlBind->setSql('SELECT count(1) FROM (' . $this->sqlBind->getSql() . ')');
        }
    }

    /**
     * 通过Model中的saveFields,过滤要保存的数据
     * @param $params
     * @return array
     */
    protected function filterSaveFields($params)
    {
        if($this->saveFields === true){
            return $params;
        }
        if(is_array($this->saveFields)){
            $saveFieldsMap = array_flip($this->saveFields);
            return array_intersect_key($params, $saveFieldsMap);
        }

        return [];
    }

    /**
     * 生成InsertSql Params 必须必须有值
     * @param array $params
     */
    protected function createInsertSql($params)
    {
        $params = $this->filterSaveFields($params);
        if(($len = count($params)) > 0){
            if($this->createField){
                $params[$this->createField] = date($this->timeFormat);
                $len++;
            }
            if($this->updateField){
                $params[$this->updateField] = date($this->timeFormat);
                $len++;
            }
            $this->sqlBind->setBindValues([]);
            $bindValues = array_values($params);
            $this->field = [];
            $this->field(array_keys($params));
            $this->sqlBind->addBindValues($bindValues, $len);

            $bindArr = [];
            for ($i = 0; $i<$len; $i++){
                $bindArr[] = '?';
            }

            $this->sqlBind->setSql('INSERT INTO ');
            $this->_initTableSql();

            $this->sqlBind->addSql(' (');
            $this->_initFieldSql();
            $this->sqlBind->addSql(') VALUES (' . join(',', $bindArr) . ') ');
        }
    }

    /**
     * 生成UpdateSql Params、where 必须必须有值
     * @param array $params
     */
    protected function createUpdateSql($params)
    {
        $params = $this->filterSaveFields($params);
        if(!$this->whereBilder->isEmptyWhere() && ($len = count($params)) > 0){
            if($this->updateField){
                $params[$this->updateField] = date($this->timeFormat);
                $len++;
            }

            $this->sqlBind->setBindValues([]);
            $bindValues = array_values($params);
            $this->sqlBind->addBindValues($bindValues, $len);

            $bindArr = [];
            foreach ($params as $field=>$v) {
                $bindArr[] = "{$field} = ?";
            }

            $this->Field($bindArr);

            $this->sqlBind->setSql('UPDATE ');
            $this->_initTableSql();

            $this->sqlBind->addSql(' SET ');
            $this->_initFieldSql();
            $this->_initWhereSql();
        }
    }

    /**
     * @return string
     */
    public function getSelectSql()
    {
        $this->CreateSelectSql();
        return $this->CreateRealSql();
    }

    /**
     * @param $params
     * @return string
     */
    public function getInsertSql($params)
    {
        $this->CreateInsertSql($params);
        return $this->CreateRealSql();
    }

    /**
     * @param $params
     * @return string
     */
    public function getUpdateSql($params)
    {
        $this->CreateUpdateSql($params);
        return $this->CreateRealSql();
    }

    /**
     * 将绑定参数带入Sql的?号当中
     * @return string
     */
    public function createRealSql()
    {
        $sqlArr = explode('?', ' '. $this->sql .' ');
        $realSql = '';

        $i = 1;
        $lenArr = count($sqlArr);
        foreach ($sqlArr as $k=>$v) {
            if(isset($this->bindValues[$k]) && $i != $lenArr){
                $bindVal = $this->bindValues[$k];
                if(is_string($bindVal)){
                    $bindVal = "'{$bindVal}'";
                }
                $realSql .= $v .$bindVal;
            }else{
                $realSql .= $v;
                break;
            }

            $i++;
        }
        return trim($realSql);
    }

}