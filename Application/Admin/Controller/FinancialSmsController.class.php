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

        $smses = $this->SmsService->get_by_month_year($year, $month);
        $smses_map = result_to_map($smses, 'phone');
        $data = [];

        $data[] = $this->get_data('保险公司(财产)',\Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('保险公司(人身)',\Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('保险互助社',\Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('担保公司',\Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('股权投资机构',\Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('股权投资管理机构',\Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('期货营业部',\Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('融资租赁',\Common\Model\FinancialDepartmentModel::TYPE_FinancialLease,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('小额贷款公司',\Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('银行机构',\Common\Model\FinancialDepartmentModel::TYPE_FinancialBank,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('证券营业部',\Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities,$year,$month,$all_departments_map,$smses_map);
        $data[] = $this->get_data('转贷',\Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds,$year,$month,$all_departments_map,$smses_map);


        $this->assign('list', $data);

        $this->display();
    }


    private function get_data($name,$type,$year,$month,$all_departments_map,$smses_map){
        //保险公司property
        $data1 = [
            'name'=>$name,
            'num'=>0,
            'child'=>[],
            'group' => $type . '-'
        ];
        $InsurancePropertyService = \Common\Service\InsurancePropertyService::get_instance();
        $logs = $InsurancePropertyService->get_by_month_year($year, $month);
        $departments = $all_departments_map[$type];
        $all_names = result_to_array($departments, 'all_name');
        $logs_all_names = result_to_array($logs, 'all_name');
        $not_log_all_names = array_diff($all_names, $logs_all_names);
        if ($not_log_all_names) {
            $departments_map = result_to_map($departments, 'all_name');
            $temp = [];
            $num = 0;
            foreach ($not_log_all_names as $__k => $_all_name) {
                $temp = [
                    'name'=>$_all_name,
                    'num'=>0,
                    'child'=>[],
                    'group' => $data1['group'] . $__k . '-'
                ];

                if (isset($departments_map[$_all_name])) {

                    $filler_mans = explode(',',$departments_map[$_all_name]['filler_man']);
                    $filler_man_tels = explode(',',$departments_map[$_all_name]['filler_man_tel']);
                }

                if ($filler_man_tels) {
                    $temp_temp = [];
                    foreach ($filler_man_tels as $k => $v) {
                        $temp_temp[] = [
                            'name' => isset($filler_mans[$k]) ? $filler_mans[$k] : '未知人',
                            'tel' => $v,
                            'count' => isset($smses_map[$v]['count']) ? $smses_map[$v]['count'] : 0,
                            'group' => $temp['group'] . $k
                        ];
                    }
                    $temp['num'] = count($temp_temp) + 1;
                    $temp['child'] = $temp_temp;
                    $num +=  $temp['num'];
                }
                $data1['child'][] = $temp;
                $data1['num'] = $num + 1;
            }
        }
        return $data1;
    }

    public function send() {
        $ids = I('ids');
        $post_data = [];
        $post_data['sender'] = '74753AB482BE5C724076F9181A3FF8CB';
        $post_data['content'] = '1q23';
        $post_data['mobiles'] = '13646847040,杨益银';
        $post_data['charset'] = 'utf-8';

//        $ch = curl_init();
//        $timeout = 5;
//        curl_setopt ($ch, CURLOPT_URL, 'http://hd.cixi.gov.cn/sms.rs?Send');
//        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//        $file_contents = curl_exec($ch);
//        curl_close($ch);
//        var_dump($file_contents);
        //更新发送次数
        $year = intval(date('Y'));
        $month = intval(date('m'));
        $this->SmsService->add_count($year, $month, $ids, $post_data['content']);
        $this->success('发送成功!');
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