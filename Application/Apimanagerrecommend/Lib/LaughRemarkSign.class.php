<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughRemarkSign extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $type = $this->post_data['type'];
        $remark = $this->post_data['remark'];

        if (!$id) {
            return result_json(false, '参数错误');
        }

        if ($type == 'sign') {
            $Service = \Common\Service\PageSignService::get_instance();

        } elseif($type == 'praise') {
            $Service = \Common\Service\PagePraiseService::get_instance();
        } elseif($type == 'cutprice') {
            $Service = \Common\Service\PageCutpriceService::get_instance();
        }

        if (!isset($Service)) {
            return result_json(false, '参数错误');
        }
        $ret = $Service->update_by_id($id, ['remark' => $remark]);


        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        return result_json(TRUE, '备注成功');
    }


}