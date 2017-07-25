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
        $pre_order_id = I('post.pre_order_id');
        $address = I('post.address');
        $name = I('post.name');
        $tel = I('post.tel');

        $pre_order_id = $this->post_data['pre_order_id'];
        $pre_order_ids = explode(',', $this->post_data['pre_order_ids']);
        $address = $this->post_data['address'];
        $name = $this->post_data['name'];
        $tel = $this->post_data['tel'];
        $receiving_type = $this->post_data['receiving_type'];
        $receiving_service_name = $this->post_data['receiving_service_name'];


        if (!$pre_order_ids) {
            return result_json(FALSE, '参数错误~');
        }

        if ($receiving_type == \Common\Model\NfOrderModel::RECIEVE_TYPE_ARRIVE) {
            if (!$address || !$name || !$tel) {
                return result_json(FALSE, '请检查收货信息是否填写~');
            }
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
        $order_ids = [];
        foreach ($pre_order_ids as $pre_order_id) {
            $ret = $this->OrderService->create_by_pre_order_id($pre_order_id, $this->uid, ['receiving_type' => $receiving_type, 'receiving_service_name' => $receiving_service_name, 'address' => $address, 'name' => $name, 'tel' => $tel]);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
            $order_ids[] = $ret->data;
        }
        //$order_ids = $ret->data;

        return result_json(TRUE, '成功创建订单~', ['order_ids' => join(',', $order_ids), 'to_pay'=>true]);
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