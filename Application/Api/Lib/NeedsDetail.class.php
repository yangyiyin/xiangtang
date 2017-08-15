<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsDetail extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $NeedsService;
    public function init() {
        $this->NeedsService = Service\NeedsService::get_instance();
    }

    public function excute() {

        $id = I('get.id');
        $data = $this->NeedsService->get_info_by_id($id);
        $data = convert_obj($data, 'id,type,title,content,create_time');
        return result_json(TRUE, '', $data);
    }

}