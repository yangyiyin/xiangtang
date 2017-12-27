<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class PagesController extends Controller {


	//系统首页
    public function index(){
        $id = I('id');
        if (!$id) {
            $this->error('信息异常');
        }

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);

        $this->assign('page_info', $page_info);
        $this->display();
    }



}