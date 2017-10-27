<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/alipay/aop/AopClient.php';
require APP_PATH . '/Common/Lib/alipay/aop/request/AlipayTradeAppPayRequest.php';

class VolunteerAlipayNotify extends BaseSapi{

    private $PayNotifyLogService;
    protected $method = parent::API_METHOD_POST;
    public function init() {
        $this->PayNotifyLogService = \Common\Service\PayNotifyLogService::get_instance();
    }

    public function excute() {
        $data_notify = [];
        $data_notify['pay_no'] = isset($_POST['out_trade_no']) ? $_POST['out_trade_no'] : '';
        $data_notify['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ALIPAY;
        $data_notify['content'] = json_encode($_POST);
        $data_notify['create_time'] = current_date();
        $data_notify['code'] = isset($_POST['trade_status']) ? $_POST['trade_status'] : '';
        $data_notify['remark'] = '';
        $this->PayNotifyLogService->add_one($data_notify);
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = Service\PayService::AlipayPubKey;
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if (!$flag) {
            echo 'fail';
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = 'rsacheck_fail';
            $this->PayNotifyLogService->add_one($data_notify);
            exit();
        }

        //业务处理
        //记录回调

        if ($_POST['trade_status'] == 'TRADE_SUCCESS') {//成功
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
            $data_update['pay_type'] = \Common\Model\NfVolunteerModel::PAY_TYPE_ALI;
            $ret = $VolunteerService->update_by_id($data_notify['pay_no'], $data_update);

            if (!$ret->success) {
                $data_notify['create_time'] = current_date();
                $data_notify['remark'] = '更新交易状态失败';
                $this->PayNotifyLogService->add_one($data_notify);
                exit;
            }

            echo 'success';
            exit;
        } elseif ($_POST['trade_status'] == 'TRADE_FINISHED') {//完成
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = '交易finish';
            $this->PayNotifyLogService->add_one($data_notify);
            exit;
            echo 'success';
            exit;
        }  elseif ($_POST['trade_status'] == 'TRADE_CLOSED') {//关闭
            $data_notify['create_time'] = current_date();
            $data_notify['remark'] = '交易close';
            $this->PayNotifyLogService->add_one($data_notify);
            exit;
            echo 'success';
            exit;
        }

    }
}