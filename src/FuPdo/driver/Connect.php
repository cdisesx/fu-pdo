<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/13
 * Time: 00:25
 */

namespace FuPdo\driver;


class Connect
{
    private function __construct()
    {
        $this->config = Conf::GetConf();
    }

    /**
     * @var Connect
     */
    static private $instance;

    /**
     * @return Connect
     */
    private static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array
     *
    //        [
    //            "default"=>[
    //                'read'=>\PDO,
    //                'write'=>null,
    //            ]
    //        ];
     */
    private $db_list;

    /**
     * @var array
     */
    private $config = [];

    private $host;
    private $port;
    private $user;
    private $password;
    private $dbname;
    private function initCurrentConfig($witchDB, $readOrWrite)
    {
        $this->host = $this->config[$witchDB][$readOrWrite]['host'];
        $this->port = $this->config[$witchDB][$readOrWrite]['port'];
        $this->user = $this->config[$witchDB][$readOrWrite]['user'];
        $this->password = $this->config[$witchDB][$readOrWrite]['password'];
        $this->dbname = $this->config[$witchDB][$readOrWrite]['dbname'];
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    public static function GetWriteDB($witchDB)
    {
        return self::getInstance()->getDb($witchDB, 'write');
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    public static function GetReadDB($witchDB)
    {
        return self::getInstance()->getDb($witchDB, 'read');
    }

    /**
     * @param $readOrWrite
     * @return \PDO
     * @throws \Exception
     */
    private function getDb($witchDB, $readOrWrite)
    {
        $db = null;
        if (isset($this->db_list[$witchDB]) && isset($this->db_list[$witchDB][$readOrWrite])){
            $db =  $this->db_list[$witchDB][$readOrWrite];
        };
        if(!($db instanceof \PDO)){
            $this->initCurrentConfig($witchDB, $readOrWrite);

            $callFunName = "get{$this->config[$witchDB]['dbType']}Dsn";
            $dns = $this->$callFunName();

            $callFunName = "get{$this->config[$witchDB]['dbType']}Options";
            $options = $this->$callFunName();

            $db = self::connentDB($dns, $this->user, $this->password, $options);
            $this->db_list[$witchDB][$readOrWrite] = $db;
        }

        return $db;
    }


    /**
     * Create ConnentDB
     * @param $dsn
     * @param $user
     * @param $password
     * @return \PDO
     * @throws \Exception
     */
    private static function connentDB($dsn, $user, $password)
    {
        try
        {
            $db = new \PDO($dsn, $user, $password);
            return $db;
        }
        catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 获取MysqlDsn
     * @return string
     */
    private function getMysqlDsn()
    {
        return "mysql:host={$this->host};dbname={$this->dbname};port={$this->port}";
    }

    /**
     * 获取MysqlOptions
     */
    private function getMysqlOptions($dbConf)
    {
        $options = array();
        if (isset($dbConf['mysqlAttrInitCommand'])){
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = $dbConf['mysqlAttrInitCommand'];
        }
        return $options;
    }

}