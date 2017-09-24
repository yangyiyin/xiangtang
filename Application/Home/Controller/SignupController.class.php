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

    public static $type = 101;
    public static $free_count = 1000;
	//系统首页
    public function index(){

        //增加点击量
        $NfClicks = M('NfClicks');

        $one = $NfClicks->where(['ip'=>$_SERVER['REMOTE_ADDR']])->find();
        if ($one) {
            $NfClicks->where(['ip'=>$_SERVER['REMOTE_ADDR']])->setInc('count',1);
        } else {
            $add_click_data = [];
            $add_click_data['ip'] = $_SERVER['REMOTE_ADDR'];
            $add_click_data['create_time'] = date('Y-m-d H:i:s');
            $NfClicks->add($add_click_data);
        }

        if (IS_POST) {
            $qq = I('qq');
            $yy = I('yy');
            if (!$qq) {
                $this->error('请输入qq号');
            }
            $data = I('post.');
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            if (!$data['ip']) {
                $this->error('您当前的网络环境不允许报名,请联系官方');
            }
            $data['type'] = self::$type;
            $NfOnes = M('NfOnes');

            $count = $NfOnes->where(['type'=>self::$type])->count();
            if ($count > $free_count) {
                $data['status'] = 1;
            }
            $data['status'] = 2;
            $data['create_time'] = date('Y-m-d H:i:s');

            $one = $NfOnes->where(['ip'=>$data['ip']])->find();

            if ($one) {
                $this->error('您已报名,不能再次报名~');
            }

            $one = $NfOnes->where(['qq'=>$data['qq']])->find();

            if ($one) {
                $this->error('该qq已报名,不能再次报名~');
            }

            if ($NfOnes->add($data)) {
                $this->success('报名成功');
            } else {
                $this->error('网络繁忙,请联系官方');
            }
        }

        $NfOnes = M('NfOnes');
        $list = $NfOnes->where(['type'=>self::$type])->order('status desc, create_time desc')->select();

        foreach ($list as &$li) {
            $li['status_desc'] = ($li['status'] == 1) ? '等待付款确认' : '报名成功';
            $li['qq'] = qq_num_security($li['qq']);
        }

        $this->assign('list', $list);

        $one = $NfOnes->where(['ip'=>$_SERVER['REMOTE_ADDR']])->find();
        $has_signup = $one ? 1 : 0;
        $this->assign('has_signup', $has_signup);
        $this->display();
    }

    public function yy_info(){
        $this->display();
    }

}