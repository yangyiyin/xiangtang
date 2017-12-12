<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class CooperationDetail extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $CooperationService;
    public function init() {
        $this->CooperationService = Service\CooperationService::get_instance();
    }

    public function excute() {
        $id = I('id');
        $info = $this->CooperationService->get_info_by_id($id);
        if (!$info) {
            return result_json(false, '未找到合作单位信息');
        }
        $info = convert_obj($info, 'id,content,create_time');
        return result_json(TRUE, '', $info);
        
    }

}