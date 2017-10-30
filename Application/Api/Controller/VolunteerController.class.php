<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class VolunteerController extends BaseController {
    public function apply() {
        $this->excute_api('Api\Lib\VolunteerApply');

    }

    public function apply_info() {
        $this->excute_api('Api\Lib\VolunteerApplyInfo');

    }

}