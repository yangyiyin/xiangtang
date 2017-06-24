<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */
$auth = 'AuthManager/changeStatus?method=forbidGroup';
$query = preg_replace('/^.+\?/U','',$auth);

parse_str($query,$param); //解析规则中的param
var_dump( $param);

?>
