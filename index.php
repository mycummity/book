<?php
$config = include 'config/config.php';
include 'common/mysqli.php';

$model = 'index';
$action = 'index';
//允许ajax跨域
if($model=='api'){
	header('Access-Control-Allow-Origin:*');
}
// 检测get中变量是否设置，并且不是NULL
if(isset($_GET['m'])){
  $model = $_GET['m'];
}
if(isset($_GET['a'])){
  $action = $_GET['a'];
}
// 连接数据库
$link = db_connect($config['mysql']['host'],$config['mysql']['user'],$config['mysql']['password'],'xmr');
// 设置调用某文件的目录，如api/addcar.php
include $model.'/' . $action . '.php';
