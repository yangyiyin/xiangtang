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
class OrderAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {

        $pre_order_ids = explode(',', $this->post_data['pre_order_ids']);
        $address = $this->post_data['address'];
        $name = $this->post_data['name'];
        $tel = $this->post_data['tel'];
        $receiving_type = $this->post_data['receiving_type'];
        $receiving_service_name = $this->post_data['receiving_service_name'];
        $pay_type = $this->post_data['pay_type'];

        $account_money = $this->post_data['account_money'];
        //优惠券
        $coupon_id = $this->post_data['coupon_id'];

        if (!$pre_order_ids) {
            return result_json(FALSE, '参数错误~');
        }


        if ($receiving_type == \Common\Model\NfOrderModel::RECIEVE_TYPE_SERVER) {
            if (!$receiving_service_name) {
                return result_json(FALSE, '请检查收货网点是否填写~');
            }
        }

        if (!is_tel_num($tel)) {
            return result_json(FALSE, '请检查联系电话');
        }

        $OrderPreService = \Common\Service\OrderPreService::get_instance();
        $pre_orders = $OrderPreService->get_by_ids($pre_order_ids);

        if(count($pre_orders) != count($pre_order_ids)) {
            return result_json(false, '订单异常,请稍后再试');
        }

        if (!$this->check_same_real($pre_orders)) {
            return result_json(false, '订单性质不一致,请稍后再试');
        }
        if (!$pre_orders[0]['is_real']) {
            $address = '虚拟商品没有收货地址';
        }
        if ($receiving_type == \Common\Model\NfOrderModel::RECIEVE_TYPE_ARRIVE) {

            if (!$address || !$name || !$tel) {
                return result_json(FALSE, '请检查收货信息是否填写~');
            }
        }

        $sum = array_sum(result_to_array($pre_orders, 'sum'));
        if ($coupon_id) {
            //检测优惠券信息
            $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
            $coupon = $UserDeductibleCouponService->get_info_by_id($coupon_id);
            if (!$coupon || $coupon['uid'] != $this->uid) {
                return result_json(FALSE, '非法的优惠券!');
            }
            if ($sum < $coupon['least']) {
                return result_json(FALSE, '非法的优惠券!');
            }
            $sum -= $coupon['deductible'];//总价减去优惠券
        }

        $order_ids = [];
        foreach ($pre_order_ids as $pre_order_id) {
            $ret = $this->OrderService->create_by_pre_order_id($pre_order_id, $this->uid, ['receiving_type' => $receiving_type, 'receiving_service_name' => $receiving_service_name, 'address' => $address, 'name' => $name, 'tel' => $tel, 'pay_type' => $pay_type]);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
            $order_ids[] = $ret->data;
        }

        $orders = $this->OrderService->get_by_ids($order_ids);
        //$order_ids = $ret->data;
        if ($account_money) {
            //账户支付--优惠方式
            $AccountService = \Common\Service\AccountService::get_instance();

            if ($sum != $account_money) {//必须钱数一样
                return result_json(FALSE, '参数错误');
            }

            $ret = $AccountService->check_is_available($this->uid, $account_money);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }

