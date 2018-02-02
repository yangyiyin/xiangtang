<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughArticleInfo extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $id = I('get.id');
        $ArticleService = \Common\Service\ArticleService::get_instance();
        $info = $ArticleService->get_info_by_id($id);
        if ($info && $info['imgs']) {
            $info['imgs'] = explode(',',$info['imgs']);
        }
        return result_json(TRUE, '', $info);
    }

}