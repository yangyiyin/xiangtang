<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughDelUserPage extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        if (!$id) {
            return result_json(false, '参数错误');
        }
        $UserPageService = \Common\Service\UserPageService::get_instance();

        $ret = $UserPageService->update_by_id($id,['status'=>\Common\Service\UserPageService::STATUS_DISABLE]);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        return result_json(TRUE, '删除成功');
    }


}