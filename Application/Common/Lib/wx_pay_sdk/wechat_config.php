<?php
$wechat_config['APPID'] = "wx939ea03c3f8d5f12";//

$wechat_config['APPSECRET'] = "";// 权限获取所需密钥 Key

$wechat_config['KEY'] = "150858720115085872011508587201YX";// 加密密钥 Key，也即appKey

$wechat_config['MCH_ID'] = '1508587201';// 财付通商户身份标识

$wechat_config['NOTIFY_URL'] = 'http://'.C('BASE_WEB_HOST').'/public/index.php/Apimanagerrecommend/pay/wechat_pay_notify.html';// 微信支付完成服务器通知页面地址

?>