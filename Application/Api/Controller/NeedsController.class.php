<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class NeedsController extends BaseController {
    public function types() {
        $this->excute_api('Api\Lib\NeedsTypes');
    }
    public function add() {
        $this->excute_api('Api\Lib\NeedsAdd');
    }

    public function index() {
        $this->excute_api('Api\Lib\NeedsIndex');
    }
}