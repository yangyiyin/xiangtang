<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughSysTips extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $infos = [];
        $infos[]= '纯小助手:点击下方菜单栏中间的+号可以投稿哦!';
        $infos[]= '纯小助手:投稿(中稿)、被赞、转发的用户可获得笑笑币奖励!';
        $infos[]= '纯小助手:在我的里面,可以设置个性化笔名和头像哦~';
        $infos[]= '纯小助手:笑笑币奖励规则在我的页面可以查看~';
        $infos[]= '纯小助手:投稿审核一般1-3天左右,可以在我的投稿中查看审核状态~';
        $infos[]= '纯小助手:中稿,会获得额外笑笑币奖励!';

        $info = $infos[array_rand($infos,1)];
        return result_json(TRUE, '', $info);
    }

}