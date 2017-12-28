<?php
/**
 * Created by newModule.
 * Time: 2017-12-13 10:25:22
 */
namespace Admin\Controller;

class AntDeductibleCouponController extends AdminController {
    protected $DeductibleCouponService;
    protected function _initialize() {
        parent::_initialize();
        $this->DeductibleCouponService = \Common\Service\DeductibleCouponService::get_instance();
    }

    public function index() {

        $where = [];
        /**
        if (I('get.sex')) {
            $where['sex'] = ['EQ', I('get.sex')];
        }

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        */
        if (I('get.title')) {
            $where['title'] = ['LIKE', '%' . I('get.title') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->DeductibleCouponService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\DeductibleCouponService::$page_size);
        if($total>\Common\Service\DeductibleCouponService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->DeductibleCouponService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除抵扣优惠券');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->DeductibleCouponService->get_info_by_id($id);
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
            $data['least'] = intval(strval($data['least'] * 100));
            $data['deductible'] = intval(strval($data['deductible'] * 100));
            if ($id) {
                $ret = $this->DeductibleCouponService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改抵扣优惠券');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->DeductibleCouponService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加抵扣优惠券');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        $ids = result_to_array($data);
        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $count_map = [];
        foreach ($ids as $id) {
            $count_map[$id] = $UserDeductibleCouponService->get_count_by_id($id);
        }
        foreach ($data as $key => $item) {
            $data[$key]['count'] = isset($count_map[$item['id']]) ? $count_map[$item['id']] : 0;
        }
    }

    public function gain() {
        $id = I('post.cid');
        $num = I('post.num');
        $info = $this->DeductibleCouponService->get_info_by_id($id);
        if (!$info) {
            $this->error('没有对应的优惠券信息');
        }

        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $ret = $UserDeductibleCouponService->gain($id, $info['title'],$info['least'],$info['deductible'],$num,$info['img']);
        if ($ret->success) {
            action_user_log('生成抵扣优惠券'.$num);
            $this->success('生成成功！');
        } else {
            $this->error($ret->message);
        }
    }

}