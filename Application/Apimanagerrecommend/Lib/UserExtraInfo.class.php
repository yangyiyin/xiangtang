<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Model;
use Common\Service;
class UserExtraInfo extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $data = [];
        $entity_name = $this->post_data['entity_name'];
        $entity_title = $this->post_data['entity_title'];
        $entity_tel = $this->post_data['entity_tel'];
        $entity_license = $this->post_data['entity_license'];

        if (!$entity_name || !$entity_title || !$entity_tel || !$entity_license) {
            result_json(FALSE, '参数不完整~');
        }

        $data['entity_name'] = $this->post_data['entity_name'];
        $data['entity_title'] = $this->post_data['entity_title'];
        $data['entity_tel'] = $this->post_data['entity_tel'];
        $data['entity_license'] = $this->post_data['entity_license'];
        $data['verify_status'] = $this->UserService->get_verify_status_submit();

        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '提交成功!');
    }
}