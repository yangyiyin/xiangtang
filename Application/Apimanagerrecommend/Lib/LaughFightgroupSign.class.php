<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughFightgroupSign extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $pay_no = $this->post_data['pay_no'];
        $phone = isset($this->post_data['phone']) ? $this->post_data['phone'] : '';

        //查询支付状态
        $ActivityPayService = \Common\Service\ActivityPayService::get_instance();
        $pay_info = $ActivityPayService->get_by_pay_no($pay_no);
        if (!$pay_info || $pay_info['status'] != 1) {
            return result_json(false, '您还未支付,暂无法下单!');
        }

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

        $price = $max_number = 0;
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
            if (!isset($page_info['tmp_data']['page']) || !$page_info['tmp_data']['page']) {
                return result_json(false, '页面信息异常!');
            }
            //获取拼团价格
            foreach ($page_info['tmp_data']['page'] as $item) {
                if ($item['type'] == 'fight_group') {
                    $price = isset($item['fight_group_price']) ? $item['fight_group_price'] * 100 : 0;
                    $max_number = isset($item['fight_group_number']) ? $item['fight_group_number'] : 0;

                }
            }
        }
        if (!$price || !$max_number) {
            return result_json(false, '页面信息异常2!');
        }
        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {
                return result_json(false, '活动已结束!');
            }
        }

        //存入
        $PageFightgroupService = \Common\Service\PageFightgroupService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['group_number'] = 1;
        $data['max_number'] = $max_number;
        $data['price'] = $price;
        $data['phone'] = $phone;
        $data['group'] = json_encode([['uid'=>$this->user_info['id'], 'user_name'=>$this->user_info['user_name'], 'avatar'=>item_img($this->user_info['avatar']), 'user_tel'=>$this->user_info['user_tel']]]);
        if ($PageFightgroupService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            return result_json(false, '您已开团!');
        }
        $ret = $PageFightgroupService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //生成提货码
        $pick_code = \Common\Service\PageFightgroupService::pick_code_fightgroup . sprintf("%04d", $ret->data);
        $ret = $PageFightgroupService->update_by_id($ret->data, ['pick_code'=>$pick_code]);
        if (!$ret->success) {
            return result_json(false, '系统异常,您的提货码生成失败,请联系客服');
        }
        //发送短信 todo
        curl_post_raw('http://api.88plus.net/index.php/waibao/common/send_pick_code_manager_recommend', json_encode(['phone'=>$phone,'activity_name'=>$page_info['title'],'pick_phone'=>$phone,'pick_code'=>$pick_code]));

        //记录我的手机号
        $UserPhoneService = Service\UserPhoneService::get_instance();
        if (!$UserPhoneService->get_one(['uid'=>$this->uid, 'phone'=>$phone])) {
            $UserPhoneService->add_one(['uid'=>$this->uid, 'phone'=>$phone]);
        }

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }

        return result_json(TRUE, '开团成功!');
    }

}