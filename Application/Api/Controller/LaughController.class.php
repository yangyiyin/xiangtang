<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class LaughController extends BaseController {
    public function publish() {
        $this->excute_api('Api\Lib\LaughPublish');
    }
    public function index() {
        $this->excute_api('Api\Lib\LaughIndex');
    }

}