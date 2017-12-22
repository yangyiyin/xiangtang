<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMyCollectIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {

        $p = I('p',1);

        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();
        $collects = $ArticleEventsService->get_by_uid_type($this->uid, \Common\Model\NfArticleEventsModel::TYPE_COLLECT);
        $collects_aids = result_to_array($collects, 'aid');
        if (!$collects_aids) {
            return result_json(TRUE, '', []);
        }

        $ArticleService = \Common\Service\ArticleService::get_instance();
        $where = [];
        $where['id'] = ['in', $collects_aids];
        $where['status'] = \Common\Model\NfArticleModel::STATUS_OK;
        list($list, $count) = $ArticleService->get_by_where($where,'id desc',$p);


        $list = $this->convert($list);
        return result_json(TRUE, '', $list);
    }

    protected function convert($list) {
        $aids = result_to_array($list);
        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();
        $likes = $ArticleEventsService->get_by_aids_uid_type($aids, $this->uid, \Common\Model\NfArticleEventsModel::TYPE_LIKE);
        $collects = $ArticleEventsService->get_by_aids_uid_type($aids, $this->uid, \Common\Model\NfArticleEventsModel::TYPE_COLLECT);
        $likes_map = result_to_map($likes, 'aid');
        $collects_map = result_to_map($collects, 'aid');

        $uids = result_to_array($list, 'uid');
        $UserService = \Common\Service\UserService::get_instance();
        $users = $UserService->get_by_ids($uids);
        $users_map = result_to_map($users, 'id');

        //获取点击相关
        $ArticleCliksService = \Common\Service\ArticleClicksService::get_instance();
        $clicks = $ArticleCliksService->get_by_aids($aids);
        $clicks_map = result_to_complex_map($clicks, 'aid');
        foreach ($clicks_map as $aid => $click) {
            foreach ($click as $key => $_click) {
                if ($_click['type'] == \Common\Model\NfArticleClicksModel::TYPE_LIKE) {
                    $clicks_map[$aid]['like'] = $_click;
                }
                if ($_click['type'] == \Common\Model\NfArticleClicksModel::TYPE_COLLECT) {
                    $clicks_map[$aid]['collect'] = $_click;
                }
            }

        }

        if ($list) {
            foreach ($list as $key => $_li) {
                if (isset($likes_map[$_li['id']])) {
                    $list[$key]['is_like'] = true;
                }

                if (isset($collects_map[$_li['id']])) {
                    $list[$key]['is_collect'] = true;
                }
                $list[$key]['user'] = [];
                if ($_li['from'] == \Common\Model\NfArticleModel::FROM_CUSTOM) {

                    if (isset($users_map[$_li['uid']])) {

                        $list[$key]['user'] =  $users_map[$_li['uid']] ;
                        $list[$key]['user']['avatar'] = item_img(get_cover(46, 'path'));
                    }


                } elseif ($_li['from'] == \Common\Model\NfArticleModel::FROM_ADMIN) {
                    $list[$key]['user'] = [
                        'user_name' => '纯笑笑',
                        'avatar' => '../../resource/images/img_75.png'
                    ];
                } else {

                }

                $list[$key]['like_count'] = isset($clicks_map[$_li['id']]['like']['count']) ? $clicks_map[$_li['id']]['like']['count'] : 0;
                $list[$key]['collect_count'] = isset($clicks_map[$_li['id']]['collect']['count']) ? $clicks_map[$_li['id']]['collect']['count'] : 0;

            }

        }
        return $list;
    }

}