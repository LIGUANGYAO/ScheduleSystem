<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 20:42
 */
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//定义全局变量
define('APP_DEBUG',true);

define('APP_PATH','./Application/');

define('ROOT_PATH', dirname(__FILE__).'/');

define('__PUBLIC__', ROOT_PATH.'Public');

//配置跨域
$allow_origin = array(
    'http://localhost',
    'http://localhost:3000',
    'http://119.29.121.240',
    'http://119.29.77.37'
);
if(isset($_SERVER['HTTP_ORIGIN']) &&
    in_array($_SERVER['HTTP_ORIGIN'], $allow_origin)) {

    //配置信任的跨域来源
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    //配置允许发送认证信息 比如cookies（会话机制的前提）
    header('Access-Control-Allow-Credentials: true');
    //信任跨域有效期，秒为单位
    header('Access-Control-Max-Age: 120');
    //允许的自定义请求头
    header('Access-Control-Allow-Headers: x-request-with,content-type');
    //允许的请求方式
    header('Access-Control-Allow-Methods: GET, POST');
}

require './ThinkPHP/ThinkPHP.php';
















