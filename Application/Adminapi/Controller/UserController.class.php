<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class UserController extends BaseController {
    public function index() {
        $this->excute_api('Adminapi\Lib\UserIndex');
    }

    public function add() {
        $this->excute_api('Adminapi\Lib\UserAdd');
    }

    public function change_passwd() {
        $this->excute_api('Adminapi\Lib\UserChangePasswd');
    }


    public function change_status() {
        $this->excute_api('Adminapi\Lib\UserChangestatus');
    }

    public function action_log() {
        $this->excute_api('Adminapi\Lib\UserActionLog');
    }

}