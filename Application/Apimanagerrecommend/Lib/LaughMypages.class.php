<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMypages extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $type = I('type');
        $p = I('p',1);
        $PageService = \Common\Service\PageService::get_instance();
        $where = [];
        $where['uid'] = $this->uid;
        list($list, $count) = $PageService->get_by_where($where, 'id desc', $p);

        $result = [];
        $result['list'] = $this->convert($list);
        $result['has_more'] = has_more($count, $p, \Common\Service\PageService::$page_size);

        return result_json(TRUE, '获取成功',$result);
    }

    public function convert($list) {
        if ($list) {

            foreach ($list as $k => $value) {
                $list[$k]['active_status'] = '进行中';
                if ($value['start_time'] && time() < strtotime($value['start_time'])) {
                    $list[$k]['active_status'] = '尚未开始';
                }

                if ($value['end_time'] && time() > strtotime($value['end_time'])) {
                    $list[$k]['active_status'] = '已结束';
                }

            }
        }
        return $list;
    }
}