<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class CartController extends BaseController {
    public function _empty() {
        $this->excute_api('Api\Lib\CartList');
    }

    public function Modify() {
        $this->excute_api('Api\Lib\CartModify');
    }

    public function del() {
        $this->excute_api('Api\Lib\CartDel');
    }
}