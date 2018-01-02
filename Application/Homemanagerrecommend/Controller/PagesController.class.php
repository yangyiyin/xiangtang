<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Homemanagerrecommend\Controller;
use Think\Controller;
use Think\Exception;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class PagesController extends Controller {
    protected static $rate = 1;
    protected static $file_name = "pages/tmp/preview.tmp_data";
	//系统首页
    public function index(){

        $this->display();
    }

    public function show(){
        $width = I('w', 750);
        $height = I('h', 1334);//默认iphone6尺寸
        self::$rate = $width / 750;

        $is_preview = I('is_preview');
        //预览
        if ($is_preview) {
            $file_name = self::$file_name;
            $tmp_data = file_get_contents($file_name);
            if (!$tmp_data) {
                $this->error('信息异常!');
            }

            $tmp_data = $this->parse_rpx($tmp_data);
            $this->assign('tmp_data', json_decode($tmp_data, true));
            $this->assign('font_size', (self::$rate * 28).'px');

            $this->display();
            return;
        }
        $id = I('id');

        if (!$id) {
            $this->error('信息异常');
        }

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if (!$page_info) {
            $this->error('信息异常!');
        }
        $this->assign('page_info', $page_info);

        //替换rpx
        //如果存在缓存,则直接返回
        $file_name = "pages/tmp/".date('Ym-').$id.'-'.$width.".tmp_data";
        if ($tmp_data = file_get_contents($file_name)) {
            $page_info['tmp_data'] = $tmp_data;
        } else {
            $page_info['tmp_data'] = $this->parse_rpx($page_info['tmp_data']);
            $myfile = fopen($file_name, "w");
            fwrite($myfile,$page_info['tmp_data']);
            fclose($myfile);
        }

        $tmp_data = json_decode($page_info['tmp_data'], true);
        if ($tmp_data['sign_list']) {
            $PageSignService = \Common\Service\PageSignService::get_instance();
            $sign_list = $PageSignService->get_by_page_id($id);
            $sign_list = $this->convert($sign_list);
            $this->assign('sign_list', $sign_list);
        }

        if ($tmp_data['time_limit_end']) {
            $tmp_data['time_limit_left'] = strtotime($tmp_data['time_limit_end']) - time();
        }
        $this->assign('page_title', $page_info['title']);
        $this->assign('tmp_data', $tmp_data);
        $this->assign('font_size', (self::$rate * 28).'px');
        $this->assign('rate', self::$rate);
        $this->display();
    }

    //解析rpx
    private function parse_rpx($data) {

        $pattern = '/([0-9]+)rpx/';
        $data = preg_replace_callback($pattern, function($matches){
            return $matches[1] * self::$rate . 'px';
        }, $data);
        return $data;
    }

    public function sign() {
        $id = I('get.id');

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }
        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {
                $this->error('报名已结束!');
            }
        }

        //存入
        $PageSignService = \Common\Service\PageSignService::get_instance();
        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        if ($PageSignService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            $this->error('您已报名!');
        }

        $ret = $PageSignService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('报名成功!');
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