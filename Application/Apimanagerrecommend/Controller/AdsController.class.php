<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class AdsController extends BaseController {
    public function index() {
        $this->excute_api('Apimanagerrecommend\Lib\AdsIndex');
    }
}