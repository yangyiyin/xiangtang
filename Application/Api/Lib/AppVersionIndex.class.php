<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class AppVersionIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $AdService;
    public function init() {
        $this->AppVersionService = Service\AppVersionService::get_instance();
    }

    public function excute() {

        $data = $this->AppVersionService->get_current();
        return result_json(TRUE, '', $data);
    }

}