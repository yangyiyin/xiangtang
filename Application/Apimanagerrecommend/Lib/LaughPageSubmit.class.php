<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPageSubmit extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $tmp_data = $this->post_data['tmp_data'];
        $tmp_id = $this->post_data['tmp_id'];
        if (!$tmp_data || !$tmp_id) {
            return result_json(false, '页面内容异常!');
        }

        //生成page
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $tmp_info = $TemplateService->get_info_by_id($tmp_id);
        if (!$tmp_info) {
            return result_json(false, '页面内容异常!');
        }

        $data = [];
        $data['uid'] = $this->uid;
        $data['title'] = $tmp_info['title'];
        $data['img'] = $tmp_info['img'];


        return result_json(TRUE, '发布成功','http://baidu.com');
    }

}