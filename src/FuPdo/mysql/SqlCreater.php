<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/14
 * Time: 09:54
 */

namespace FuPdo\mysql;

// 加入批量Insert
// 加入Update
// 日志

class SqlCreater
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
     * @var array
     */
    protected $bindValues = [];

    /**
     * @param $bindValues
     * @param $num
     */
    protected function addBindValues($bindValues, $num)
    {
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
     * @param array $bindParams
     * @return $this
     */
    public function Where($sql, $bindParams = [])
    {
        $this->createWhere = true;
        $this->where[] = [
            'sql'=>$sql,
            'bind'=>$bindParams
        ];
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
     * @param string $groupby
     * @return $this
     */
    public function GroupBy($groupby)
    {
        $this->createGroup = true;
        $this->group[] = addslashes($groupby);
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
     * @param string $orderby name asc id,create_time desc
     * @return $this
     */
    public function OrderBy($orderby)
    {
        $this->createOrder = true;
        $this->order[] = addslashes($orderby);
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
     * 生成InsertSql Params 必须必须有值
     * @param array $params
     */
    protected function CreateInserSql($params)
    {
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
    public function GetSelectSql()
    {
        $this->CreateSelectSql();
        return $this->CreateRealSql();
    }

    /**
     * @param $params
     * @return string
     */
    public function GetInsertSql($params)
    {
        $this->CreateInserSql($params);
        return $this->CreateRealSql();
    }

    /**
     * @param $params
     * @return string
     */
    public function GetUpdateSql($params)
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

        foreach ($sqlArr as $k=>$v) {
            if(isset($this->bindValues[$k])){
                $bindVal = $this->bindValues[$k];
                if(is_string($bindVal)){
                    $bindVal = "'{$bindVal}'";
                }
                $realSql .= $v .$bindVal;
            }else{
                $realSql .= $v;
            }
        }
        return trim($realSql);
    }
    
}