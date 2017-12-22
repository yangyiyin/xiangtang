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
class UserReceivingInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserReceivingService;
    public function init() {
        $this->UserReceivingService = Service\UserReceivingService::get_instance();
    }

    public function excute() {
        $receivings = $this->UserReceivingService->get_by_uid($this->uid);
        $data = [];
        foreach ($receivings as $_receiving) {
            $_receiving['id'] = (int) $_receiving['id'];
            $temp = convert_obj($_receiving, 'id,name,tel,province,city,area,address,address_full,is_default');
//            if(isset($temp->tel)) {
//                //$temp->tel = tel_num_security($temp->tel);
//            }
            $temp->is_default = (bool) $temp->is_default;
            $data[] = $temp;
        }
        return result_json(TRUE, '', ['list' => $data]);
    }
}