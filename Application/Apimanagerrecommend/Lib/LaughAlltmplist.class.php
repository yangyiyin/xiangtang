<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughAlltmplist extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $type = I('type');
        $p = I('p',1);
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $where = [];
        $where['type'] = $type;
        list($list, $count) = $TemplateService->get_by_where($where, 'id desc', $p);

        $result = [];
        $result['list'] = $this->convert($list);
        $result['has_more'] = has_more($count, $p, \Common\Service\TemplateService::$page_size);

        return result_json(TRUE, '获取成功',$result);
    }

    public function convert($list) {
        if ($list) {
            $ids = result_to_array($list);
            $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
            $mylist = $UserTemplateService->get_by_tids_uid($ids, $this->uid);
            $mylist_map = result_to_map($mylist, 'tid');
            foreach ($list as $k => $value) {
                $list[$k]['is_add'] = isset($mylist_map[$value['id']]);
            }
        }
        return $list;
    }
}