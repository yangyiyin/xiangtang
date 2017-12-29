<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapimanagerrecommend\Controller;
class ManagerrecommendController extends BaseController {
    public function cache_data() {
        $this->excute_api('Adminapimanagerrecommend\Lib\ManagerrecommendCacheData');
    }

    public function tmp_add() {
        $this->excute_api('Adminapimanagerrecommend\Lib\ManagerrecommendTmpAdd');
    }



}