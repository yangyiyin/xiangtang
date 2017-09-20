<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';
require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';

class WechatPayNotify extends BaseSapi{

    private $PayNotifyLogService;
    protected $method = parent::API_METHOD_ALL;
    public function init() {
        $this->PayNotifyLogService = \Common\Service\PayNotifyLogService::get_instance();
    }

    public function excute() {
        //var_dump(function_exists('simplexml_load_string'));die();


        $wechat = new \Wechat($wechat_config);
        $verify_info = $wechat->verifyNotify(); // 验证通知

        $data_notify = [];
        $data_notify['pay_no'] = isset($verify_info['out_trade_no']) ? $verify_info['out_trade_no'] : '';
        $data_notify['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_WECHAT_PAY;
        $data_notify['content'] = $verify_info;
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
            $PayService = Service\PayService::get_instance();
            $pay_info = $PayService->is_available_complete($data_notify['pay_no']);
            if (!$pay_info) {
                $data_notify['create_time'] = current_date();
                $data_notify['remark'] = '交易信息异常';
                $this->PayNotifyLogService->add_one($data_notify);
                exit;
            }
            $ret = $PayService->complete($data_notify['pay_no']);
            if (!$ret->success) {
                $data_notify['create_time'] = current_date();
                $data_notify['remark'] = '更新交易状态失败';
                $this->PayNotifyLogService->add_one($data_notify);
                exit;
            }
            $AccountLogService = \Common\Service\AccountLogService::get_instance();
            $AccountService = \Common\Service\AccountService::get_instance();
            //更新订单状态
            $order_ids = explode(',', $pay_info['order_ids']);
            $account_data = [];
            //获取加盟商的uids
            $MemberService = \Common\Service\MemberService::get_instance();
            $franchisee_uids = $MemberService->get_franchisee_uids();
            $OrderService = Service\OrderService::get_instance();
            $UserService = \Common\Service\UserService::get_instance();
            foreach ($order_ids as $order_id) {

                $ret = $OrderService->is_available_payed($order_id, $pay_info['uid']);
                if (!$ret->success) {
                    $data_notify['create_time'] = current_date();
                    $data_notify['remark'] = $ret->message;
                    $this->PayNotifyLogService->add_one($data_notify);
                    exit;
                }
                $order = $ret->data;
                $ret = $OrderService->payed($order, \Common\Model\NfOrderModel::PAY_TYPE_WECHAT);
                if (!$ret->success) {
                    $data_notify['create_time'] = current_date();
                    $data_notify['remark'] = '订单状态更新失败';
                    $this->PayNotifyLogService->add_one($data_notify);
                    exit;
                }
                //财务记录
                $account_data = [];
                if (in_array($order['seller_uid'], $franchisee_uids)) {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_FRANCHISEE_ADD;
                } else {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_PLATFORM_ADD;
                }
                $account_data['sum'] = $order['sum'];
                $account_data['oid'] = $order_id;
                $account_data['uid'] = $order['seller_uid'];
                $account_data['pay_no'] = $data_notify['pay_no'];
                $AccountLogService->add_one($account_data);
//
//                if ($order['inviter_id']) {
//                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD;
//                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
//                    $account_data['sum'] = $order['dealer_profit'];
//                    $account_data['oid'] = $order_id;
//                    $account_data['uid'] = $order['inviter_id'];
//                    $account_data['pay_no'] = $data_notify['pay_no'];
//                    $AccountLogService->add_one($account_data);
//                    $AccountService->add_account($order['inviter_id'], $order['dealer_profit']);
//                }
//
//                if ($UserService->is_dealer($order['uid'])) {
//                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD;
//                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
//                    $account_data['sum'] = $order['dealer_profit'];
//                    $account_data['oid'] = $order_id;
//                    $account_data['uid'] = $order['uid'];
//                    $account_data['pay_no'] = $data_notify['pay_no'];
//                    $AccountLogService->add_one($account_data);
//                    $AccountService->add_account($order['uid'], $order['dealer_profit']);
//                }
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