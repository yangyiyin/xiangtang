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
        $data = convert_obj($info, 'user_name,avatar,user_tel,sex,province,city,address,entity_name,verify_status');
        $data->avatar = item_img(get_cover(13, 'path'));
        return result_json(TRUE, '', $data);
    }
}