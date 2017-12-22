<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Model;
use Common\Service;
class UserReceivingInfo_default extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserReceivingService;
    public function init() {
        $this->UserReceivingService = Service\UserReceivingService::get_instance();
    }

    public function excute() {
        $receiving = $this->UserReceivingService->get_by_uid_default($this->uid);
        $data = NULL;
        if ($receiving) {
            $receiving['id'] = (int) $receiving['id'];
            $data = convert_obj($receiving, 'id,name,tel,province,city,area,address,address_full');

        }
        return result_json(TRUE, '', $data);
    }
}