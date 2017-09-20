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
$xmlData = '<xml><appid><![CDATA[wxf716bd5d6844abf4]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[N]]></is_subscribe>
<mch_id><![CDATA[1488335702]]></mch_id>
<nonce_str><![CDATA[59c1cabcbd2af]]></nonce_str>
<openid><![CDATA[oAe_Z0043h9RSJ5HTXQpry8-AakU]]></openid>
<out_trade_no><![CDATA[T10150587257277596]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[AC4DB31B9B702E54633DCC5689654D72]]></sign>
<time_end><![CDATA[20170920095619]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[APP]]></trade_type>
<transaction_id><![CDATA[4200000003201709203140993177]]></transaction_id>
</xml>';


$postObj = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);

if (! is_object($postObj)) {
    echo 1;
}
$array = json_decode(json_encode($postObj), true); // xml对象转数组

$xml = array_change_key_case($array, CASE_LOWER); // 所有键小写

$wx_sign = $xml['sign'];
unset($xml['sign']);
$fb_sign = setWxSign($xml);
if ($fb_sign != $wx_sign) {
    echo 4;
}
echo 2;

function setWxSign($sign_data) {
    if (isset($sign_data['sign'])) unset($sign_data['sign']);
    ksort($sign_data);
    $sign_str = urldecode(http_build_query($sign_data));
    return strtoupper(md5($sign_str.'&key='.'148833570214883357021488335702hs'));
}

