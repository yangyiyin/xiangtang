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
class UserInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $info = $this->UserService->get_info_by_id($this->uid);
        $data = convert_obj($info, 'user_name,type,avatar,user_tel,sex,province,city,address,entity_name,verify_status');
        $data->verify_status = (int) $data->verify_status;
        $data->avatar = item_img(get_cover(46, 'path'));

        if ($data->is_inviter) {
            $UserInviterCodeService = \Common\Service\UserInviterCodeService::get_instance();
            $inviter_info = $UserInviterCodeService->get_by_uid($this->uid);
            if ($inviter_info) {
                $data->inviter_code = $inviter_info['code'];
            } else {
                $data->inviter_code = '';
            }
        }

        return result_json(TRUE, '', $data);
    }
}