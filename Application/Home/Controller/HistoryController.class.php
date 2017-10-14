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
        $NfClicks = M('NfClicks');

        $ones = $NfClicks->where(['type'=>['gt',0]])->select();
        $ones_map = result_to_complex_map($ones, 'type');
        foreach ($ones_map as $type => $value) {
            $ones_map[$type] = array_sum(result_to_array($value, 'count'));
        }
        $this->assign('map',$ones_map);
        //增加点击量

        $this->display();
    }

    public function download() {

        $type = I('type');

        if ($type == 1) {
            $path = __ROOT__ . '/Public/' . MODULE_NAME . '/images/1.wmv';
        } elseif ($type == 2) {
            $path = __ROOT__ . '/Public/' . MODULE_NAME . '/images/1.pptx';
        } elseif ($type == 3) {
            $path = __ROOT__ . '/Public/' . MODULE_NAME . '/images/2.wmv';
        } elseif ($type == 4) {
            $path = __ROOT__ . '/Public/' . MODULE_NAME . '/images/2.pptx';
        }

        //增加点击量
        $NfClicks = M('NfClicks');

        $one = $NfClicks->where(['ip'=>$_SERVER['REMOTE_ADDR'],'type'=>$type])->find();
        if ($one) {
            $NfClicks->where(['id'=>$one['id']])->setInc('count',1);
        } else {
            $add_click_data = [];
            $add_click_data['ip'] = $_SERVER['REMOTE_ADDR'];
            $add_click_data['create_time'] = date('Y-m-d H:i:s');
            $add_click_data['type'] = $type;
            $NfClicks->add($add_click_data);
        }

        echo '<script>javascript:location.href="'.$path.'"</script>';
    }



}