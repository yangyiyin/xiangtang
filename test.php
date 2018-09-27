<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */

//function make_tree($list) {
//    $child_pids = [];
//    foreach ($list as $_ms) {
//        foreach ($_ms as $_m) {
//            $sub_tree[$_m['pid']][] = get_tree($list, $_m, $child_pids);
//        }
//    }
//
//    foreach ($child_pids as $pid) {
//        unset($sub_tree[$pid]);
//    }
//
//
//
//    return $sub_tree;
//
//}
//
//function get_tree($map, $m, &$child_pids) {
//    if (isset($map[$m['id']])) {
//        foreach ($map[$m['id']] as $_m) {
//            $return_m = get_tree($map, $_m, $child_pids);
//            $child_pids[] = $return_m['pid'];
//            $m['child'][$_m['id']][] = $return_m;
//        }
//
//    } else {
//        return $m;
//    }
//    return $m;
//}
//
//
//
//$list = [
//    ['id'=>2,"pid"=>5],
//    ['id'=>8,"pid"=>2],
//    ['id'=>3,"pid"=>0],
//    ['id'=>4,"pid"=>0],
//    ['id'=>5,"pid"=>0],
//    ['id'=>7,"pid"=>3],
//    ['id'=>1,"pid"=>5],
//    ['id'=>13,"pid"=>2],
//    ['id'=>12,"pid"=>8],
//    ['id'=>17,"pid"=>7]
//
//];
//foreach ($list as $k => $_li) {
//    $list[$_li['pid']][] = $_li;
//}
//$tree = make_tree($list);
//http://pbw56w09g.bkt.clouddn.com/Screen Shot 2018-08-21 at 1.39.53 PM.png-1534832104
$a = 1==2;
var_dump($a);
