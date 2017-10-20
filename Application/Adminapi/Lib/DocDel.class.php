<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;

class DocDel extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {

        $id = I('id');
        $service = Service\DocsService::get_instance();

        $ret = $service->del_by_id($id);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }




        return result_json(true, '操作成功');
    }



}