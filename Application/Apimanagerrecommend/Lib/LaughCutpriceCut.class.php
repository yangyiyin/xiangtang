<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughCutpriceCut extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];
        $phone = isset($this->post_data['phone']) ? $this->post_data['phone'] : '';

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);

        if (!$page_info) {
            return result_json(false, '页面不存在!');
        }

        $VipService = \Common\Service\VipService::get_instance();
        $ret = $VipService->is_vip($page_info['uid']);
        if (!$ret->success) {
            return result_json(false, '对不起,当前链接暂无法砍价');
        }

        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

//        if (isset($page_info['tmp_data']['time_limit_end'])) {
//            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {
//
//                return result_json(false, '报名已结束!');
//            }
//        }

        if ($page_info['start_time'] && time() < $page_info['start_time']) {
            return result_json(false, '活动尚未开始!');
        }

        if ($page_info['end_time'] && time() > $page_info['end_time']) {
            return result_json(false, '活动已结束!');
        }


        $price = $max_minus_price = $average_price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
                $max_minus_price = $_page['cutprice_max_minus_price'];
                $average_price = $_page['cutprice_average_price'];
            }
        }
        if (!$price || !$max_minus_price || !$average_price) {

            return result_json(false, '活动信息异常!');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        $data['phone'] = $phone;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {

            return result_json(false, '您已砍价!');

        }
        $cut_info = $PageCutpriceService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$cut_info) {

            return result_json(false, '砍价信息异常!');
        }

        $left_price = $max_minus_price * 100 - ($price * 100 - $cut_info['price']);
        if ($left_price <= 0) {

            return result_json(false, '该砍价已经到上限!');
        }
        $can_cut_price = $left_price > ($average_price * 1.4 * 100) ? ($average_price * 1.4 * 100) : $left_price;

        $data['cutprice'] = mt_rand($can_cut_price * 0.4, $can_cut_price);
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

//        //生成提货码
//        $pick_code = sprintf("%04d", $ret->data);
//        $ret = $PageCutpriceService->update_by_id($ret->data, ['pick_code'=>$pick_code]);
//        if (!$ret->success) {
//            return result_json(false, '系统异常,您的提货码生成失败,请联系客服');
//        }
//        //发送短信 todo

        $data_up = [];
        $data_up['price'] = $cut_info['price'] - $data['cutprice'];
        $PageCutpriceService->update_by_id($cut_info['id'], $data_up);

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }

        return result_json(TRUE, '成功砍价'.format_price($data['cutprice']).'元!');
    }

}