<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class IndexController extends BaseController {
    public function index() {
        $this->excute_api('Api\Lib\IndexIndex');
    }

    public function sections() {
        $this->excute_api('Api\Lib\IndexSections');
    }
}