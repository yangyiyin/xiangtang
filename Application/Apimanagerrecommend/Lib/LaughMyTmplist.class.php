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
        $type = I('type',0);
        $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
        $mylist = $UserTemplateService->get_by_uid($this->uid);
        $tids = result_to_array($mylist, 'tid');
        $tids = array_slice($tids, 0, 20);//取最新的20个
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $tmp_list = $TemplateService->get_by_type_ids($type, $tids);
        $tmp_list_map = result_to_map($tmp_list);
        $new_list = [];
        foreach ($tids as $tid) {
            if (isset($tmp_list_map[$tid])) {
                $new_list[] = $tmp_list_map[$tid];
            }
        }
        return result_json(TRUE, '获取成功',$new_list);
    }


}