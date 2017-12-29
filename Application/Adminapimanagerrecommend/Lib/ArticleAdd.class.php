<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;

class ArticleAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {
    }

    public function excute() {

        $data = $this->post_data['data'];
        $service = Service\ArticleService::get_instance();
        if ($this->post_data['action'] == 'add') {
            if (strpos($data['content'], 'kkkkkk') !== false) {
                $contents = explode('kkkkkk', $data['content']);
                foreach ($contents as $content) {
                    $data['content'] = $content;
                    $data['from'] = \Common\Model\NfArticleModel::FROM_ADMIN;
                    $data['status'] = \Common\Model\NfArticleModel::STATUS_OK;
                    $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
                    $data['publish_time'] = date('Y-m-d H:i:s');
                    $ret = $service->add_one($data);
                    if (!$ret->success) {
                        return result_json(false, $ret->message);
                    }
                }
            } else {
                $data['from'] = \Common\Model\NfArticleModel::FROM_ADMIN;
                $data['status'] = \Common\Model\NfArticleModel::STATUS_OK;
                $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
                $data['publish_time'] = date('Y-m-d H:i:s');
                $ret = $service->add_one($data);
                if (!$ret->success) {
                    return result_json(false, $ret->message);
                }
            }

        } elseif ($this->post_data['action'] == 'edit') {
            unset($data['id']);
            $data['publish_time'] = date('Y-m-d H:i:s');
            $ret = $service->update_by_id($this->post_data['data']['id'], $data);
            if (!$ret->success) {
                return result_json(false, $ret->message);
            }
        }


        return result_json(true, '操作成功');
    }



}