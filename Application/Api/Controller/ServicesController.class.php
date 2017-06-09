<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class ServicesController extends BaseController {
    public function _empty() {
        $this->excute_api('Api\Lib\ServicesList');
    }
}