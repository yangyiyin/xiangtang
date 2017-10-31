<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class DisabledController extends BaseController {
    public function _empty() {
        $this->excute_api('Api\Lib\DisabledList');
    }
    public function info() {
        $this->excute_api('Api\Lib\DisabledInfo');
    }

    public function help_apply() {
        $this->excute_api('Api\Lib\DisabledHelpApply');
    }

    public function help_apply_list() {
        $this->excute_api('Api\Lib\DisabledHelpApplyList');

    }

    public function work_info_apply() {
        $this->excute_api('Api\Lib\DisabledWorkInfoApply');
    }

    public function cat_list() {
        $this->excute_api('Api\Lib\DisabledHelpCatList');
    }

}