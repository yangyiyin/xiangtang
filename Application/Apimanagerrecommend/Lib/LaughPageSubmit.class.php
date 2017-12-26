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
        if (!$tmp_data) {
            return result_json(false, '页面内容异常!');
        }


        return result_json(TRUE, '发布成功','http://baidu.com');
    }

}