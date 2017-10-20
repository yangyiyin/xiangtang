<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;

class DocAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {
    }

    public function excute() {

        $data = $this->post_data['data'];
        $service = Service\DocsService::get_instance();
        if ($this->post_data['action'] == 'add') {

            $map = \Common\Model\NfDocsModel::$type_map;
            $data['type'] = isset($map[$data['type']]) ? $map[$data['type']] : 0;
            $ret = $service->add_one($data);
            if (!$ret->success) {
                return result_json(false, $ret->message);
            }
        } elseif ($this->post_data['action'] == 'edit') {
            $map = \Common\Model\NfDocsModel::$type_map;
            $data['type'] = isset($map[$data['type']]) ? $map[$data['type']] : 0;
            unset($data['id']);
            $ret = $service->update_by_id($this->post_data['data']['id'], $data);
            if (!$ret->success) {
                return result_json(false, $ret->message);
            }
        }



        return result_json(true, '操作成功');
    }



}