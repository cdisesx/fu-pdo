<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/14
 * Time: 09:54
 */

namespace FuPdo\mysql;

class Builder extends SqlCreater
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
    public $error_code = 0;

    /**
     * @var string
     */
    public $error_message = '';

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
//        p($this->GetSelectSql());
        return $this->getData(Runner::RunQuery, Runner::ReturnArray);
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
        $this->CreateInserSql($params);
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

    public function Begin()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->Begin();
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }
    public function Commit()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->Commit();
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }
    public function RollBack()
    {
        try{
            $this->getRunner()->setRunType(Runner::RunExec)->RollBack();
        }catch (\Exception $e){
            $this->error_code = $e->getCode();
            $this->error_message= $e->getMessage();
        }
    }

}