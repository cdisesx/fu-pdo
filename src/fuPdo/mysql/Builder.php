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
     * @var int
     */
    private $error_code = 0;

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param int $error_code
     */
    public function setErrorCode($error_code)
    {
        $this->error_code = $error_code;
    }

    /**
     * @var string
     */
    private $error_message = '';

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param string $error_message
     */
    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
    }

    /**
     * @return int|mixed|null|string
     */
    public function Find()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneRow);
    }

    /**
     * @return int|mixed|null|string
     */
    public function FindAsObj()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneObjRow);
    }

    /**
     * @return int|mixed|null|string
     */
    public function Select()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnArray);
    }

    /**
     * @param $sql
     * @param array $params
     * @return $this
     */
    public function Sql($sql, $params = [])
    {
        $this->setSql($sql);
        $this->setBindValues($params);
        return $this;
    }

    /**
     * @param $sql
     * @param $params
     * @return int|mixed|null|string
     */
    public function Query($sql, $params = [])
    {
        $this->setSql($sql);
        $this->setBindValues($params);
        return $this->getData(Runner::RunQuery, Runner::ReturnArray);
    }

    /**
     * @param $sql
     * @param $params
     * @return int|mixed|null|string
     */
    public function Exec($sql, $params = [])
    {
        $this->setSql($sql);
        $this->setBindValues($params);
        return $this->getData(Runner::RunExec, Runner::ReturnArray);
    }

    /**
     * @return int|mixed|null|string
     */
    public function SelectAsObj()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnObj);
    }

    /**
     * @return int|mixed|null|string
     */
    public function One()
    {
        $this->CreateSelectSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneField);
    }

    /**
     * @return int|mixed|null|string
     */
    public function Count()
    {
        $this->CreateCountSql();
        return $this->getData(Runner::RunQuery, Runner::ReturnOneField);
    }

    /**
     * @param $params
     * @return int|mixed|null|string
     */
    public function Insert($params)
    {
        $this->CreateInsertSql($params);
        return $this->getData(Runner::RunExec, Runner::ReturnLastInertID);
    }

    /**
     * @param $params
     * @return int|mixed|null|string
     */
    public function Update($params)
    {
        $this->CreateUpdateSql($params);
        return $this->getData(Runner::RunExec, Runner::ReturnRunResult);
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
                ->setSql($this->sql)
                ->setBindValues($this->bindValues)
                ->Run();
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
        return $data;
    }

    /**
     * @return $this
     */
    public function Begin()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->Begin();
            return $this;
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }

    /**
     * @return $this
     */
    public function Commit()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->Commit();
            return $this;
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }

    /**
     * @return $this
     */
    public function RollBack()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->RollBack();
            return $this;
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }

}