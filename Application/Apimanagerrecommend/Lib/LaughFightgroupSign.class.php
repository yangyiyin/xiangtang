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

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        $price = 0;
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
            if (isset(!$page_info['tmp_data']['page']) || !$page_info['tmp_data']['page']) {
                return result_json(false, '页面信息异常!');
            }
            //获取拼团价格
            foreach ($page_info['tmp_data']['page'] as $item) {
                if ($item['type'] == 'fight_group') {
                    $price = isset($item['fight_group_price']) ? $item['fight_group_price'] : 0;
                }
            }
        }
        if (!$price) {
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
        $data['price'] = $price;
        $data['group'] = json_encode('uid'=>$this->user_info['id'], 'user_name'=>$this->user_info['user_name'], 'avatar'=>item_img($this->user_info['avatar']), 'user_tel'=>$this->user_info['user_tel']);
        if ($PageFightgroupService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            return result_json(false, '您已开团!');
        }
        $ret = $PagePraiseService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
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