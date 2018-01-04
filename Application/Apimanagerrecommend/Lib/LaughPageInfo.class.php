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
            $tmp_data = $info['content'] = json_decode($info['tmp_data'],true);
            foreach ($info['content']['page'] as $k => $_page) {
                if ($_page['type'] == 'text') {
                    $info['content']['page'][$k]['text'] = str_replace("<br/>","\n",$_page['text']);
                }
            }

            $info['sign_list'] = $info['praise_list'] = $info['cutprice_list'] = $info['vote_list']=[];
            if ($tmp_data['sign_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;
            }

            if ($tmp_data['praise_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['praise_list'] = $sign_list;
            }

            if ($tmp_data['cutprice_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;
            }

            if ($tmp_data['vote_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;
            }

            $info['page_url'] = 'https://www.88plus.net/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $id;
        }
        return result_json(TRUE, '获取成功', $info);
    }

    private function convert($list) {
        if ($list) {
            $uids = result_to_array($list, 'uid');
            $UserService = \Common\Service\UserService::get_instance();
            $users = $UserService->get_by_ids($uids);
            $users_map = result_to_map($users);
            foreach ($list as $k => $value) {
                $list[$k]['user'] = isset($users_map[$value['uid']]) ? $users_map[$value['uid']] : [];
            }
        }
        return $list;
    }

}