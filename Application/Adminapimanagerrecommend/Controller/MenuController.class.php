<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class MenuController extends BaseController {
    public function index() {
        $this->excute_api('Adminapi\Lib\MenuIndex');
    }

    public function setting() {
        $this->excute_api('Adminapi\Lib\MenuSetting');
    }

    public function add_edit() {
        $this->excute_api('Adminapi\Lib\MenuAddedit');
    }

    public function del() {
        $this->excute_api('Adminapi\Lib\MenuDel');
    }

}