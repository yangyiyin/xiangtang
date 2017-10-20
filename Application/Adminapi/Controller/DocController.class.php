<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class DocController extends BaseController {
    public function index() {
        $this->excute_api('Adminapi\Lib\DocIndex');
    }

    public function add() {
        $this->excute_api('Adminapi\Lib\DocAdd');
    }


    public function del() {
        $this->excute_api('Adminapi\Lib\DocDel');
    }


}