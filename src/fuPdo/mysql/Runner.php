<?php

namespace fuPdo\mysql;


use PDO;
use Exception;
use fuPdo\driver\Connect;

class Runner
{
    public static function getInstance()
    {
        return new self();
    }

    /**
     * @var int
     */
    private $runType;
    const RunQuery = 1;
    const RunExec = 2;

    /**
     * @param mixed $runType RunQuery or RunExec
     * @return $this
     */
    public function setRunType($runType)
    {
        $this->runType = $runType;
        return $this;
    }

    /**
     * @var int
     */
    private $returnType;
    const ReturnOneField = 1;
    const ReturnOneRow = 2;
    const ReturnOneObjRow = 3;
    const ReturnArray = 4;
    const ReturnObj = 5;
    const ReturnEffectRow = 6;
    const ReturnLastInertID = 7;
    const ReturnRunResult = 8;

    /**
     * @param int $returnType
     * @return $this
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }


    protected $sql = '';
    protected $bindValues = [];

    /**
     * @param $bindValues
     * @return $this
     */
    public function setBindValues($bindValues)
    {
        $this->bindValues = $bindValues;
        return $this;
    }

    /**
     * @return array
     */
    public function getBindValues()
    {
        return $this->bindValues;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

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
     * @var PDO
     */
    protected $db;

    public function __construct(){}

    private $witchDB = 'default';

    /**
     * @param $witchDB
     * @return $this
     */
    public function setWitchDB($witchDB)
    {
        $this->witchDB = $witchDB;
        return $this;
    }

    /**
     * @return string
     */
    public function getWitchDB()
    {
        return $this->witchDB;
    }

    /**
     * @return int|mixed|null|string
     * @throws Exception
     */
    public function Run()
    {
        try{
            $this->connectDb();
            $result = $this->getResult();
            return $result;
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function Begin()
    {
        try{
            $this->connectDb();
            $this->db->beginTransaction();
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function Commit()
    {
        try{
            $this->connectDb();
            $this->db->commit();
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function RollBack()
    {
        try{
            $this->connectDb();
            $this->db->rollBack();
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function connectDb()
    {
        if ($this->runType == self::RunExec){
            $this->db = Connect::GetWriteDB($this->witchDB);
        }else{
            $this->db = Connect::GetReadDB($this->witchDB);
        }
    }

    /**
     * @return int|mixed|null|string
     * @throws Exception
     */
    private function getResult()
    {
        try{
            $sth = $this->db->prepare($this->sql);
            $sth->execute($this->bindValues);

            switch ($this->returnType){
                case self::ReturnOneField:
                    return $sth->fetchColumn();
                    break;
                case self::ReturnOneRow:
                    $sth->setFetchMode(PDO::FETCH_ASSOC);
                    return $sth->fetch();
                    break;
                case self::ReturnOneObjRow:
                    $sth->setFetchMode(PDO::FETCH_OBJ);
                    return $sth->fetch();
                    break;
                case self::ReturnArray:
                    $sth->setFetchMode(PDO::FETCH_ASSOC);
                    return $sth->fetchAll();
                    break;
                case self::ReturnObj:
                    $sth->setFetchMode(PDO::FETCH_OBJ);
                    return $sth->fetchAll();
                    break;
                case self::ReturnEffectRow:
                    return $sth->columnCount();
                    break;
                case self::ReturnLastInertID:
                    return $this->db->lastInsertId();
                    break;
                case self::ReturnRunResult:
                    if ($this->db->errorCode() === '00000'){
                        return true;
                    }else{
                        return false;
                    }
                    break;
                default:
                    return null;
            }
        }catch (Exception $e){
            throw $e;
        }
    }


}