<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsOutCash extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $OutCashService;
    public function init() {
        $this->OutCashService = Service\OutCashService::get_instance();
    }

    public function excute() {

        $name = $this->post_data['name'];
        $bank_code = $this->post_data['bank_code'];
        $sum = $this->post_data['sum'];
        $bank_name = $this->post_data['bank_name'];

        if (!$name || !$bank_code || !$sum || !$bank_name) {
            return result_json(false, '参数不完整');
        }

        $data = [];
        $data['name'] = $name;
        $data['bank_code'] = $bank_code;
        $data['sum'] = $sum;
        $data['bank_name'] = $bank_name;

        $data['status'] = \Common\Model\NfOutCashModel::STATUS_READY;
        $data['uid'] = $this->uid;
        $ret = $this->OutCashService->add_one($data);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        return result_json(TRUE, '发布成功');
    }

}