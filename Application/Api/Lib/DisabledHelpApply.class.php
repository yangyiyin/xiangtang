<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledHelpApply extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $DisabledHelpService;
    public function init() {
        $this->DisabledHelpService = Service\DisabledHelpService::get_instance();
    }

    public function excute() {
        $data = [];
        $data['name'] = $this->post_data['name'];
        $data['id_no'] = $this->post_data['id_no'];
        $data['address'] = $this->post_data['address'];
        $data['tel'] = $this->post_data['tel'];
        $data['directly_name'] = $this->post_data['directly_name'];
        $data['directly_tel'] = $this->post_data['directly_tel'];
        $data['help_cat'] = $this->post_data['help_cat'];
        $data['uid'] = $this->uid;
        if (!$data['name'] || !$data['id_no'] || !$data['address'] || !$data['tel']) {
            return result_json(false, '参数不完整,请检查姓名,住址,电话,和残疾人证件号已填写正确!');
        }
        $ret = $this->DisabledHelpService->add_one($data);
        if (!$ret->success) {
            return result_json(false, '提交失败!', '');
        }
        $id = $ret->data;
        return result_json(TRUE, '提交成功!', $id);
        
    }

}