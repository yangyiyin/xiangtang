<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';

class VolunteerWechatPayNotify extends BaseSapi{

    private $PayNotifyLogService;
    protected $method = parent::API_METHOD_ALL;
    public function init() {
        $this->PayNotifyLogService = \Common\Service\PayNotifyLogService::get_instance();
    }

    public function excute() {

        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';
        $wechat = new \Wechat($wechat_config);
        $verify_info = $wechat->verifyNotify(); // 验证通知

        $data_notify = [];
        $data_notify['pay_no'] = isset($verify_info['out_trade_no']) ? $verify_info['out_trade_no'] : '';
        $data_notify['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_WECHAT_PAY;
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


        //业务处理
        //记录回调

        if ($verify_info['result_code'] == 'SUCCESS') {//成功
            //更新交易状态
            $VolunteerService = Service\VolunteerService::get_instance();
            $pay_info = $VolunteerService->is_available_complete($data_notify['pay_no']);
            if (!$pay_info) {
                $data_notify['create_time'] = current_date();
                $data_notify['remark'] = '交易信息异常';
                $this->PayNotifyLogService->add_one($data_notify);
                exit;
            }
            $data_update = [];
            $data_update['status'] = \Common\Model\NfVolunteerModel::STATUS_PAYED;
            $ret = $VolunteerService->update_by_id($data_notify['pay_no'], $data_update);

            if (!$ret->success) {
                $data_notify['create_time'] = current_date();
                $data_notify['remark'] = '更新交易状态失败';
                $this->PayNotifyLogService->add_one($data_notify);
                exit;
            }


            echo 'success';
            exit;
        } elseif ($verify_info['result_code'] == 'FAIL') {//完成
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = '交易fail,erro_code_des'.$verify_info['err_code_des'];
            $this->PayNotifyLogService->add_one($data_notify);
            exit;
            echo 'success';
            exit;
        }  else {//异常
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = '微信异步结果异常' . json_decode($verify_info);
            $this->PayNotifyLogService->add_one($data_notify);
            exit;
            echo 'success';
            exit;
        }

    }
}