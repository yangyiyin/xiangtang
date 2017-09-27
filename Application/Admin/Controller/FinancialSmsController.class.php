<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class FinancialSmsController extends AdminController {
    private $SmsService;
    protected function _initialize() {
        parent::_initialize();
        $this->SmsService = \Common\Service\SmsService::get_instance();
    }

    public function index() {

        $year = intval(date('Y'));
        $month = intval(date('m'));

        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $all_departments = $DepartmentService->get_all();
        $all_departments_map = result_to_complex_map($all_departments, 'type');
        $data = [];
        //保险公司property
        $data1 = [
            'name'=>'保险公司',
            'num'=>0,
            'child'=>[];
        ];
        $InsurancePropertyService = \Common\Service\InsurancePropertyService::get_instance();
        $logs = $InsurancePropertyService->get_by_month_year($year, $month);
        $departments = $all_departments_map[\Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty];
        $all_names = result_to_array($departments, 'all_name');
        $logs_all_names = result_to_array($logs, 'all_name');
        $not_log_all_names = array_diff($all_names, $logs_all_names);
        if ($not_log_all_names) {

        }




        $this->assign('list', $data);

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->SmsService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除广告');
        $this->success('禁用成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $courier = $this->SmsService->get_info_by_id($id);
            if ($courier) {
                $this->assign('info',$courier);
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
            if ($data['imgs']) {
                $data['imgs'] = join(',', $data['imgs']);
            }
            if ($id) {
                $ret = $this->SmsService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改广告');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->SmsService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加广告');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

}