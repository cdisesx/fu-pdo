<?php
namespace fuPdo\mysql;

class SqlCreator
{
    use Log;
    use Field;

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
    protected $whereBuilder = null;

    public function __construct()
    {
        $this->whereBuilder = new Where();
    }

    public function getSqlBind()
    {
        if($this->sqlBind == null){
            $this->sqlBind = new SqlBind();
        }
        return $this->sqlBind;
    }

    public function NewSqlBind()
    {
        $this->sqlBind = new SqlBind();
        return $this->sqlBind;
    }

    public function field($field)
    {
        $this->appendSelectField($field);
        return $this;
    }

    protected function _initSelectFieldSql()
    {
        if (count($this->selectField) == 0){
            $this->getSqlBind()->addSql(" {$this->table}.*");
        }else{
            $this->getSqlBind()->addSql(join(',', $this->selectField));
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
        $this->getSqlBind()->addSql(' '.$this->table);
    }

    /**
     * @param $sql
     * @param array|max $bindParams
     * @param string $logic
     * @return $this
     */
    public function where($sql, $bindParams = [], $logic = "and")
    {
        $this->whereBuilder->where($sql, $bindParams, $logic);
        return $this;
    }

    /**
     * @param Where $where
     * @return $this
     */
    public function whereMerge(Where $where)
    {
        $this->whereBuilder->mergeWhere($where);
        return $this;
    }

    /**
     * @param $field
     * @param array $params
     * @return $this
     */
    public function whereIn($field, $params = [])
    {
        $where = Where::NewAssignWhere($field, "in", $params);
        $this->whereBuilder->mergeWhere($where);
        return $this;
    }

    protected function _initWhereSql()
    {
        $this->getSqlBind()->mergeBind($this->whereBuilder->getSqlBind(), ' WHERE ');
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
     * @param string $logic
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
                $this->getSqlBind()->addBindValues($item['bind'], $bindNum);
                $this->getSqlBind()->addSql(" {$item['type']} {$item['table']} ON {$item['on']}");
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
    public function groupBy(string $groupBy)
    {
        $this->createGroup = true;
        $this->group[] = addslashes($groupBy);
        return $this;
    }
    protected function _initGroupSql()
    {
        if ($this->createGroup){
            $this->getSqlBind()->addSql(' GROUP BY ' . join(',', $this->group));
        }
    }

    private $limit = 1;
    private $offset = 20;
    private $createLimit = false;

    /**
     * @param $page
     * @param $pageSize
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
            $this->getSqlBind()->addSql(" LIMIT {$this->offset},{$this->limit}");
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
    public function orderBy(string $orderBy)
    {
        $this->createOrder = true;
        $this->order[] = addslashes($orderBy);
        return $this;
    }

    protected function _initOrderSql()
    {
        if ($this->createOrder){
            $this->getSqlBind()->addSql(' ORDER BY ' . join(',', $this->order));
        }
    }

    /**
     * 生成 Select Sql
     */
    protected function createSelectSql()
    {
        $this->sqlBind->setBindValues([]);
        $this->sql = 'SELECT ';
        $this->_initSelectFieldSql();

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
        $this->sqlBind->setBindValues([]);
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
     * 生成InsertSql Params 必须必须有值
     * @param array $params
     */
    protected function createInsertSql(array $params)
    {
        $params = $this->filterSaveFields($params);
        if(($len = count($params)) > 0){
            if($this->createField){
                $params[$this->createField] = date($this->timeFormat);
            }
            if($this->updateField){
                $params[$this->updateField] = date($this->timeFormat);
            }

            $this->NewSqlBind()->setSql('INSERT INTO ');
            $this->_initTableSql();

            $insertFields = array_keys($params);
            $this->getSqlBind()->addSql(' ('. join(',', $insertFields).')');
            $this->getSqlBind()->addSql('VALUES ('. SqlBind::GetMarks($params).')');

            $bindValues = array_values($params);
            $this->getSqlBind()->addBindValues($bindValues);
        }
    }

    /**
     * 生成UpdateSql Params,where 必须必须有值
     * @param array $params
     */
    protected function createUpdateSql(array $params)
    {
        $params = $this->filterSaveFields($params);
        if(!$this->whereBuilder->isEmptyWhere() && ($len = count($params)) > 0){
            if($this->updateField){
                $params[$this->updateField] = date($this->timeFormat);
            }

            $this->NewSqlBind()->setSql('UPDATE ');
            $this->_initTableSql();
            $this->getSqlBind()->addSql(' SET ');

            $updateSql = [];
            $bindValues = [];
            foreach ($params as $field=>$v) {
                $updateSql[] = "{$field} = ?";
                $bindValues[] = $v;
            }
            $this->getSqlBind()->addSql(join(',', $updateSql));
            $this->getSqlBind()->addBindValues($bindValues);
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
        $bindValues = $this->getSqlBind()->getBindValues();
        foreach ($sqlArr as $k=>$v) {
            if(isset($bindValues[$k]) && $i != $lenArr){
                $bindVal = $bindValues[$k];
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