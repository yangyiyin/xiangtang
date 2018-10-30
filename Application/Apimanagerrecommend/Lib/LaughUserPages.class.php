<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughUserPages extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $type = I('type');
        $p = I('p',1);
        $UserPageService = \Common\Service\UserPageService::get_instance();
        $where = [];
        $where['uid'] = $this->uid;
        $where['status'] = \Common\Service\UserPageService::STATUS_ENABLE;
        list($list, $count) = $UserPageService->get_by_where($where, 'id desc', $p);

        $result = [];
        $result['list'] = $this->convert($list);
        $result['has_more'] = has_more($count, $p, \Common\Service\UserPageService::$page_size);

        return result_json(TRUE, '获取成功',$result);
    }

    public function convert($list) {
        if ($list) {
            $ids = result_to_array($list,'page_id');
            $PageService = \Common\Service\PageService::get_instance();
            $pages = $PageService->get_by_ids($ids);
            $pages_map = result_to_map($pages);

            //
            foreach ($list as $k => $value) {
                $list[$k]['page'] = isset($pages_map[$value['page_id']]) ? $pages_map[$value['page_id']] : [];
            }
        }
        return $list;
    }
}