            //记录订单优惠
            $OrderBenefitService = \Common\Service\OrderBenefitService::get_instance();
            $data = [];
            sort($order_ids);
            $data['oids'] = join(',', $order_ids);
            $data['type'] = \Common\Model\NfOrderBenefitModel::TYPE_ACCOUNT;
            $data['rule'] = $account_money;
            $ret = $OrderBenefitService->add_one($data);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }

            $PayService = \Common\Service\PayService::get_instance();
            $data = [];
            $data['pay_no'] = $PayService->get_pay_no($orders[0]['uid']);
            $data['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ACCOUNT;
            $data['uid'] = $orders[0]['uid'];
            $data['order_ids'] = join(',', $order_ids);
            $data['sum'] = $account_money;
            $data['create_time'] = current_date();
            $data['status'] = \Common\Model\NfPayModel::STATUS_COMPLETE;
            $ret = $PayService->add_one($data);

            if (!$ret->success) {
                return result(FALSE, $ret->message);
            }
            $data['id'] = $ret->data;

            //更新订单
            $AccountLogService = \Common\Service\AccountLogService::get_instance();
            $AccountService = \Common\Service\AccountService::get_instance();
            //更新订单状态
            //获取加盟商的uids
            $MemberService = \Common\Service\MemberService::get_instance();
            $franchisee_uids = $MemberService->get_franchisee_uids();
            $OrderService = \Common\Service\OrderService::get_instance();
            $UserService = \Common\Service\UserService::get_instance();
            foreach ($order_ids as $order_id) {

                $ret = $OrderService->is_available_payed($order_id, $data['uid']);
                if (!$ret->success) {
                    return result(FALSE, '订单不可支付');
                }
                $order = $ret->data;
                $ret = $OrderService->payed($order,  \Common\Model\NfOrderModel::PAY_TYPE_LOCAL);
                if (!$ret->success) {
                    return result(FALSE, '订单支付失败');
                }
            }
            //财务记录
            $account_data = [];
            //扣除佣金
            $ret = $AccountService->minus_account($data['uid'], $sum);
            if (!$ret->success){
                $this->error($ret->message);
            }
            $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS;
            $account_data['sum'] = -$sum;
            $account_data['oid'] = join(',', $order_ids);
            $account_data['uid'] = $data['uid'];
            $account_data['pay_no'] ='';
            $AccountLogService->add_one($account_data);
        }

        //处理优惠相关
        if (isset($coupon)) {//使用了优惠券
            //记录订单优惠
            $OrderBenefitService = \Common\Service\OrderBenefitService::get_instance();
            $data = [];
            sort($order_ids);
            $data['oids'] = join(',', $order_ids);
            $data['type'] = \Common\Model\NfOrderBenefitModel::TYPE_COUPON;
            $data['rule'] = $coupon['deductible'];
            $ret = $OrderBenefitService->add_one($data);
            if (!$ret->success) {
                //return result_json(FALSE, $ret->message);
            } else {
                //删除优惠券
                $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
                $UserDeductibleCouponService->del_by_id($coupon['id']);

                //插入订单优惠券表
                $OrderCouponService = \Common\Service\OrderCouponService::get_instance();
                foreach ($order_ids as $order_id) {
                    $order_coupon_data = [];
                    $order_coupon_data['oid'] = $order_id;
                    $order_coupon_data['cid'] = $coupon['id'];
                    $OrderCouponService->add_one($order_coupon_data);
                }
            }
        }
        //全场满赠
        $OverallGiftActivityService = \Common\Service\OverallGiftActivityService::get_instance();
        $activities = $OverallGiftActivityService->get_all();
        $OrderBenefitService = \Common\Service\OrderBenefitService::get_instance();
        if ($activities) {
            foreach ($activities as $activity) {
                $rule = [];
                if ($sum >= $activity['least']) {
                    $rule[] = '满'.($activity['least']/100).'元送'.$activity['extra'];
                }
            }
            //记录订单优惠
            if ($rule) {
                $data = [];
                $data['oids'] = $order_ids[0];//订单第一个赠送
                $data['type'] = \Common\Model\NfOrderBenefitModel::TYPE_OVERALL;
                $data['rule'] = join('|', $rule);
                $ret = $OrderBenefitService->add_one($data);
            }

        }


        $to_pay = true;
        if ($pay_type == \Common\Model\NfOrderModel::PAY_TYPE_OFFLINE) {
            $to_pay = false;
        }

        return result_json(TRUE, '成功创建订单~', ['order_ids' => join(',', $order_ids), 'to_pay'=>$to_pay]);
    }

    public function check_same_real($items) {
        $is_real = 99;
        foreach ($items as $_item) {
            if ($is_real == 99) {
                $is_real = $_item['is_real'];
            }

            if ($is_real != 99 && $is_real != $_item['is_real']) {
                return false;
            }

        }
        return true;
    }
}