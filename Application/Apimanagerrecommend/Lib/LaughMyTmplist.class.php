<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMyTmplist extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $type = I('type');
        $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
        $mylist = $UserTemplateService->get_by_uid($this->uid);
        $tids = result_to_array($mylist, 'tid');
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $tmp_list = $TemplateService->get_by_type_ids($type, $tids);

        return result_json(TRUE, '获取成功',$tmp_list);
    }


}