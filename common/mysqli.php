<?php
//定义俩个错误的全局变量
$error = $config['error'];
$errno = $config['errno'];
$debug = $config['debug'];

/*
*@desc 数据库连接函数
*@param $host string 数据库的主机地址
*@param $user string 数据库的用户名
*@param $pass string 数据库的密码
*@param $db_name string 数据库的数据库名称
*@param $port int 数据库的端口 默认3306
*@param $charset string 字符集 默认utf8
*@return false | $link  bool|object 连接失败|连接的资源
*/
function db_connect($host,$user,$pass,$db_name,$port=3306,$charset='utf8'){
    global $error;
    global $errno;

    //判断端口是否默认
    if($port!=3306){
        $host = $host . ':' . $port;
    }
    //1. 连接数据库
    $link = @mysqli_connect($host,$user,$pass);

    //2. 判断是否连接成功
    if($link===false){
        $error =  mysqli_connect_error($link);
        $errno =  mysqli_connect_errno($link);
        return false;
    }

    //3. 选择数据库
    $res = mysqli_select_db($link,$db_name);
    if($res===false){
        $error =  mysqli_error($link);
        $errno =  mysqli_errno($link);
        return false;
    }

    //4. 设置字符集
    $res = mysqli_set_charset($link,$charset);
    if($res===false){
        $error =  mysqli_error($link);
        $errno =  mysqli_errno($link);
        return false;
    }

    return $link;
}

/*
 *@desc 更新操作
 *@param $link object 连接的资源
 *@param $table string 数据表
 *@param $set mixd 要修改的数据数组/字符串
 *@param $where string 更新条件
 */
function db_update ($link, $table, $set, $where) {
    global $error;
    global $errno;

    if(!is_string($where)) {
        $error ='跟新条件语法不正确，必须是字符串格式';
        $errno = 1007;
        return false;
    }
    if(is_array($set)){
        $set = parse_array($set);
        $set = parset_set ($set);
    }
    //如果输入和字符串都不是的话，错误处理
    if(!is_string($set)){
        $error = '跟新语法错误';
        $errno = 1006;
        return false;
    }
    $sql = "update {$table} set {$set} where $where; ";
    $res = mysqli_query ($link, $sql) ;

    //判断语法是否错误
    if($res===false) {
        $error = 'sql 语法错误';
        $errno = 1002;
        return false;
    }
    //正确跟新返回true
    if($res && mysqli_affected_rows($link)) {
        return true ;
    }
    //没有跟新的内容

    return true;
}

/*
 *@desc 格式化数组，保证数组的值两边具有引号
 *@param $set array 要设置的数组
 *@return $data array 调整之后的数组，如"name='张三'"
 */
function parse_array($data){
    foreach ($data as $key => $value){
        $data[$key] = '\'' . $value . '\'';
    }
    return $data;
}

/*
 *@desc 格式化set后面的数据，给值添加引号
 *@param $set array 要修改的数据数组
 *return string 字符串拼接，如",name=张三,age=18"
 */
function parset_set($set){
    $re='';
    foreach ($set as $key => $value){
        $re = $re.',' . $key . '=' . $value;
    }

    return trim($re,',') ;
}

/*
*@desc delete 数据库刪除操作
*@param $link object 数据库连接资源
*@param $table string 数据表
*@param $where string 条件
*@retuan bool true 表示刪除成功
*/
function db_delete($link, $table, $where) {
    global $error;
    global $errno;
    if(!is_string ($where)) {
        $error = '刪除条件珸法不正碗，必須是字符串格式';
        $errno = 1004;
        return false;
    }
    $sql = "delete from {$table} where $where";
    $rs = mysqli_query ($link, $sql);

    if($rs=== false){
        $error = 'sql 语法错误';
        $errno = 1002;
        return false;
    }

    if($rs && mysqli_affected_rows($link)) {
        return true;
    }
    $error = ' 未找到刪除的数据';
    $errno = 1005;
    return false;
}

/*
*@desc select 数据库查询操作
*@param $fields string 数据库的字符段
*@param $table string 结果集类型
*@param $where string 条件
*@param $group string 以字段进行分组
*@param $having string 将执行$group后返回的结果再次进行过滤
*@param $order string 排序
*@param $limit string 限制条件
*@retuan bool true 表示查询成功
*/
function db_select($link, $fields, $table ,$where=null, $group=null, $having=null, $order=null, $limit=null, $arr_type=MYSQLI_ASSOC){
    if(is_string($table)){
        $table = $table;
    }else if(is_array(Stable)){
        $tables = implode(',',$table);
    }

    if(is_string($fields)) {
        $fields = $fields;
    }else if(is_array($fields)){
        $fields = implode(',',$fields);
    }

    $sql = "select {$fields} from {$table}";
    if($where){
        if(!is_string ($where)){
            $error = '刪除条件珸法不正碗，必須是字符串格式';
            $errno = 1004;
            return false;
        }
        $sql .= " where {$where}";
    }

    if ($group) {
        $sql .= " group by $group";
    }

    if($having) {
        $sql .= " having {$having}";
    }
    if($order){
        $sql .= " order by {$order}";
    }
    if($limit){
        $sql .= " limit {$limit}";
    }

    $res = mysqli_query($link, $sql) ;

    if($res && mysqli_num_rows($res)){
        return mysqli_fetch_all($res, $arr_type);
    }

    return false;
}


/*
*@desc 获取错误信息
*@return array 错误信息数组
*/
function get_error(){
    global $error;
    global $errno;
    global $debug;
    if($debug){
       return [
           'error' => $error,
           'errno' => $errno
       ];
    }
}


/*
* @desc db_insert 数据库插入操作
* @param $link object 数据库的连接资源
* @param $table string数据表
* @param $data array要插入的数据
* @param $get_id bool 是否需要返回插入行的ID
* @return bool/int bool表示是否插入成功,int表示插入行的ID
*/
function db_insert($link, $table, $data, $get_id=false) {
    global  $error;
    global $errno;
    if(!is_array($data)) {
        $error = ' 数据库数据的插入格式不合法，必须是数组格式' ;
        $errno = 1001;
    return false;
    }
    $data = parse_array($data) ;
    $fildes = implode(', ' ,array_keys($data)) ;
    $values = implode(', ' ,array_values($data)) ;
    $sql = "insert into {$table} ({$fildes}) values ({$values}) ;";
    //执行SQL
    $rs = mysqli_query ($link, $sql) ;
    if($rs===false) {
        $error = 'sq1 语法错误';
        $errno = 1002;
        return false;
    }
    if($rs && mysqli_affected_rows($link)) {
        if($get_id) {
            return mysqli_insert_id($link) ;
        }
        return true ;
    }
    $error = '插入的数据不匹配';
    $errno = 1003;
    return false;
}