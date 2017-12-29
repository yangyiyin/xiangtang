<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class AuthManagerController extends BaseController {
    public function index() {
        $this->excute_api('Adminapi\Lib\AuthManagerIndex');
    }

    public function add() {
        $this->excute_api('Adminapi\Lib\AuthManagerAdd');
    }

    public function change_status() {
        $this->excute_api('Adminapi\Lib\AuthManagerChangestatus');
    }

    public function add_edit() {
        $this->excute_api('Adminapi\Lib\AuthManagerAddedit');
    }

    public function group_user() {
        $this->excute_api('Adminapi\Lib\AuthManagerGroupUser');
    }

    public function group_add_user() {
        $this->excute_api('Adminapi\Lib\AuthManagerGroupAddUser');
    }

    public function group_remove_user() {
        $this->excute_api('Adminapi\Lib\AuthManagerGroupRemoveUser');
    }

    public function access() {
        $this->excute_api('Adminapi\Lib\AuthManagerAccess');
    }
}