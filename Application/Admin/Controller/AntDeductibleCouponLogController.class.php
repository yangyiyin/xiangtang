<?php
/**
 * Created by newModule.
 * Time: 2018-01-09 15:38:05
 */
namespace Admin\Controller;

class AntDeductibleCouponLogController extends AdminController {
    protected $DeductibleCouponLogService;
    protected function _initialize() {
        parent::_initialize();
        $this->DeductibleCouponLogService = \Common\Service\DeductibleCouponLogService::get_instance();
    }

    public function index() {

        $where = [];

        if (I('get.create_begin')) {
            $where['disable_time'][] = ['egt', I('get.create_begin')];
        }
        if (I('get.create_end')) {
            $where['disable_time'][] = ['elt', I('get.create_end')];
        }

        $page = I('get.p', 1);
        if (I('export')) {
            list($data, $count) = $this->DeductibleCouponLogService->get_by_where_all($where, 'id desc');

        } else {
            list($data, $count) = $this->DeductibleCouponLogService->get_by_where($where, 'id desc', $page);

        }
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\DeductibleCouponLogService::$page_size);
        if($total>\Common\Service\DeductibleCouponLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        if (I('export')) {
            $excel_data = [];
            $excel_data[] = ["优惠券名称","发放数量","优惠券领取者","领取时间","使用时间"];
            foreach ($data as $value) {
                $temp = [];
                $temp[] = $value['title'];
                $temp[] = $value['num'];
                $temp[] = $value['user_name'];
                $temp[] = $value['enable_time'];
                $temp[] = $value['disable_time'];
                $excel_data[] = $temp;
            }
            exportexcel($excel_data,'', '优惠券统计');
            exit();
        }

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->DeductibleCouponLogService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除优惠券统计');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->DeductibleCouponLogService->get_info_by_id($id);
            if ($info) {

                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            if ($id) {
                $ret = $this->DeductibleCouponLogService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改优惠券统计');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->DeductibleCouponLogService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加优惠券统计');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {
            $coupon_ids = result_to_array($data, 'coupon_id');
            $DeductibleCouponService = \Common\Service\DeductibleCouponService::get_instance();
            $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
            $usercoupons = $UserDeductibleCouponService->get_by_ids($coupon_ids);
            $usercoupons_map = result_to_map($usercoupons);
            $cids = result_to_array($usercoupons, 'cid');
            $coupons = $DeductibleCouponService->get_by_ids($cids);
            $coupons_map = result_to_map($coupons);
           // var_dump($coupon_ids);die();
            foreach ($data as $key => $_item) {
                if (isset($coupons_map[$usercoupons_map[$_item['coupon_id']]['cid']]['num'])) {
                    $data[$key]['num'] = $coupons_map[$usercoupons_map[$_item['coupon_id']]['cid']]['num'];
                } else {
                    $data[$key]['num'] = '未知';
                }
            }
        }

    }
}