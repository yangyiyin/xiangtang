<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledWorkInfoApply extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $ArticleService;
    public function init() {
        $this->ArticleService = Service\ArticleService::get_instance();
    }

    public function excute() {
        $data = [];
        $data['title'] = $this->post_data['title'];
        $data['content'] = $this->post_data['content'];
        $data['from_uid'] = $this->uid;
        $data['status'] = \Common\Model\NfArticleModel::STATUS_SUBMIT;
        if (!$data['title'] || !$data['content']) {
            return result_json(false, '参数不完整,请检查标题内容已填写!');
        }
        $ret = $this->ArticleService->add_one($data);
        if (!$ret->success) {
            return result_json(false, '提交失败!', '');
        }
        $id = $ret->data;
        return result_json(TRUE, '提交成功!', $id);
        
    }

}