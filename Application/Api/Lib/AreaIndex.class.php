<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class AreaIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $AreaService = Service\AreaService::get_instance();
        $data = $AreaService->get_tree();
        return result_json(TRUE, '', $data);
    }

}