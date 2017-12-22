<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughLike extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $aid = $this->post_data['aid'];
        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();

        $has_one = $ArticleEventsService->get_by_aid_uid_type($aid,$this->uid,\Common\Model\NfArticleEventsModel::TYPE_LIKE);

        if ($has_one) {
            return result_json(TRUE, '您已点赞');
        }

        $data = [];
        $data['type'] = \Common\Model\NfArticleEventsModel::TYPE_LIKE;
        $data['desc'] = '点赞';
        $data['uid'] = $this->uid;
        $data['aid'] = $aid;

        $ret = $ArticleEventsService->add_one($data);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //获取文章作者uid
        $ArticleService = \Common\Service\ArticleService::get_instance();
        $info = $ArticleService->get_info_by_id($aid);
        if ($info && $info['uid'] && $info['from'] == \Common\Model\NfArticleModel::FROM_CUSTOM) {
            $AccountLogService = \Common\Service\AccountLogService::get_instance();
            $account_data = [];
            $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_LIKED;
            $account_data['sum'] = \Common\Model\NfAccountLogModel::$TYPE_VALUE_MAP[\Common\Model\NfAccountLogModel::TYPE_LIKED];
            $account_data['oid'] = 0;
            $account_data['uid'] = $info['uid'];
            $account_data['pay_no'] = 0;
            $AccountLogService->add_one($account_data);

            $AccountService = \Common\Service\AccountService::get_instance();
            $AccountService->add_account($info['uid'], $account_data['sum']);
        }

        //增加文章点击数
        $ArticleCliksService = \Common\Service\ArticleClicksService::get_instance();
        $info = $ArticleCliksService->get_info_by_aid($aid, \Common\Model\NfArticleClicksModel::TYPE_COLLECT);
        if ($info) {
            $data = [];
            $data['count'] = $info['count'] + 1;
            $ArticleCliksService->update_by_id($data, $info['id']);
        } else {
            $data = [];
            $data['type'] = \Common\Model\NfArticleClicksModel::TYPE_LIKE;
            $data['desc'] = '点赞';
            $data['count'] = 1;
            $data['aid'] = $aid;
            $ArticleCliksService->add_one($data);
        }


        return result_json(TRUE, '谢谢喜欢');
    }

}