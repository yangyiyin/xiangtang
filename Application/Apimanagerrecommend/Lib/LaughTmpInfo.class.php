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
            $info['content'] = json_decode($info['content']);
        }
        return result_json(TRUE, '获取成功', $info);
    }


}