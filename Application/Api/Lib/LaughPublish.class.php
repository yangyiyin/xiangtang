<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughPublish extends BaseSapi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $content = $this->post_data['content'];
        if (!$content) {
            return result_json(false, '请输入内容!');
        }
        $ArticleService = \Common\Service\ArticleService::get_instance();
        $data = [];
        $data['content'] = $content;
        $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
        $ret = $ArticleService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        return result_json(TRUE, '发布成功');
    }

}