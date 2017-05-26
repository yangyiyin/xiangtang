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
class UserReceivingModify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserReceivingService;
    public function init() {
        $this->UserReceivingService = Service\UserReceivingService::get_instance();
    }

    public function excute() {
        $id = I('post.rid');
        $id = $this->post_data['rid'];
        if ($id) {
            $receiving = $this->UserReceivingService->get_info_by_id($id);
            if (!$receiving) {
                return result_json(FALSE, '没有该收货地址');
            }
        }

        if (!$this->post_data['name'] || !$this->post_data['tel'] || !$this->post_data['province'] || !$this->post_data['city'] || !$this->post_data['area'] || !$this->post_data['address']) {
            return result_json(FALSE, '请填写完整的收货地址信息~');
        }

        if (!is_tel_num($this->post_data['tel'])) {
            return result_json(FALSE, '您填写的手机号码有误~');
        }

//        name:姓名，
//		tel:联系手机号（服务端处理成带**的字符串）
//		province:省
//		city:市
//		area：区
//		address:详细地址
        $data = [];
        if ($this->post_data['name']) $data['name'] = $this->post_data['name'];
        if ($this->post_data['tel']) $data['tel'] = $this->post_data['tel'];
        if ($this->post_data['province']) $data['province'] = $this->post_data['province'];
        if ($this->post_data['city']) $data['city'] = $this->post_data['city'];
        if ($this->post_data['area']) $data['area'] = $this->post_data['area'];
        if ($this->post_data['address']) $data['address'] = $this->post_data['address'];

        if ($id) {
            $province = isset($data['province']) ? $data['province'] : $receiving['province'];
            $city= isset($data['city']) ? $data['city'] : $receiving['city'];
            $area = isset($data['area']) ? $data['area'] : $receiving['area'];
            $address = isset($data['address']) ? $data['address'] : $receiving['address'];
            $data['address_full'] = $province . $city . $area . $address;
            $ret = $this->UserReceivingService->update_by_id($id, $data);
        } else {
            $province = isset($data['province']) ? $data['province'] : '';
            $city= isset($data['city']) ? $data['city'] : '';
            $area = isset($data['area']) ? $data['area'] : '';
            $address = isset($data['address']) ? $data['address'] : '';
            $data['address_full'] = $province . $city . $area . $address;
            $data['uid'] = $this->uid;
            $ret = $this->UserReceivingService->add_one($data);
            $id = $ret->data;
        }


        if (!$ret) {
            return result_json(FALSE, $ret->message);
        }

        return result_json(TRUE, '成功啦', $id);
    }
}