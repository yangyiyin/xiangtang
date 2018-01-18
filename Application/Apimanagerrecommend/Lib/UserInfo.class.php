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
class UserInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $info = $this->UserService->get_info_by_id($this->uid);
        $data = convert_obj($info, 'id,user_name,avatar,entity_title,entity_tel');
        //$data->type = (int) $data->type;
        $data->avatar = $data->avatar ? item_img($data->avatar) : item_img(get_cover(46, 'path'));

        //获取会员信息
        $VipService = \Common\Service\VipService::get_instance();
        $vip = $VipService->get_info_by_uid($this->uid);
        if ($vip) {
            $data->vip = $vip;
            $data->is_vip = true;
            $data->is_past = false;
            $data->day_left = '';

            $left_time = strtotime($vip['end_time']) - time();
            if ($left_time <= 0) {
                $data->is_past = true;
                $data->day_left = '已过期';

            } elseif ($left_time < 7 * 3600 * 24) {
                $left_day = floor($left_time / 3600 / 24);
                if ($left_day == 0) {
                    $data->day_left = '今天到期';
                } else {
                    $data->day_left = '还剩'.$left_day.'天';
                }

            }
        } else {
            $data->vip = [];
            $data->is_vip = false;
            $data->day_left = '';
        }

        return result_json(TRUE, '', $data);
    }
}