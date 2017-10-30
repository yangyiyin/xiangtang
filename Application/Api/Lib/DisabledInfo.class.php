<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $DisabledManService;
    public function init() {
        $this->DisabledManService = Service\DisabledManService::get_instance();
    }

    public function excute() {
        $id = I('id',0);
        if (!$id) {
            return result_json(false, '参数错误!');
        }
        $where = [];
        $info = $this->DisabledManService->get_info_by_id($id);

        if ($info) {
            $info['img'] = item_img($info['img']);
        }
        return result_json(TRUE, '', $info);
        
    }

    private function convert($data) {
        if ($data) {

        }
        return $data;

    }

}