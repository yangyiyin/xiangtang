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
        $page_title = $this->post_data['page_title'];
        $page_stock = isset($this->post_data['page_stock']) ? $this->post_data['page_stock'] : 0;
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
        $data['type'] = $tmp_info['type'];
        $data['title'] = $page_title ? $page_title : $tmp_info['title'];
        $data['stock'] = $page_stock;
        $data['img'] = $tmp_info['img'];
        foreach ($tmp_data['page'] as $k => $_page) {
            if ($_page['type'] == 'text') {
                $tmp_data['page'][$k]['text'] = str_replace("\n","<br/>",$_page['text']);
            }
        }

        if (isset($tmp_data['time_limit_left']) && $tmp_data['time_limit_left']) {
            $tmp_data['time_limit_end'] = date('Y-m-d H:i:s', (time() + $tmp_data['time_limit_left']));
        }
        $data['tmp_data'] = json_encode($tmp_data);
        $PageService = \Common\Service\PageService::get_instance();
        $ret = $PageService->add_one($data);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //添加我的模板
        $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
        if (!$UserTemplateService->get_by_tids_uid([$tmp_id], $this->uid)) {
            $UserTemplateService->add_one(['tid'=>$tmp_id, 'uid'=>$this->uid]);
        }

        $url = 'https://www.'.C('BASE_WEB_HOST').'/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $ret->data;
        return result_json(TRUE, '制作成功',['url'=>$url,'page_id'=>$ret->data]);
    }


}