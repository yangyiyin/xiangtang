<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughTmpInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $id = I('id');

        $TemplateService = \Common\Service\TemplateService::get_instance();
        $info = $TemplateService->get_info_by_id($id);

        if ($info) {
            $info['content'] = json_decode($info['content'], true);
        }

        foreach ($info['content']['page'] as $_page) {
            if ($_page['type'] == 'sign') {
                $info['content']['sign_list'] = true;
            }
            if ($_page['type'] == 'cutprice_btn') {
                $info['content']['cutprice_list'] = true;
            }
            if ($_page['type'] == 'praise') {
                $info['content']['praise_list'] = true;
            }
            if ($_page['type'] == 'vote') {
                $info['content']['vote_list'] = true;
            }

        }
        $VipService = \Common\Service\VipService::get_instance();
        $ret = $VipService->is_vip($this->uid);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
//        return result_json(false, '您的vip已到期,请联系客服续费', $info);
        return result_json(TRUE, '获取成功', $info);
    }


}