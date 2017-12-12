<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class CooperationController extends BaseController {
    public function _empty() {
        $this->excute_api('Api\Lib\CooperationList');

    }

    public function detail() {
        $this->excute_api('Api\Lib\CooperationDetail');
    }

}