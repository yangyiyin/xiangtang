<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughSign extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $phone = isset($this->post_data['phone']) ? $this->post_data['phone'] : '';

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);

        if (!$page_info) {
            return result_json(false, '页面不存在!');
        }

        $VipService = \Common\Service\VipService::get_instance();
        $ret = $VipService->is_vip($page_info['uid']);
        if (!$ret->success) {
            return result_json(false, '对不起,当前链接暂无法报名');
        }

        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }
        if ($page_info['start_time'] && time() < $page_info['start_time']) {
            return result_json(false, '活动尚未开始!');
        }

        if ($page_info['end_time'] && time() > $page_info['end_time']) {
            return result_json(false, '活动已结束!');
        }
        //检测库存
        if ($page_info['stock'] > 0 && ($page_info['stock'] - $page_info['sell_num']) <= 0) {
            return result_json(false, '对不起,当前库存不足');
        }

        //存入
        $PageSignService = \Common\Service\PageSignService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['phone'] = $phone;
        if ($PageSignService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            return result_json(false, '您已报名');
        }

        $ret = $PageSignService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //生成提货码
        $pick_code = \Common\Service\PageSignService::pick_code_sign . sprintf("%04d", $ret->data);
        $ret = $PageSignService->update_by_id($ret->data, ['pick_code'=>$pick_code]);
        if (!$ret->success) {
            return result_json(false, '系统异常,您的提货码生成失败,请联系客服');
        }
        //发送短信 todo
        //curl_post_raw('http://api.88plus.net/index.php/waibao/common/send_pick_code_manager_recommend', json_encode(['phone'=>$phone,'activity_name'=>$page_info['title'],'pick_phone'=>$phone,'pick_code'=>$pick_code]));

        //扣库存
        //检测库存
        if ($page_info['stock'] > 0) {
            $PageService->setInc(['id'=>$page_info['id']], 'sell_num', 1);
        }

        //记录我的手机号
        $UserPhoneService = Service\UserPhoneService::get_instance();
        if (!$UserPhoneService->get_one(['uid'=>$this->uid, 'phone'=>$phone])) {
            $UserPhoneService->add_one(['uid'=>$this->uid, 'phone'=>$phone]);
        }

        //记录卖家和买家关系记录
        $UserRelationService = Service\UserRelationService::get_instance();
        if (!$UserRelationService->get_one(['uid'=>$this->uid, 'seller_uid'=>$page_info['uid']])) {
            $UserPhoneService->add_one(['uid'=>$this->uid, 'seller_uid'=>$page_info['uid']]);
        }

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }

        return result_json(TRUE, '报名成功!');
    }

}