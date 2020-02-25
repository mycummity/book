<?php

$re = db_select($link,'id,name,ctime','car');
// 判断是否存在数据，存在返回数据，不存在返回空数组（防止前端没有赋值，报错undefined）
if($re==false){
	$ajaxreturn = [];
}else{
	$ajaxreturn = [
		"msg"=> '获取成功',
		'code'=> 200,
		're'=> $re
	];
}
echo json_encode($ajaxreturn);