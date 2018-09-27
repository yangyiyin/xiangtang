<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class VipPrice extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $VipService;
    public function init() {
        $this->VipService = Service\VipService::get_instance();
    }

    public function excute() {

        $price_info_list = Service\VipService::$price_info_list;
        result_json(TRUE, '', $price_info_list);
    }
}