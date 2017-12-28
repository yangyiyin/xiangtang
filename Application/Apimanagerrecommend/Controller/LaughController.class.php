<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class LaughController extends BaseController {
    public function publish() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPublish');
    }
    public function index() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughIndex');
    }

    public function my_publish_index() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughMyPublishIndex');
    }

    public function my_collect_index() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughMyCollectIndex');
    }


    public function login() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughLogin');
    }

    public function like() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughLike');
    }

    public function collect() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughCollect');
    }

    public function share_success() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughShareSuccess');
    }

    public function welcome_info() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughWelcomeInfo');
    }
    public function sys_tips() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughSysTips');
    }

    public function img_upload() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughImgUpload');
    }

    public function page_submit() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPageSubmit');
    }

    public function make_qrcode() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughMakeQrcode');
    }

}