<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $NeedsService;
    public function init() {
        $this->NeedsService = Service\NeedsService::get_instance();
    }

    public function excute() {
        $page = I('get.p') ? I('get.p') : 1;
        $type = I('get.type');
        $where = ['type' => $type];
        $is_mine = I('get.is_mine');
        if ($is_mine) {
            $where['uid'] = $this->uid;
        }
        list($list, $count) = $this->NeedsService->get_by_where($where, 'id desc', $page);
        if ($list) {
            $map = \Common\Model\NfNeedsModel::$status_map;
            foreach ($list as $key => $li) {
                $list[$key]['status_desc'] = isset($map[$li['status']]) ? $map[$li['status']] : '未知';
            }
        }
        $list = convert_objs($list, 'id,type,title,create_time,remark,status_desc');

        $has_more = has_more($count, $page, Service\NeedsService::$page_size);
        return result_json(TRUE, '', ['list' => $list, 'has_more' => $has_more]);
    }

}