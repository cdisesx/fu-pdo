<?php

namespace fuPdo\mysql;

use fuPdo\log\Base;

trait Log
{

    /**
     * @var Base
     */
    private $log = null;

    public function SetLog($log)
    {
        $this->log = $log;
    }

    public function PushLog($content)
    {
        if($this->log){
            $this->log->Push($content);
        }
    }

}