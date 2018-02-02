<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class ArticleController extends BaseController {
    public function index() {
        $this->excute_api('Adminapi\Lib\ArticleIndex');
    }

    public function add() {
        $this->excute_api('Adminapi\Lib\ArticleAdd');
    }

    public function change_status() {
        $this->excute_api('Adminapi\Lib\ArticleChangeStatus');
    }

    public function spider_laugh() {
        $this->excute_api('Adminapi\Lib\SpiderLaugh');
    }




}