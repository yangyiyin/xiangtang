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
class HistoryController extends Controller {


	//系统首页
    public function index(){

        //增加点击量

        $this->display();
    }



}