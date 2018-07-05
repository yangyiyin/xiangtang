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
        $this->assign('rate', self::$rate);
        $is_preview = I('is_preview');
        //预览
        if ($is_preview) {
            $file_name = self::$file_name;
            $tmp_data = file_get_contents($file_name);
            if (!$tmp_data) {
                $this->error('信息异常!');
            }

            $tmp_data = $this->parse_rpx($tmp_data);
            $tmp_data_arr = json_decode($tmp_data, true);
            foreach ($tmp_data_arr['page'] as &$item) {
                if ($item['type'] == 'vote') {
                    $item['vote_num_arr'] = ['src'=>'http://paz3jxo1v.bkt.clouddn.com/logo144.png', 'desc'=>'描述内容描述内容描述内容描述内容描述内容描述内容描述内容描述内容'];
                }
            }
            $this->assign('tmp_data', $tmp_data_arr);
            //{src:'http://paz3jxo1v.bkt.clouddn.com/logo144.png',desc:'描述内容描述内容描述内容描述内容描述内容描述内容描述内容描述内容'};

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
        if ($tmp_data['cutprice_list']) {
            $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
            $list = $PageCutpriceService->get_by_page_id($id,1);
            $list = $this->convert($list);
            $this->assign('cutprice_list', $list);

            $extra_uid = I('extra_uid');
            $all_log = $PageCutpriceService->get_by_uid_page_id_all(1,$id);
            $is_sign_cutprice = $is_help_cutprice = 0;
            if ($all_log) {
                foreach ($all_log as $log) {
                    if ($log['pid'] == 0) {
                        $is_sign_cutprice = 1;
                    } else {
                        if ($extra_uid == $log['pid']) {
                            $is_help_cutprice = 1;
                        }

                    }
                }
            }

            $this->assign('is_sign_cutprice', $is_sign_cutprice);
            $this->assign('is_help_cutprice', $is_help_cutprice);
            $this->assign('extra_uid', $extra_uid);

            //var_dump($list);die();
        }
        if ($tmp_data['praise_list']) {
            $PageSignService = \Common\Service\PagePraiseService::get_instance();
            $list = $PageSignService->get_by_page_id($id,1);
            $list = $this->convert($list);
            $this->assign('praise_list', $list);

            $extra_uid = I('extra_uid');
            $all_log = $PageSignService->get_by_uid_page_id_all(1,$id);
            $is_sign_praise = $is_help_praise = 0;
            if ($all_log) {
                foreach ($all_log as $log) {
                    if ($log['pid'] == 0) {
                        $is_sign_praise = 1;
                    } else {
                        if ($extra_uid == $log['pid']) {
                            $is_help_praise = 1;
                        }

                    }
                }
            }

            $this->assign('is_sign_praise', $is_sign_praise);
            $this->assign('is_help_praise', $is_help_praise);
            $this->assign('extra_uid', $extra_uid);
        }
        if ($tmp_data['vote_list']) {
            $PageSortService = \Common\Service\PageSortService::get_instance();
            $list = $PageSortService->get_by_page_id($id);
            $key = 0;
            foreach ($tmp_data['page'] as $_key => $_page) {
                if ($_page['type'] == 'vote') {
                    $key = $_key;
                    break;
                }
            }
            $tmp_data['page'][$key]['vote_num_arr'] = $this->convert_vote_list($tmp_data['page'][$key]['vote_num_arr'], $list);
        }


        if ($tmp_data['time_limit_end']) {
            $tmp_data['time_limit_left'] = strtotime($tmp_data['time_limit_end']) - time();
        }
        $this->assign('page_title', $page_info['title']);
        $this->assign('tmp_data', $tmp_data);
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

    public function cutprice_sign() {
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
        $price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
            }
        }
        if (!$price) {
            $this->error('活动信息异常!');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();

        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            $this->error('您已报名!');
        }

        $data['price'] = $price * 100;
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('报名成功!');
    }


    public function cutprice_cut() {
        $id = I('get.id');
        $extra_uid = I('get.extra_uid');
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
        $price = $max_minus_price = $average_price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
                $max_minus_price = $_page['cutprice_max_minus_price'];
                $average_price = $_page['cutprice_average_price'];
            }
        }
        if (!$price || !$max_minus_price || !$average_price) {
            $this->error('活动信息异常!');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {
            $this->error('您已砍价!');

        }
        $cut_info = $PageCutpriceService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$cut_info) {
            $this->error('砍价信息异常!');
        }

        $left_price = $max_minus_price * 100 - ($price * 100 - $cut_info['price']);
        if ($left_price <= 0) {
            $this->error('该砍价已经到上限!');
        }
        $can_cut_price = $left_price > ($average_price * 1.4 * 100) ? ($average_price * 1.4 * 100) : $left_price;

        $data['cutprice'] = mt_rand($can_cut_price * 0.4, $can_cut_price);
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        $data_up = [];
        $data_up['price'] = $cut_info['price'] - $data['cutprice'];
        $PageCutpriceService->update_by_id($cut_info['id'], $data_up);
        $this->success('成功砍价'.format_price($data['cutprice']).'元!');
    }


    public function praise_sign() {
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
        $PagePraiseService = \Common\Service\PagePraiseService::get_instance();
        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        if ($PagePraiseService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            $this->error('您已报名!');
        }
        $ret = $PagePraiseService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('报名成功!');
    }

    public function praise_praise() {
        $id = I('get.id');
        $extra_uid = I('get.extra_uid');
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
        $PagePraiseService = \Common\Service\PagePraiseService::get_instance();
        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        $data['sum'] = 0;
        if ($PagePraiseService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {
            $this->error('您已点赞!');
        }
        $praise_info = $PagePraiseService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$praise_info) {
            $this->error('点赞信息异常!');
        }


        $ret = $PagePraiseService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        $data_up = [];
        $data_up['sum'] = $praise_info['sum'] + 1;
        $PagePraiseService->update_by_id($praise_info['id'], $data_up);
        $this->success('点赞成功!');
    }

    public function vote() {
        $id = I('get.id');
        $vote_id = I('get.vote_id');
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
        $PageSortUserService = \Common\Service\PageSortUserService::get_instance();
        $data = [];
        $data['uid'] = 1;
        $data['page_id'] = $id;
        $data['sort_id'] = $vote_id;
        if ($PageSortUserService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['sort_id'])) {
            $this->error('您已投票!');
        }
        $data['sort_id'] = $vote_id;
        $ret = $PageSortUserService->add_one($data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $PageSortService = \Common\Service\PageSortService::get_instance();
        $sort = $PageSortService->get_by_sort_id_page_id($vote_id, $id);
        if ($sort) {
            $data = [];
            $data['sum'] = $sort['sum'] + 1;
            $PageSortService->update_by_id($sort['id'], $data);
        } else {
            $data = [];
            $data['sort_id'] = $vote_id;
            $data['page_id'] = $id;
            $data['sum'] = 1;
            $PageSortService->add_one($data);
        }

        $this->success('投票成功!');
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

    private function convert_vote_list($vote_arr, $list) {
        if ($vote_arr) {
            $list_map = result_to_map($list, 'sort_id');
            foreach ($vote_arr as $key => $value) {
                $vote_arr[$key]['sign'] = isset($list_map[$key]) ? $list_map[$key]['sum'] : 0;
            }
        }
        return $vote_arr;
    }
}