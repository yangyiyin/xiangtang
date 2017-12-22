<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class NeedsAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $NeedsService;
    public function init() {
        $this->NeedsService = Service\NeedsService::get_instance();
    }

    public function excute() {

        $type = $this->post_data['type'];
        $title = $this->post_data['title'];
        $content = $this->post_data['content'];
        $extra = $this->post_data['extra'];

        if (!$type || !$title || !$content) {
            return result_json(false, '请填写完整的信息');
        }

        $data = [];
        $data['type'] = $type;
        $data['title'] = $title;
        $data['content'] = $content;
        if ($extra) {
            $data['extra'] = $extra;
        }
        $data['status'] = \Common\Model\NfNeedsModel::STATUS_READY;
        $data['uid'] = $this->uid;
        $ret = $this->NeedsService->add_one($data);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        return result_json(TRUE, '发布成功');
    }

}