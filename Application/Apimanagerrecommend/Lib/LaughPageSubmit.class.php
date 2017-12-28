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
        foreach ($tmp_data['page'] as $k => $_page) {
            if ($_page['type'] == 'text') {
                $tmp_data['page'][$k]['text'] = str_replace("\n","<br/>",$_page['text']);
            }
        }
        $data['tmp_data'] = json_encode($tmp_data);
        $PageService = \Common\Service\PageService::get_instance();
        $ret = $PageService->add_one($data);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        $url = 'https://www.88plus.net/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $ret->data;
        return result_json(TRUE, '制作成功',$url);
    }


}