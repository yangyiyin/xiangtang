<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class VolunteerApply extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $VolunteerService;
    public function init() {
        $this->VolunteerService = Service\VolunteerService::get_instance();
    }

    public function excute() {
        $data = [];
        $data['name'] = $this->post_data['name'];
        $data['id_no'] = $this->post_data['id_no'];
        $data['tel'] = $this->post_data['tel'];
        $data['address'] = $this->post_data['address'];
        $data['free_time'] = $this->post_data['free_time'];
        $data['status'] = \Common\Model\NfVolunteerModel::STATUS_SUBMIT;

        $ConfService = \Common\Service\ConfService::get_instance();
        $conf = $ConfService->get_by_key_name('volunteer_pay_sum');
        $data['pay_sum'] = isset($conf['content']) ? $conf['content'] : 10000;
        $info = $this->VolunteerService->get_info_by_uid($this->uid);

        if ($info) {
            $id = $info['id'];
            $ret = $this->VolunteerService->update_by_id($info['id'], $data);
            if (!$ret->success) {
                return result_json(false, '提交失败,请修改后再提交!', $id);
            }
        } else {
            $data['uid'] = $this->uid;
            $ret = $this->VolunteerService->add_one($data);
            if (!$ret->success) {
                return result_json(false, '提交失败!', '');
            }
            $id = $ret->data;
        }

        return result_json(TRUE, '提交成功!', $id);
        
    }

}