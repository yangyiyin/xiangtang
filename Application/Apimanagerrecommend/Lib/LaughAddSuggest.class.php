<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughAddSuggest extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        $content = $this->post_data['content'];

        $SuggestService = \Common\Service\SuggestService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['content'] = $content;
        $ret = $SuggestService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        return result_json(TRUE, '提交成功');
    }


}