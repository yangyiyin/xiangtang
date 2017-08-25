<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class OrderStep extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        $this->can_order();
        $order_id = I('post.order_id');

        $action = I('post.action');
        $order_id = $this->post_data['order_id'];
        $action = $this->post_data['action'];
        $order_ids = explode(',', $this->post_data['order_ids']);

        if (!$order_ids) {
            return result_json(FALSE, '参数错误~');
        }

        if ($action == 'paying') {
            //检测优惠
            sort($order_ids);
            $OrderBenefitService = \Common\Service\OrderBenefitService::get_instance();
            $benefit = $OrderBenefitService->get_info_by_oids(join(',', $order_ids));
            if ($benefit) {
                if ($benefit['type'] == \Common\Model\NfOrderBenefitModel::TYPE_ACCOUNT) {
                    $AccountService = \Common\Service\AccountService::get_instance();
                    $ret = $AccountService->pay($this->uid, $benefit['rule']);
                    if (!$ret->success) {
                        $PayNotifyLogService = \Common\Service\PayNotifyLogService::get_instance();
                        $data_notify = [];
                        $data_notify['pay_no'] = 'oids::'. join(',', $order_ids);
                        $data_notify['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ALIPAY;
                        $data_notify['content'] = '订单账户支付异常';
                        $data_notify['create_time'] = current_date();
                        $data_notify['code'] = '';
                        $data_notify['remark'] = '订单账户支付异常::'.$ret->message;
                        $PayNotifyLogService->add_one($data_notify);

                        return result(FALSE, $ret->message);
                    }
                }
            }

        }

        foreach ($order_ids as $order_id) {
            $ret = $this->OrderService->process($order_id, $action, $this->uid);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
        }



        return result_json(TRUE, '操作成功~');
    }
}