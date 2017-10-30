<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class ActivityController extends BaseController {
    public function apply() {
        $this->excute_api('Api\Lib\ActivityApply');

    }

    public function sign() {
        $this->excute_api('Api\Lib\ActivitySign');

    }

    public function _empty() {
        $this->excute_api('Api\Lib\ActivityList');
    }

}