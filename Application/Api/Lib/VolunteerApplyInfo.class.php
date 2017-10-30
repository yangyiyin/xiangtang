<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class VolunteerApplyInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $VolunteerService;
    public function init() {
        $this->VolunteerService = Service\VolunteerService::get_instance();
    }

    public function excute() {

        $info = $this->VolunteerService->get_info_by_uid($this->uid);

        if ($info) {
            return result_json(TRUE, '获取成功!', $info);
        } else {
            return result_json(false, '您还没有申请记录!', '');
        }
        
    }

}