<?php

function p($data, $is_die=1){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if($is_die) die();
}
function vp($data, $is_die=1){
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    if($is_die) die();
}
define('JUSTIN_TIME', microtime(true));

function showRunSecond($no = 0){
    $ts = (microtime(true) - JUSTIN_TIME);
    $ts = $ts < 0.0000001 ? '< 0.0000001' : $ts;
    $ts = round($ts, 7);
    echo '--NO:'.$no.'----'.$ts.'s----<br>';
}