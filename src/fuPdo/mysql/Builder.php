<?php

namespace fuPdo\mysql;

class Builder extends SqlCreator
{

    /**
     * @param $db
     * @param $table
     * @return Builder
     */
    public static function getInstance($db, $table)
    {
        $builder = new self();
        $builder->db = $db;
        $builder->Table($table);
        return $builder;
    }

    /**
     * @var Runner
     */
    private $runner;

    /**
     * @return Runner
     */
    public function getRunner()
    {
        if (!($this->runner instanceof Runner)){
            $this->runner = Runner::getInstance()->setWitchDB($this->db);
        }
        return $this->runner;
    }

    /**
     * @var Error
     */
    public $error = null;

    /**
     * @param string $field
     * @return int|mixed|null|string
     */
    public function one($field = 'id')
    {
        $this->setSaveFields([$field]);
        $this->createSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneField);
    }

    /**
     * @return int|mixed|null|string
     */
    public function find()
    {
        $this->CreateSelectSql();
        p($this->getSqlBind()->getSql());
        return $this->getData(Runner::RunQuery, Runner::ReturnOneRow);
    }

    /**
     * @return int|mixed|null|string
     */
    public function findAsObj()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneObjRow);
    }

    /**
     * @return int|mixed|null|string
     */
    public function select()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnArray);
    }

    /**
     * @param $sql
     * @param array $params
     * @return $this
     */
    public function sql($sql, $params = [])
    {
        $this->getSqlBind()->setSql($sql);
        $this->getSqlBind()->setBindValues($params);
        return $this;
    }

    /**
     * @param $sql
     * @param $params
     * @return int|mixed|null|string
     */
    public function query($sql, $params = [])
    {
        $this->getSqlBind()->setSql($sql);
        $this->getSqlBind()->setBindValues($params);
        return $this->getData(Runner::RunQuery, Runner::ReturnArray);
    }

    /**
     * @param $sql
     * @param $params
     * @return int|mixed|null|string
     */
    public function exec($sql, $params = [])
    {
        $this->getSqlBind()->setSql($sql);
        $this->getSqlBind()->setBindValues($params);
        return $this->getData(Runner::RunExec, Runner::ReturnArray);
    }

    /**
     * @return int|mixed|null|string
     */
    public function selectAsObj()
    {
        $this->createSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnObj);
    }

    /**
     * @return int|mixed|null|string
     */
    public function count()
    {
        $this->createCountSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneField);
    }

    /**
     * @param $runType
     * @param $returnType
     * @return int|mixed|null|string
     */
    private function getData($runType, $returnType)
    {
        $data = null;
        try{
            $data = $this->getRunner()
                ->setRunType($runType)
                ->setReturnType($returnType)
                ->setSql($this->getSqlBind()->getSql())
                ->setBindValues($this->getSqlBind()->getBindValues())
                ->run();
        }catch (\Exception $e){
            $this->error = New Error($e);
        }
        return $data;
    }

    /**
     * @param $params
     * @return int|mixed|null|string
     */
    public function insert($params)
    {
        $this->createInsertSql($params);
        $this->pushLog($this-createRealSql());
        return $this->getData(Runner::RunExec, Runner::ReturnLastInertID);
    }

    /**
     * @param $params
     * @return int|mixed|null|string
     */
    public function update($params)
    {
        $this->createUpdateSql($params);
        $this->pushLog($this->createRealSql());
        return $this->getData(Runner::RunExec, Runner::ReturnRunResult);
    }

    /**
     * @return $this
     */
    public function begin()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->begin();
            return $this;
        }catch (\Exception $e){
            $this->error = New Error($e);
        }
    }

    /**
     * @return $this
     */
    public function commit()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->commit();
            return $this;
        }catch (\Exception $e){
            $this->error = New Error($e);
        }
    }

    /**
     * @return $this
     */
    public function rollBack()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->rollBack();
            return $this;
        }catch (\Exception $e){
            $this->error = New Error($e);
        }
    }

}