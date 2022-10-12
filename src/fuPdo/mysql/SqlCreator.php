<?php

namespace fuPdo\mysql;

// 加入批量Insert
// 加入Update
// 日志

class SqlCreator
{
    /**
     * @var string 准备连接的库名
     */
    protected $db;

    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var bool | array
     */
    protected $saveFields = true;

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
     * @var bool
     */
    protected $emptyReturn = false;

    /**
     * @param $sql
     * @return $this
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @var array
     */
    protected $bindValues = [];

    /**
     * @param array $bindValues
     */
    public function setBindValues($bindValues)
    {
        $this->bindValues = $bindValues;
    }

    /**
     * @param $bindValues
     * @param $num
     */
    protected function addBindValues($bindValues, $num = null)
    {
        if (is_null($num)){
            $num = count($bindValues);
        }

        $i = 0;
        foreach ($bindValues as $v) {
            if ($i < $num){
                $this->bindValues[] = $v;
                $i++;
            }else{
                break;
            }
        }
    }

    /**
     * @var array ['id','name']
     */
    private $field = [];
    public function Field($field)
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
            $this->sql .= ' *';
        }else{
            $this->sql .= join(',', $this->field);
        }

    }

    /**
     * @var string 'user as u'
     */
    private $table = '';

    public function Table($table)
    {
        $this->table = $table;
        return $this;
    }

    protected function _initTableSql()
    {
        $this->sql .= ' '.$this->table;
    }

    /**
     * @var array
    //    [
    //        [
    //            'sql'=>'id = ? or name like ? ',
    //            'bind'=>[1, "afd"],
    //        ],
    //    ]
     */
    private $where = [];
    private $createWhere = false;

    /**
     * @param $sql
     * @param array|max $bindParams
     * @return $this
     */
    public function Where($sql, $bindParams = [])
    {
        if(!is_array($bindParams)){
            $bindParams = [$bindParams];
        }
        $this->createWhere = true;
        $this->where[] = [
            'sql'=>$sql,
            'bind'=>$bindParams
        ];
        return $this;
    }

    /**
     * @param $field
     * @param array $params
     * @return $this
     */
    public function WhereIn($field, $params = [])
    {
        if(!is_array($params)){
            $this->where("$field = ?", $params);
        }
        $paramsLen = count($params);
        if($paramsLen == 0){
            $this->emptyReturn = true;
        }else if($paramsLen == 1){
            $this->where("$field = ?", $params[0]);
        }else{
            $pS = [];
            for($i=0;$i<$paramsLen;$i++){
                $pS[] = '?';
            }
            $this->where("$field = (". join($pS, ",") . ")", $params);
        }
        return $this;
    }

    protected function _initWhereSql()
    {
        if ($this->createWhere){
            $this->sql .= ' WHERE (' . join(') AND (', array_column($this->where, 'sql')) . ')';
            foreach ($this->where as $item) {
                $this->addBindValues($item['bind'], substr_count($item['sql'], '?'));
            }
        }
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
    public function LeftJoin($table, $on, $bindParams = [])
    {
        $this->createJoin = true;
        $this->join[] = [
            'type'=>'LEFT JOIN',
            'table'=>$table,
            'on'=>$on,
            'bind'=>$bindParams
        ];
        return $this;
    }

    /**
     * @param $table
     * @param $on
     * @param array $bindParams
     * @return $this
     */
    public function RightJoin($table, $on, $bindParams = [])
    {
        $this->createJoin = true;
        $this->join[] = [
            'type'=>'Right JOIN',
            'table'=>$table,
            'on'=>$on,
            'bind'=>$bindParams
        ];
        return $this;
    }

    /**
     * @param $table
     * @param $on
     * @param array $bindParams
     * @return $this
     */
    public function InnerJoin($table, $on, $bindParams = [])
    {
        $this->createJoin = true;
        $this->join[] = [
            'type'=>'INNER JOIN',
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
                $this->addBindValues($item['bind'], $bindNum);
                $this->sql .= " {$item['type']} {$item['table']} ON {$item['on']}";
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
    public function GroupBy($groupBy)
    {
        $this->createGroup = true;
        $this->group[] = addslashes($groupBy);
        return $this;
    }
    protected function _initGroupSql()
    {
        if ($this->createGroup){
            $this->sql .= ' GROUP BY ' . join(',', $this->group);
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
    public function Page($page, $pageSize)
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
            $this->sql .= " LIMIT {$this->offset},{$this->limit}";
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
    public function OrderBy($orderBy)
    {
        $this->createOrder = true;
        $this->order[] = addslashes($orderBy);
        return $this;
    }

    protected function _initOrderSql()
    {
        if ($this->createOrder){
            $this->sql .= ' ORDER BY ' . join(',', $this->order);
        }
    }

    /**
     * 生成 Select Sql
     */
    protected function CreateSelectSql()
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
    protected function CreateCountSql()
    {
        $this->bindValues = [];
        $this->sql = 'SELECT count(1) FROM';
        $this->_initTableSql();
        $this->_initJoinSql();
        $this->_initWhereSql();

        if ($this->createGroup){
            $this->_initGroupSql();
            $this->sql = 'SELECT count(1) FROM (' . $this->sql . ')';
        }
    }

    /**
     * 通过Model中的saveFields,过滤要保存的数据
     * @param $params
     * @return array
     */
    protected function FilterSaveFields($params)
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
    protected function CreateInsertSql($params)
    {
        $params = $this->FilterSaveFields($params);
        if(($len = count($params)) > 0){
            $this->bindValues = [];
            $bindValues = array_values($params);
            $this->field = [];
            $this->field(array_keys($params));
            $this->addBindValues($bindValues, $len);

            $bindArr = [];
            for ($i = 0; $i<$len; $i++){
                $bindArr[] = '?';
            }

            $this->sql = 'INSERT INTO ';
            $this->_initTableSql();

            $this->sql .= ' (';
            $this->_initFieldSql();
            $this->sql .= ') VALUES (' . join(',', $bindArr) . ') ';
        }
    }

    /**
     * 生成UpdateSql Params、where 必须必须有值
     * @param array $params
     */
    protected function CreateUpdateSql($params)
    {
        $params = $this->FilterSaveFields($params);
        if(count($this->where) > 0 && ($len = count($params)) > 0){
            $this->bindValues = [];
            $bindValues = array_values($params);
            $this->addBindValues($bindValues, $len);

            $bindArr = [];
            foreach ($params as $field=>$v) {
                $bindArr[] = "{$field} = ?";
            }

            $this->Field($bindArr);

            $this->sql = 'UPDATE ';
            $this->_initTableSql();

            $this->sql .= ' SET ';
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
    public function CreateRealSql()
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