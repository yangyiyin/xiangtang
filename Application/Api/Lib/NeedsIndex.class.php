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

        $type = I('get.type');
        $list = $this->NeedsService->get_by_type($type);
        $list = convert_objs($list, 'id,type,title,content,create_time');
        return result_json(TRUE, '', $list);
    }

}