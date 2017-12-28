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

	//系统首页
    public function index(){


        $this->display();
    }

    public function show(){
        $id = I('id');

        $width = I('w', 750);
        $height = I('h', 1334);//默认iphone6尺寸
        self::$rate = $width / 750;
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


        $this->assign('tmp_data', json_decode($page_info['tmp_data'], true));
        $this->assign('font_size', (self::$rate * 28).'px');

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




}