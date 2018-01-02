<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPageInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $id = I('id');

        $PageService = \Common\Service\PageService::get_instance();
        $info = $PageService->get_info_by_id($id);

        if ($info) {
           // $info['tmp_data'] = str_replace('<br\/>',"\n",$info['tmp_data']);
           // $info['tmp_data'] = str_replace('<br>',"\n",$info['tmp_data']);
            $info['content'] = json_decode($info['tmp_data'],true);
            foreach ($info['content']['page'] as $k => $_page) {
                if ($_page['type'] == 'text') {
                    $info['content']['page'][$k]['text'] = str_replace("<br/>","\n",$_page['text']);
                }
            }
            $info['page_url'] = 'https://www.88plus.net/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $id;
        }
        return result_json(TRUE, '获取成功', $info);
    }


}