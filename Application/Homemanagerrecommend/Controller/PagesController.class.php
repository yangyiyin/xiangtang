<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Homemanagerrecommend\Controller;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class PagesController extends Controller {


	//系统首页
    public function index(){

        $this->display();
    }

    public function show(){
        $id = I('id');
        $width = I('w', 750);
        $height = I('h', 1334);//默认iphone6尺寸
        if (!$id) {
            $this->error('信息异常');
        }

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if (!$page_info) {
            $this->error('信息异常!');
        }
        $this->assign('page_info', $page_info);
        $this->assign('tmp_data', json_decode($page_info['tmp_data'], true));
        $this->assign('width', $width);
        $this->assign('height', $height);
        $this->display();
    }

}