<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughVote extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $vote_id = $this->post_data['vote_id'];
        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {

                return result_json(false, '报名已结束!');
            }
        }

        //存入
        $PageSortUserService = \Common\Service\PageSortUserService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['sort_id'] = $vote_id;
        if ($PageSortUserService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['sort_id'])) {

            return result_json(false, '您已投票!');
        }
        $data['sort_id'] = $vote_id;
        $ret = $PageSortUserService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        $PageSortService = \Common\Service\PageSortService::get_instance();
        $sort = $PageSortService->get_by_sort_id_page_id($vote_id, $id);
        if ($sort) {
            $data = [];
            $data['sum'] = $sort['sum'] + 1;
            $PageSortService->update_by_id($sort['id'], $data);
        } else {
            $data = [];
            $data['sort_id'] = $vote_id;
            $data['page_id'] = $id;
            $data['sum'] = 1;
            $PageSortService->add_one($data);
        }

        return result_json(TRUE, '投票成功!');
    }

}