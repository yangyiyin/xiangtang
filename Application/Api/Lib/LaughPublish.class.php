<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughPublish extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $content = $this->post_data['content'];
        if (!$content) {
            return result_json(false, '请输入内容!');
        }

        //查询是否可以投稿
        $UserOperateLimitService = \Common\Service\UserOperateLimitService::get_instance();
        $publish = $UserOperateLimitService->get_info_by_uid_type($this->uid, \Common\Model\NfUserOperateLimitModel::TYPE_PUBLISH);
        if ($publish && $publish['sum'] > 2) {
            return result_json(false, '今天您已投稿3篇,不能再投稿了,审核人员抱怨了!');
        } else {
            $UserOperateLimitService->add_sum($this->uid, \Common\Model\NfUserOperateLimitModel::TYPE_PUBLISH, 1);
        }

        $ArticleService = \Common\Service\ArticleService::get_instance();
        $data = [];
        $data['content'] = $content;
        $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
        $data['uid'] = $this->uid;
        $data['from'] = \Common\Model\NfArticleModel::FROM_CUSTOM;
        $ret = $ArticleService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }


        $AccountLogService = \Common\Service\AccountLogService::get_instance();
        $account_data = [];
        $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_PUBLISH;
        $account_data['sum'] = \Common\Model\NfAccountLogModel::$TYPE_VALUE_MAP[\Common\Model\NfAccountLogModel::TYPE_PUBLISH];
        $account_data['oid'] = 0;
        $account_data['uid'] = $this->uid;
        $account_data['pay_no'] = 0;
        $AccountLogService->add_one($account_data);

        $AccountService = \Common\Service\AccountService::get_instance();
        $AccountService->add_account($this->uid, $account_data['sum']);


        return result_json(TRUE, '发布成功');
    }

}