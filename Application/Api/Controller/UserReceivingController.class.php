<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class UserReceivingController extends BaseController {
    public function info() {
        $this->excute_api('Api\Lib\UserReceivingInfo');
    }

    public function info_default() {
        $this->excute_api('Api\Lib\UserReceivingInfo_default');
    }

    public function modify() {
        $this->excute_api('Api\Lib\UserReceivingModify');
    }
    public function del() {
        $this->excute_api('Api\Lib\UserReceivingDel');
    }
    public function set_default() {
        $this->excute_api('Api\Lib\UserReceivingSet_default');
    }
}