<?php
namespace fuPdo\mysql;

class SqlBind
{
    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var array
     */
    protected $bindValues = [];

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
     * @param $sql
     * @return $this
     */
    public function addSql($sql)
    {
        $this->sql .= $sql;
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
     * @param array $bindValues
     */
    public function setBindValues($bindValues)
    {
        $this->bindValues = $bindValues;
    }

    public function getBindValues()
    {
        return $this->bindValues;
    }

    /**
     * @param $bindValues
     * @param $num
     */
    public function addBindValues($bindValues, $num = null)
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
     * @param SqlBind $bind
     * @param string $sqlSub
     * @return SqlBind
     */
    public function mergeBind(sqlBind $bind, $sqlSub = " ")
    {
        if(!empty($bind->sql)){
            $this->sql .= $sqlSub.$bind->sql;
            $this->addBindValues($bind->getBindValues());
        }
        return $this;
    }

}