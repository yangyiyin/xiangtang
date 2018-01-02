<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughSysTips extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $infos = [];
        $infos[]= '店长小助手:选择活动类别进行活动页面制作!';
        $infos[]= '店长小助手:在模板库可以选择自己喜欢的模板进行添加,然后制作!';
        $infos[]= '店长小助手:可以在个人中心点击头像设置个性化头像哦';
        $infos[]= '店长小助手:长按模板图片可以删除哦';
        $infos[]= '店长小助手:制作完的页面,可以到个人中心我制作的页面里面查看';
        $infos[]= '店长小助手:长按我的页面可以删除操作';

        $info = $infos[array_rand($infos,1)];
        return result_json(TRUE, '', $info);
    }

}