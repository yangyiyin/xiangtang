<?php
/**
 * Created by newModule.
 * Time: 2018-01-09 15:40:54
 */
namespace Admin\Controller;

class AntItemTimelimitLogController extends AdminController {
    protected $ItemTimelimitLogService;
    protected function _initialize() {
        parent::_initialize();
        $this->ItemTimelimitLogService = \Common\Service\ItemTimelimitLogService::get_instance();
    }

    public function index() {

        $where = [];
        $start_time = I('get.create_begin', '1999-12-31 00:00:00');
        $end_time = I('get.create_end', '2038-12-31 00:00:00');

        $where['start_time'] = ['elt', $end_time];
        $where['end_time'] = ['egt', $start_time];
        $page = I('get.p', 1);
        if (I('export')) {
            list($data, $count) = $this->ItemTimelimitLogService->get_by_where($where, 'id desc', $page);
        } else {
            list($data, $count) = $this->ItemTimelimitLogService->get_by_where_all($where, 'id desc');
        }

        $this->convert_data($data);
        $data = result_to_complex_map($data, 'item_id');
        $new_data = [];
        foreach ($data as $group) {
            foreach ($group as $item) {
                $new_data[] = $item;
            }
        }
        $data = $new_data;
        $PageInstance = new \Think\Page($count, \Common\Service\ItemTimelimitLogService::$page_size);
        if($total>\Common\Service\ItemTimelimitLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        if (I('export')) {
            $excel_data = [];
            $excel_data[] = ["商品名称","活动开始时间","活动结束时间","经销价","会员价","抢购价","经销价差价","会员价差价","购物数量"];
            foreach ($data as $value) {
                $temp = [];
                $temp[] = $value['title'];
                $temp[] = $value['start_time'];
                $temp[] = $value['end_time'];
                $temp[] = format_price($value['dealer_price']);
                $temp[] = format_price($value['price']);
                $temp[] = format_price($value['timelimit_price']);
                $temp[] = format_price($value['d_dealer_price']);
                $temp[] = format_price($value['d_price']);
                $temp[] = $value['num'];
                $excel_data[] = $temp;
            }
            exportexcel($excel_data,'', '限时抢购统计');
            exit();
        }

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->ItemTimelimitLogService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除限时抢购统计');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->ItemTimelimitLogService->get_info_by_id($id);
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
                $ret = $this->ItemTimelimitLogService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改限时抢购统计');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ItemTimelimitLogService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加限时抢购统计');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {
            foreach ($data as $key => $_item) {
                $data[$key]['d_dealer_price'] = $_item['dealer_price'] - $_item['timelimit_price'];
                $data[$key]['d_price'] = $_item['price'] - $_item['timelimit_price'];
            }
        }
    }
}