<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughStatisticsPoint extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $PageStatisticsService = Service\PageStatisticsService::get_instance();
        $PageStatisticsService->add_one(['page_id'=>I('page_id', 0), 'type'=>I('type', 0), 'uid'=> $this->uid, 'ip'=>get_ip()]);
        return result_json(TRUE, '');
    }

}