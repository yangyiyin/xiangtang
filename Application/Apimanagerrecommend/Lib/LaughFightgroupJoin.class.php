<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughFightgroupJoin extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];
        $phone = isset($this->post_data['phone']) ? $this->post_data['phone'] : '';
        $pay_no = $this->post_data['pay_no'];

        //查询支付状态
        $ActivityPayService = \Common\Service\ActivityPayService::get_instance();
        $pay_info = $ActivityPayService->get_by_pay_no($pay_no);
        if (!$pay_info || $pay_info['status'] != 1) {
            return result_json(false, '您还未支付,暂无法下单!!');
        }

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if (!$page_info || !$extra_uid) {
            return result_json(false, '页面不存在!');
        }

        $VipService = \Common\Service\VipService::get_instance();
        $ret = $VipService->is_vip($page_info['uid']);
        if (!$ret->success) {
            return result_json(false, '对不起,当前链接暂无法参团');
        }

        if ($extra_uid == $this->uid) {
            return result_json(false, '您不能参加自己的拼团!');
        }

        $price = 0;
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
            if (!isset($page_info['tmp_data']['page']) || !$page_info['tmp_data']['page']) {
                return result_json(false, '页面信息异常!');
            }
            //获取拼团价格
            foreach ($page_info['tmp_data']['page'] as $item) {
                if ($item['type'] == 'fight_group') {
                    $price = isset($item['fight_group_price']) ? $item['fight_group_price'] * 100 : 0;
                }
            }
        }
        if (!$price) {
            return result_json(false, '页面信息异常2!');
        }

        if ($page_info['start_time'] && time() < strtotime($page_info['start_time'])) {
            return result_json(false, '活动尚未开始!');
        }

        if ($page_info['end_time'] && time() > strtotime($page_info['end_time'])) {
            return result_json(false, '活动已结束!');
        }

        //检测库存
        if ($page_info['stock'] > 0 && ($page_info['stock'] - $page_info['sell_num']) <= 0) {
            return result_json(false, '对不起,当前库存不足');
        }

        //存入
        $PageFightgroupService = \Common\Service\PageFightgroupService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        $data['price'] = $price;
        $data['phone'] = $phone;

        $group_info = $PageFightgroupService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$group_info) {
            return result_json(false, '拼团信息异常!');
        }

        if ($group_info['status'] != \Common\Service\PageFightgroupService::STATUS_INIT) {
            return result_json(false, '参团失败:此单已成团,请选择其他参团!');
        }

        if ($PageFightgroupService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {
            return result_json(false, '您已参团!');
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
        curl_post_raw('http://api.'.C('BASE_WEB_HOST').'/index.php/waibao/common/send_pick_code_manager_recommend', json_encode(['phone'=>$phone,'activity_name'=>$page_info['title'],'pick_phone'=>$phone,'pick_code'=>$pick_code]));

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

        //更新主团信息
        $ret = $PageFightgroupService->join_group($group_info, $data);
        if (!$ret) {
            //todo 记录日志,回滚
            return result_json(false, '参团失败,请联系客服');
        }

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }

        return result_json(TRUE, ' 参团成功!');
    }

}