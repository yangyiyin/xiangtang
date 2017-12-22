<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughWelcomeInfo extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $infos = [];
        $infos[]= '你好,段子王!';
        $infos[]= '欢迎,笑话大王!';
        $infos[]= '朋友,开心每一天~';
        $infos[]= '这里,快乐无处不在~';
        $infos[]= '我需要你段子~';
        $infos[]= '我的笑话肯定好笑!';
        $infos[]= '纯笑笑就是个傻比!';
        $infos[]= '为什么每次都是这条欢迎语!';
        $infos[]= '难过的时候看看笑话~';
        $infos[]= '每日笑话10条,不多不少~';
        $infos[]= '段子不要太污哦~';
        $info = $infos[array_rand($infos,1)];
        return result_json(TRUE, '', $info);
    }

}