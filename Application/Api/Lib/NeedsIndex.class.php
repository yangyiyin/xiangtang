<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $NeedsService;
    public function init() {
        $this->NeedsService = Service\NeedsService::get_instance();
    }

    public function excute() {
        $page = I('get.p') ? I('get.p') : 1;
        $type = I('get.type');
        $where = ['type' => $type];
        $list = $this->NeedsService->get_by_where($where, 'id desc', $page);
        $list = convert_objs($list, 'id,type,title,content,create_time');
        return result_json(TRUE, '', $list);
    }

}