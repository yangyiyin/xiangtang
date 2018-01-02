<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughAddTmp extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        if (!$id) {
            return result_json(false, '参数错误');
        }
        $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['tid'] = $id;
        $ret = $UserTemplateService->add_one($data);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        return result_json(TRUE, '添加成功');
    }


}