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

    public function my_tmplist() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughMyTmplist');
    }

    public function alltmplist() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughAlltmplist');
    }

    public function tmp_info() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughTmpInfo');
    }
    public function page_info() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPageInfo');
    }
    public function page_detail_info() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPageDetailInfo');
    }

    public function add_tmp() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughAddTmp');
    }

    public function del_tmp() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughDelTmp');
    }
    public function mypages() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughMypages');
    }
    public function del_page() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughDelPage');
    }

    public function user_pages() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughUserPages');
    }
    public function del_user_page() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughDelUserPage');
    }


    public function contact() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughContact');
    }

    public function suggest_list() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughSuggestList');
    }
    public function add_suggest() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughAddSuggest');
    }

    public function sign() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughSign');
    }
    public function cutprice_sign() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughCutpriceSign');
    }
    public function cutprice_cut() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughCutpriceCut');
    }
    public function praise_sign() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPraiseSign');
    }
    public function praise_praise() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPraisePraise');
    }
    public function fightgroup_sign() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughFightgroupSign');
    }
    public function fightgroup_join() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughFightgroupJoin');
    }
    public function quick_buy() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughQuickBuy');
    }
    public function vote() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughVote');
    }
    public function remark_sign() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughRemarkSign');
    }

    public function verify_code() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughVerifyCode');
    }
    public function send_code() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughSendCode');
    }
    public function pick_verify() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughPickVerify');
    }

    public function statistics_point() {
        $this->excute_api('Apimanagerrecommend\Lib\LaughStatisticsPoint');
    }
}