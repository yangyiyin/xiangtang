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
        $address = $this->post_data['address'];
        $name = $this->post_data['name'];
        $tel = $this->post_data['tel'];


        if (!$pre_order_id || !$address || !$name || !$tel) {
            return result_json(FALSE, '请检查收货信息是否填写~');
        }

        if (!is_tel_num($tel)) {
            return result_json(FALSE, '请检查联系电话');
        }

        $ret = $this->OrderService->create_by_pre_order_id($pre_order_id, $this->uid, ['address' => $address, 'name' => $name, 'tel' => $tel]);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        $userService = Service\UserService::get_instance();
        $to_pay = $this->user_info->type == $userService->get_type_people();
        return result_json(TRUE, '成功创建订单~', ['order_id' => $ret->data, 'to_pay' => $to_pay]);
    }
}