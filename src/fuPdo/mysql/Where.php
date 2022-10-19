<?php
namespace fuPdo\mysql;

class Where
{
    private $where = [];
    private $createWhere = false;
    private $whereOr = [];
    private $createWhereOr = false;
    private $emptyWhere = true;

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
                $this->createWhere = true;
                $this->where[] = [
                    'sql'=>$sql,
                    'bind'=>$bindParams
                ];
                break;
            case "or":
                $this->createWhereOr = true;
                $this->whereOr[] = [
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
        if ($this->createWhere){
            $this->mergeOrWhereSql();
            $this->sqlBind->setSql('(' . join(') AND (', array_column($this->where, 'sql')) . ')');
            $this->sqlBind->setBindValues([]);
            foreach ($this->where as $item) {
                $this->sqlBind->addBindValues($item['bind'], substr_count($item['sql'], '?'));
            }
        }

        return $this->sqlBind;
    }

    protected function mergeOrWhereSql()
    {
        if ($this->createWhereOr){
            $sql = '(' . join(') OR (', array_column($this->whereOr, 'sql')) . ')';
            $bindValues = [];
            foreach ($this->whereOr as $item) {
                $bindValues = array_merge($bindValues, $item['bind']);
            }
            $this->where($sql, $bindValues);
            $this->createWhereOr = false;
            $this->whereOr = [];
        }
        return $this;
    }

}