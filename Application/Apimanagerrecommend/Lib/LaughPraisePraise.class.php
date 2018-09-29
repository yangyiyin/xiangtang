<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPraisePraise extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];
        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);

        if (!$page_info) {
            return result_json(false, '页面不存在!');
        }

        $VipService = \Common\Service\VipService::get_instance();
        $ret = $VipService->is_vip($page_info['uid']);
        if (!$ret->success) {
            return result_json(false, '对不起,当前链接暂无法点赞');
        }

        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

        if ($page_info['start_time'] && time() < strtotime($page_info['start_time'])) {
            return result_json(false, '活动尚未开始!');
        }

        if ($page_info['end_time'] && time() > strtotime($page_info['end_time'])) {
            return result_json(false, '活动已结束!');
        }


        //存入
        $PagePraiseService = \Common\Service\PagePraiseService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        $data['sum'] = 0;
        if ($PagePraiseService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {

            return result_json(false, '您已点赞!');
        }
        $praise_info = $PagePraiseService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$praise_info) {

            return result_json(false, '点赞信息异常!');
        }

        $data['user_name'] = $this->user_info['user_name'] ? $this->user_info['user_name'] : ($this->user_info['wechat_user_info']?$this->user_info['wechat_user_info']['nickName']:'');
        $data['avatar'] = $this->user_info['avatar'] ? $this->user_info['avatar'] : ($this->user_info['wechat_user_info']?$this->user_info['wechat_user_info']['avatarUrl']:'');


        $ret = $PagePraiseService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        $data_up = [];
        $data_up['sum'] = $praise_info['sum'] + 1;
        $PagePraiseService->update_by_id($praise_info['id'], $data_up);

        //记录我的报名
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $user_page = $UserPageService->get_by_uid_page_id($this->uid, $id);
        if (!$user_page) {
            $UserPageService->add_one(['uid'=>$this->uid, 'page_id'=>$id]);
        }

        return result_json(TRUE, '点赞成功!');
    }

}