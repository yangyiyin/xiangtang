<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author Jroy
 */
class DepartmentController extends AdminController {
    private $DepartmentService;
    protected function _initialize() {
        parent::_initialize();
        $this->DepartmentService = \Common\Service\DepartmentService::get_instance();
    }
    public function index() {

        $where = [];
        if (I('get.type')) {
            $where['type'] = ['EQ', I('get.type')];
        }

        if (I('get.all_name')) {
            $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
        }

        $this->assign('cat_options', $this->get_cat_options(I('type',0)));

        $page = I('get.p', 1);
        list($data, $count) = $this->DepartmentService->get_by_where($where, 'id desc', $page);
        $PageInstance = new \Think\Page($count, \Common\Service\DepartmentService::$page_size);
        if($total>\Common\Service\DepartmentService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }


    private function get_cat_options($selected_id = 0) {
        $all = C('GROUP_Financial_CATS_MAP');
        $options = '';
        if ($all) {
            foreach ($all as $key=>$value) {
                if ($selected_id && $selected_id == $key) {
                    $options .= '<option selected="selected" value="'.$key.'">'.$value.'</option>';
                } else {
                    $options .= '<option value="'.$key.'">'.$value.'</option>';
                }
            }
        }
        return $options;
    }

    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->DepartmentService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除部门');
        $this->success('删除成功！');
    }

    public function add() {
        $type = $sub_type = 0;
        if ($id = I('get.id')) {
            $info = $this->DepartmentService->get_info_by_id($id);
            if ($info) {
                $type = $info['type'];
                $sub_type = $info['sub_type'];
                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }

        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $bank_type_options = $DepartmentService->get_sub_type_options($sub_type);
        $this->assign('bank_type_options', $bank_type_options);
        $this->assign('cat_options', $this->get_cat_options($type));

        $type = $type ? $type : 1;
        $this->assign('fields', $this->get_avalible_fields($type));


        $this->display();
    }

    private function get_avalible_fields($type) {
        $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel'];
        switch ($type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital','total_assets','staffs'];
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','capital','build_time'];

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank:
                $fields = ['all_name','address','principal','phone','fixed_phone','tel_phone','filler_man','filler_man_tel','sub_type'];

                break;
        }
        array_push($fields,'other_name');
        return $fields;
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            $type = I('post.type');

            $fields = $this->get_avalible_fields($type);
            $data_new = [];
            foreach ($data as $field => $value) {
                if (in_array($field, $fields)) {
                    $data_new[$field] = $value;
                }
            }
            $data_new['type'] = $type;
            $data = $data_new;
            if ($id) {
                unset($data['all_name']);//不能修改全称,因为都是靠all_name查询
                $ret = $this->DepartmentService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改部门信息');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->DepartmentService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加部门信息');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }



}
