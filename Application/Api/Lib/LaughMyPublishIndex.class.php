<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughMyPublishIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {

        $p = I('p',1);
        $ArticleService = \Common\Service\ArticleService::get_instance();
        $where = [];
        $where['from'] = \Common\Model\NfArticleModel::FROM_CUSTOM;
        $where['uid'] = $this->uid;
        list($list, $count) = $ArticleService->get_by_where($where,'id desc',$p);


        $list = $this->convert($list);
        return result_json(TRUE, '', $list);
    }

    protected function convert($list) {
//        $aids = result_to_array($list);
//        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();
//        $likes = $ArticleEventsService->get_by_aids_uid_type($aids, $this->uid, \Common\Model\NfArticleEventsModel::TYPE_LIKE);
//        $collects = $ArticleEventsService->get_by_aids_uid_type($aids, $this->uid, \Common\Model\NfArticleEventsModel::TYPE_COLLECT);
//        $likes_map = result_to_map($likes, 'aid');
//        $collects_map = result_to_map($collects, 'aid');

//        $uids = result_to_array($list, 'uid');
//        $UserService = \Common\Service\UserService::get_instance();
//        $users = $UserService->get_by_ids($uids);
//        $users_map = result_to_map($users, 'id');
        $status_map = \Common\Model\NfArticleModel::$status_map;
        if ($list) {
            foreach ($list as $key => $_li) {
                $list[$key]['status_desc'] = isset($status_map[$_li['status']]) ? $status_map[$_li['status']] : '未知状态';
            }

        }
        return $list;
    }

}