<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Model;
use Common\Service;
class UserReceivingDel extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserReceivingService;
    public function init() {
        $this->UserReceivingService = Service\UserReceivingService::get_instance();
    }

    public function excute() {
        $id = I('post.rid');
        $id = $this->post_data['rid'];
        if (!$id) {
            return result_json(FALSE, '没有收到收货地址id');
        }

        $receiving = $this->UserReceivingService->get_info_by_id($id);
        if (!$receiving) {
            return result_json(FALSE, '没有该收货地址');
        }
        $ret = $this->UserReceivingService->del_by_id($id);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '修改成功');
    }
}