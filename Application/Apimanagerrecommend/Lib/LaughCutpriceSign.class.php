<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughCutpriceSign extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $phone = isset($this->post_data['phone']) ? $this->post_data['phone'] : '';
        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {
                return result_json(false, '报名已结束');
            }
        }
        $price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
            }
        }
        if (!$price) {
            return result_json(false, '活动信息异常');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();

        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            return result_json(false, '您已报名');
        }

        $data['price'] = $price * 100;
        $data['phone'] = $phone;
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //生成提货码
        $pick_code = \Common\Service\PageCutpriceService::pick_code_cutprice . sprintf("%04d", $ret->data);
        $ret = $PageCutpriceService->update_by_id($ret->data, ['pick_code'=>$pick_code]);
        if (!$ret->success) {
            return result_json(false, '系统异常,您的提货码生成失败,请联系客服');
        }
        //发送短信 todo
        curl_post_raw('http://api.88plus.net/index.php/waibao/common/send_pick_code_manager_recommend', json_encode(['phone'=>$phone,'activity_name'=>$page_info['title'],'pick_phone'=>$phone,'pick_code'=>$pick_code]));

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }
        return result_json(TRUE, '报名成功!');
    }

}