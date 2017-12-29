<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapimanagerrecommend\Lib;

use Common\Model;
use Common\Service;
use Think\Upload;
class ManagerrecommendTmpAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $tmp_data = $this->post_data['tmp_data'];
        $title = $this->post_data['title'];
        $img = $this->post_data['img'];
        $type = $this->post_data['type'];
        if (!$tmp_data) {
            result_json(false, '无数据!');
        }
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $data = [];
        $data['title'] = $title;
        $data['img'] = $img;
        $data['type'] = $type;
        //$tmp_data = str_replace("\n",'<br/>',$tmp_data);
        $data['content'] = json_encode($tmp_data);
        $TemplateService->add_one($data);
        result_json(TRUE, '保存成功!');
    }


}