<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class VipController extends BaseController {
    public function get_vip_price() {
        $this->excute_api('Apimanagerrecommend\Lib\VipPrice');
    }
    public function extend() {
        $this->excute_api('Apimanagerrecommend\Lib\VipExtend');
    }
}