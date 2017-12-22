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

    public function my_publish_index() {
        $this->excute_api('Api\Lib\LaughMyPublishIndex');
    }

    public function my_collect_index() {
        $this->excute_api('Api\Lib\LaughMyCollectIndex');
    }


    public function login() {
        $this->excute_api('Api\Lib\LaughLogin');
    }

    public function like() {
        $this->excute_api('Api\Lib\LaughLike');
    }

    public function collect() {
        $this->excute_api('Api\Lib\LaughCollect');
    }

    public function share_success() {
        $this->excute_api('Api\Lib\LaughShareSuccess');
    }

    public function welcome_info() {
        $this->excute_api('Api\Lib\LaughWelcomeInfo');
    }
    public function sys_tips() {
        $this->excute_api('Api\Lib\LaughSysTips');
    }
}