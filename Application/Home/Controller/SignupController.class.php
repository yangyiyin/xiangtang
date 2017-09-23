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
class SignupController extends Controller {

	//系统首页
    public function index(){

        if (IS_POST) {

        }

                 
        $this->display();
    }

}