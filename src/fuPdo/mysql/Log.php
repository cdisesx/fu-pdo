<?php

namespace fuPdo\mysql;

use fuPdo\log\Base;

trait Log
{

    /**
     * @var Base
     */
    private $log = null;

    public function setLog($log)
    {
        $this->log = $log;
    }

    public function pushLog($content)
    {
        if($this->log){
            $this->log->push($content);
        }
    }

}