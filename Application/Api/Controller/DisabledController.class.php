<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class DisabledController extends BaseController {
    public function help_apply() {
        $this->excute_api('Api\Lib\DisabledHelpApply');

    }

    public function help_apply_list() {
        $this->excute_api('Api\Lib\DisabledHelpApplyList');

    }


}