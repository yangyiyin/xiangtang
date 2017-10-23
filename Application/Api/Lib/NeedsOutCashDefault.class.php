<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsOutCashDefault extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $OutCashService;
    public function init() {
        $this->OutCashService = Service\OutCashService::get_instance();
    }

    public function excute() {

        $ret = $this->OutCashService->get_last_info($this->uid);
        $data = [];
        if ($ret) {
            $data['name'] = $ret['name'];
            $data['bank_name'] = $ret['bank_name'];
            $data['bank_code'] = $ret['bank_code'];
        }

        return result_json(TRUE, '', $data);
    }

}