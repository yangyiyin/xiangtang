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
class UserPhones extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserPhones;
    public function init() {
        $this->UserPhones = Service\UserPhoneService::get_instance();
    }

    public function excute() {

        $phones = $this->UserPhones->get_all(['uid'=>$this->uid]);
        $phones = $phones ? $phones : [];
        return result_json(TRUE, '', $phones);

    }
}