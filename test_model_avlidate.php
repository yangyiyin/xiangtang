<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */


$a = ' A1 decimal(9,2) NOT NULL DEFAULT 0,	#贷款利率
  Rate_A1 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率
  A2 decimal(9,2) NOT NULL DEFAULT 0,	#最高利率
  Rate_A2 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率
  A3 decimal(9,2) NOT NULL DEFAULT 0,	#最低利率
  Rate_A3 decimal(9,2) NOT NULL DEFAULT 0,	#发生额
  A4 decimal(9,2) NOT NULL DEFAULT 0,	#信用
  Rate_A4 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率
  A5 decimal(9,2) NOT NULL DEFAULT 0,	#抵押
  Rate_A5 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率
  A6 decimal(9,2) NOT NULL DEFAULT 0,	#保证
  Rate_A6 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率
  A7 decimal(9,2) NOT NULL DEFAULT 0,	#抵押+保证
  Rate_A7 decimal(9,2) NOT NULL DEFAULT 0,	#加权平均利率';


$arr = explode("\n", $a);
foreach ($arr as $_a) {
    $_a = trim($_a);

    $__arr = explode('#', $_a);
    $remark = $__arr[1];
    $_arr = explode(' ', $__arr[0]);
    $field = $_arr[0];
    $type = $_arr[1];
    $type_arr = explode('(', $type);
    $type = $type_arr[0];

    if ($type == 'decimal') {
        echo "array('".$field."', 'currency', '".$remark."', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH)," . "\n";
    } elseif ($type == 'varchar') {
        echo "array('".$field."', 'require', '".$remark."', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),". "\n";
    } else {
        echo "array('".$field."', 'require', '".$remark."', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),". "\n";

    }


}


