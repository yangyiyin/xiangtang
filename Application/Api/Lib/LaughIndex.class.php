<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {

        $p = I('p',1);
        $ArticleService = \Common\Service\ArticleService::get_instance();
        $where = [];
        $where['status'] = \Common\Model\NfArticleModel::STATUS_OK;
        list($list, $count) = $ArticleService->get_by_where_with_pre_one($where,'publish_time desc,id desc',$p);

        $list = $this->convert($list);

        if ($list && count($list) > 1 && $p != 1) {
            if ($list[0]['title'] && isset($list[2]['title']) && $list[0]['title'] == $list[2]['title']) {
                unset($list[2]);
            }
            unset($list[0]);
            unset($list[1]);


            $list = array_values($list);
        }
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
        $list_new = [];
        $time = '';
        if ($list) {
            foreach ($list as $key => $_li) {
                if (isset($likes_map[$_li['id']])) {
                    $_li['is_like'] = true;
                }

                if (isset($collects_map[$_li['id']])) {
                    $_li['is_collect'] = true;
                }
                $_li['user'] = [];
                if ($_li['from'] == \Common\Model\NfArticleModel::FROM_CUSTOM) {

                    if (isset($users_map[$_li['uid']])) {

                        $_li['user'] =  $users_map[$_li['uid']] ;
                        $_li['user']['avatar'] = item_img(get_cover(46, 'path'));
                    }

                } elseif ($_li['from'] == \Common\Model\NfArticleModel::FROM_ADMIN) {
                    $_li['user'] = [
                        'user_name' => '纯笑笑',
                        'avatar' => '../../resource/images/img_75.png'
                    ];
                } else {

                }

                $cur_time = substr($_li['publish_time'], 0, 10);
                if ($cur_time != $time) {
                    $time = $cur_time;

                    if ($cur_time == date('Y-m-d')) {
                        $title = '今日笑话';
                    } elseif (strtotime($cur_time) == strtotime(date('Y-m-d')) - 3600*24) {
                        $title = '昨日笑话';
                    } else {
                        $title = '往期笑话';
                    }

                    $list_new[] = [
                        'title' => $title,
                        'block_type' =>'title'
                    ];

                }
                $list_new[] = $_li;
            }

        }

//        var_dump($list_new);
        return $list_new;
    }

}