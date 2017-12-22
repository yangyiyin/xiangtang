<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class UserBeInviter extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {

        if ($this->user_info['is_inviter'] != \Common\Model\NfUserModel::IS_INVITER_NONE) {
            return result_json('FALSE', '无法申请成为邀请者,已申请或已成为邀请者');
        }

        $data = [];
        $data['is_inviter'] = \Common\Model\NfUserModel::IS_INVITER_SUBMIT;

        $ret = $this->UserService->update_by_id($this->uid, $data);

        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '申请成功,等待审核');


    }

}