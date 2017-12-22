<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class UserDisabledVerify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {

        $disabled_name = $this->post_data['disabled_name'];
        $disabled_tel = $this->post_data['disabled_tel'];
        $disabled_id = $this->post_data['disabled_id'];

        if (!$disabled_name || !$disabled_tel || !$disabled_id) {
            return result_json('FALSE', '请填写完整的信息');
        }

        if (!is_tel_num($disabled_tel)) {
            return result_json('FALSE', '请填写正确的手机号');
        }

        if ($this->user_info['verify_status'] != \Common\Model\NfUserModel::VERIFY_STATUS_NONE) {
            return result_json('FALSE', '无法认证信息,已提交资料或已被拒绝');
        }

        $data = [];
        $data['disabled_name'] = $disabled_name;
        $data['disabled_tel'] = $disabled_tel;
        $data['disabled_id'] = $disabled_id;
        $data['verify_status'] = \Common\Model\NfUserModel::VERIFY_STATUS_SUBMIT;

        $ret = $this->UserService->update_by_id($this->uid, $data);

        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '提交资料成功,等待审核');


    }

}