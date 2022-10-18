<?php
namespace fuPdo\log;

class FileLog implements Base
{
    protected $dir = "";
    protected $file_name_sub = "";
    protected $switch = false;

    /**
     * @var array
     */
    static private $mapInstance = [];

    /**
     * @return Base
     */
    public static function getInstance(array $option)
    {
        $key = md5(json_encode($option));
        $unqObj = self::$mapInstance[$key] ?? null;
        if (!$unqObj instanceof self) {
            self::$mapInstance[$key] = new self($option);
        }

        return $unqObj;
    }

    private function __construct(array $option)
    {
        $this->dir = $option['dir'];
        $this->switch = $option['switch'] ?? false;
        $this->file_name_sub = $option['file_name_sub'] ?? "";
    }

    protected function getFile()
    {
        if(!$this->switch || empty($this->dir)){
            return false;
        }
        if(empty($this->file_name_sub)){
            $this->file_name_sub = "temp";
        }
        $file = $this->dir."/".$this->file_name_sub."_".date("Ymd").'.log';
        if(!file_exists($this->dir)){
            mkdir($this->dir, 0760, true);
        }

        return $file;
    }

    public function Push($content)
    {
        $file = $this->getFile();
        if(!$file){
            return false;
        }

        if(!is_string($content)){
            $content = json_encode($content);
        }
        $content = '['.date('Y-m-d H:i:s').'] - '.$content."\n";

        $openFile = fopen($file, 'a');
        fwrite($openFile, $content);
        fclose($openFile);
        return true;
    }
}