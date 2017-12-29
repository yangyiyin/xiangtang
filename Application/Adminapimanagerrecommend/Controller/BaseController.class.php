<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapimanagerrecommend\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize() {
    }

    protected function excute_api($api){
        $api_instance = new $api();
        $api_instance->excute();
        exit();
    }
}