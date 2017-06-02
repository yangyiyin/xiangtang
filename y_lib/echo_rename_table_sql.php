<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */
error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED );
//设置好相关信息
$dbserver='121.40.162.180';//连接的服务器一般为localhost
$dbname='gd_lvjin';//数据库名
$dbuser='gd_lvjin_user';//数据库用户名
$dbpassword='EcCJYrnxMAfy2vcz';//数据库密码
$old_prefix='ant_';//数据库的前缀
$new_prefix='shopy_';//数据库的前缀修改为
if ( !is_string($dbname) || !is_string($old_prefix)|| !is_string($new_prefix) ){
    return false;
}
if (!mysql_connect($dbserver, $dbuser, $dbpassword)) {
    print 'Could not connect to mysql';
    exit;
}

//取得数据库内所有的表名
//$result = mysql_list_tables($dbname);
$result = mysql_query("SHOW TABLES FROM $dbname");
if (!$result) {
    print "DB Error, could not list tables\n";
    exit;
}

//把表名存进$data
while ($row = mysql_fetch_row($result)) {
    $data[] = $row[0];
}
//过滤要修改前缀的表名
foreach($data as $k => $v)
{
    $preg = preg_match("/^($old_prefix{1})([a-zA-Z0-9_-]+)/i", $v, $v1);
    if($preg){
        $tab_name[$k] = $v1[2];
//$tab_name[$k] = str_replace($old_prefix, '', $v);
    }
}
if ($tab_name) {
    foreach($tab_name as $k => $v){
        echo $sql = 'alter table `'.$old_prefix.$v.'` rename `'.$new_prefix.$v.'`;' . "<br/>";
        //mysql_query($sql);
    }
}
die();

?>
