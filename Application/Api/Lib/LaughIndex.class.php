<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {

        $p = I('p',1);
        $ArticleService = \Common\Service\ArticleService::get_instance();

        list($list, $count) = $ArticleService->get_by_where([],'id desc',$p);


        return result_json(TRUE, '', $list);
    }

}