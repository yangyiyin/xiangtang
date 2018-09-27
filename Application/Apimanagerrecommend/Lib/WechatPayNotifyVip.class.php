<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';

class WechatPayNotifyVip extends BaseSapi{

    private $PayNotifyLogService;
    protected $method = parent::API_METHOD_ALL;
    public function init() {
        $this->PayNotifyLogService = \Common\Service\PayNotifyLogService::get_instance();
    }

    public function excute() {

        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config_vip.php';
        $wechat = new \Wechat($wechat_config);
        $verify_info = $wechat->verifyNotify(); // 验证通知

        $data_notify = [];
        $data_notify['pay_no'] = isset($verify_info['out_trade_no']) ? $verify_info['out_trade_no'] : '';
        $data_notify['pay_agent'] = 'wechat';
        $data_notify['content'] = json_encode($verify_info);
        $data_notify['create_time'] = current_date();
        $data_notify['code'] = isset($verify_info['result_code']) ? $verify_info['result_code'] : '';
        $data_notify['remark'] = '';
        $this->PayNotifyLogService->add_one($data_notify);


        if ($verify_info === false) {
            echo 'fail';
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = 'rsacheck_fail';
            $this->PayNotifyLogService->add_one($data_notify);
            exit();
        }

        $ActivityPayService = Service\ActivityPayService::get_instance();
        $ActivityPayService->update_by_pay_no($verify_info['out_trade_no'], ['callback_content'=>json_encode($verify_info)]);

        //业务处理
        //记录回调

        if ($verify_info['result_code'] == 'SUCCESS') {//成功
            $pay_info = $ActivityPayService->get_by_pay_no($verify_info['out_trade_no']);
            if (!$pay_info) {
                echo 'success';
                exit;
            }
            //开始事务
            $Model = M();
            $Model->startTrans();

            $res1 = $ActivityPayService->update_by_pay_no($verify_info['out_trade_no'], ['status'=>1]);

            if ($res1->success) {
                $Model->commit();
            } else {
                $Model->rollback();
            }
            echo 'success';
            exit;
        } elseif ($verify_info['result_code'] == 'FAIL') {//完成
            $ActivityPayService->update_by_pay_no($verify_info['out_trade_no'], ['status'=>2]);
            echo 'fail1';
            exit;
        }  else {//异常
            $ActivityPayService->update_by_pay_no($verify_info['out_trade_no'], ['status'=>2]);
            echo 'fail2';
            exit;
        }

    }
}