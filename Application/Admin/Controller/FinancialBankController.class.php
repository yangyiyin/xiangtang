<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 07:53:24
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialBankController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank;

         parent::_initialize();
     }

     /**
      * 不良贷款处置明细
      */
     public function baddebt_dispose_submit_monthly()
     {

         $this->local_service = \Common\Service\BankBaddebtDisposeService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;
//             if (!$data['logs1']) {
//                 $this->error('请填写完整的信息~');
//             }
             if (!$this->is_history) {
                 $data['year'] = intval(date('Y'));
                 $data['month'] = intval(date('m'));
             } else {
                 $time = intval(strtotime($data['year'] . '-' . $data['month']));
                 if (!$time || $time > strtotime('201712')) {
                     $this->error('历史数据时间必须小于201712');
                 }
             }
             $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name']);
             if ($ret){
                 //删除
                 if ($this->is_history && !$data['force_modify']) {
                     $this->error('该月报表已经提交,如需修改,请勾选强制修改');
                 }
                 $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name']);
             }

             $batch_data = [];
             $good_key = I('post.good_key');
             $cache_data = S($good_key);
             if ($cache_data) {
                 foreach ($cache_data as $value) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Enterprise'] = $value[0];
                     $temp['Principal'] = $value[1];
                     $temp['Area'] = $value[2];
                     $temp['Recover'] = $value[3];
                     $temp['Recover_Time'] = strtotime($value[4]);
                     $temp['Recover_Method'] = $value[5];
                     $temp['Remarks'] = (string) $value[6];
                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];

                     $batch_data[] = $temp;
                 }
             }
//             foreach ($data['logs1'] as $k => $v) {
//                 if ($v) {
//                     $temp = [];
//                     $temp['all_name'] = $data['all_name'];
//                     $temp['year'] = $data['year'];
//                     $temp['month'] = $data['month'];
//                     $temp['uid'] = $data['uid'];
//                     $temp['filler_man'] = $data['filler_man'];
//                     $temp['gmt_create'] = time();
//                     $temp['Enterprise'] = $v;
//                     $temp['Principal'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
//                     $temp['Area'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
//                     $temp['Recover'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
//                     $temp['Recover_Time'] = isset($data['logs5'][$k]) ? strtotime($data['logs5'][$k]) : 0;
//                     $temp['Recover_Method'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : 0;
//                     $temp['Remarks'] = isset($data['logs7'][$k]) ? $data['logs7'][$k] : '';
//
//                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
//                     $batch_data[] = $temp;
//                 }
//
//             }
             $ret = $this->local_service->add_batch($batch_data);
             if ($ret->success) {
                 //$this->update_st($batch_data);
                 //$this->update_area_a_st($data['year'], $data['month']);
                 action_user_log('新增不良贷款处置报表');
                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }


         } else {
             $this->title = '不良贷款处置月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '不良贷款处置月填报[正在编辑历史数据]';
             }

             $this->assign('title', $this->title);

             //获取所有相关的公司
             $DepartmentService = \Common\Service\DepartmentService::get_instance();

             $departments = $DepartmentService->get_my_list(UID, $this->type);


             if (!$departments) {
                 $departments = $DepartmentService->get_all_list($this->type);
             } else {
                 $data = $departments[0];
             }
             $departments = result_to_array($departments, 'all_name');
             $this->assign('departments', $departments);

             //获取当期的数据
             $infos = [];
             if (!$this->is_history) {
                 if (isset($data['all_name']) && $data['all_name']) {
                     $infos = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);
                     //$this->convert_data_detail_submit_monthly($infos);
                 }
             }
             //获取区域
             $AreaService = \Common\Service\AreaService::get_instance();
             if ($infos) {

                 $infos = $AreaService->set_area_options($infos);

                 $infos = $this->local_service->recover_method_options($infos);
             }
             $this->assign('infos', $infos);
             $this->assign('area_options', $AreaService->set_area_options());
             $this->assign('recover_method_options', $this->local_service->recover_method_options());



             $this->display();
         }
     }

     public function add_history_baddebt_dispose_submit_monthly() {
         $this->is_history = true;
         $this->baddebt_dispose_submit_monthly();
     }

     public function baddebt_dispose_submit_log() {
         $this->local_service = \Common\Service\BankBaddebtDisposeService::get_instance();
         $where = [];
         $all_name = '';
         if (I('get.all_name')) {
             $all_name = I('get.all_name');
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);

         if ($departments) {
             $where['all_name'] = $departments[0]['all_name'];
             $all_name = $departments[0]['all_name'];
             $this->assign('only_my_department', false);
         } else {
             $this->assign('only_my_department', true);
         }

         $this->assign('all_name', $all_name);
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_baddebt_dispose_submit_log($data);
         $data = $this->convert_data_baddebt_dispose_st($data);
         $service = '\Common\Service\BankBaddebtDisposeService';
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     protected function convert_data_baddebt_dispose_st($data) {
         $result = [];
         if ($data) {
             $new_data = [];
             foreach ($data as $k => $v) {
                 $new_data[$v['all_name'] . $v['year'] . $v['month']][] = $v;
             }

             foreach ($new_data as $k => $v) {
                 $temp = $v[0];
                 foreach ($v as $li) {
                     $temp['all_Recover'] += $li['Recover'];
                 }
                 $result[] = $temp;
             }

         }
         return $result;
     }
     protected function convert_data_baddebt_dispose_submit_log(&$data) {
         if ($data) {
             $AreaService = \Common\Service\AreaService::get_instance();
             $areas = $AreaService->get_all();
             $areas_map = result_to_map($areas);
             $recover_method_map = \Common\Model\FinancialBankBaddebtDisposeModel::$RECOVER_METHOD_MAP;
             foreach ($data as $k => $v) {
                 $data[$k]['area_name'] = isset($areas_map[$v['Area']]['name']) ? $areas_map[$v['Area']]['name'] : '未知';
                 $data[$k]['recover_time'] = time_to_date($v['Recover_Time']);
                 $data[$k]['recover_method'] = isset($recover_method_map[$v['Recover_Method']]) ? $recover_method_map[$v['Recover_Method']] : '未知';
             }
         }
     }


     /**
      * 信贷情况月报
      */
     public function credit_new_submit_monthly()
     {
         $this->local_service = \Common\Service\BankCreditNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;

         parent::submit_monthly();

     }
     protected function convert_data_submit_monthly(&$info) {
        if ($info) {
            foreach ($info as $field => $data) {
                $arr = explode('|', $data);
                if (count($arr) == 3) {
                    $info[$field] = [];
                    $info[$field]['a'] = $arr[0];
                    $info[$field]['b'] = $arr[1];
                    $info[$field]['c'] = $arr[2];
                }
                if (count($arr) == 4) {
                    $info[$field] = [];
                    $info[$field]['a'] = $arr[0];
                    $info[$field]['b'] = $arr[1];
                    $info[$field]['c'] = $arr[2];
                    $info[$field]['d'] = $arr[3];
                }

                if (count($arr) == 5) {
                    $info[$field] = [];
                    $info[$field]['a'] = $arr[0];
                    $info[$field]['b'] = $arr[1];
                    $info[$field]['c'] = $arr[2];
                    $info[$field]['d'] = $arr[3];
                    $info[$field]['e'] = $arr[4];
                }

            }
        }
     }



     /**
      * 信贷情况月报
      */
     public function quarterly_quantity_a_new_submit_monthly()
     {

         $this->local_service = \Common\Service\BankQuaterlyQuantityANewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter;
         parent::submit_monthly();

     }

     /**
      * 信贷情况月报
      */
     public function quarterly_quantity_b_new_submit_monthly()
     {
         $this->local_service = \Common\Service\BankQuaterlyQuantityBNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter;
         parent::submit_monthly();

     }

     /**
      * 信贷情况月报
      */
     public function quarterly_quantity_c_new_submit_monthly()
     {
         $this->local_service = \Common\Service\BankQuaterlyQuantityCNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter;
         parent::submit_monthly();

     }


     public function add_history_credit_submit_monthly() {
         $this->is_history = true;
         $this->credit_submit_monthly();
     }


     public function credit_submit_log() {
         $this->local_service = \Common\Service\BankCreditService::get_instance();
         $where = [];
         if (I('get.all_name')) {
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);

         if ($departments) {
             $where['all_name'] = $departments[0]['all_name'];
             $this->assign('only_my_department', false);
         } else {
             $this->assign('only_my_department', true);
         }
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
//         $this->convert_data_credit_submit_log($data);
         $service = '\Common\Service\BankCreditService';
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }


     protected function update_loan_st($batch_data) {
         $BankBaddebtStService = \Common\Service\BankBaddebtStService::get_instance();
         $st = $BankBaddebtStService->get_by_month_year($batch_data[0]['year'], $batch_data[0]['month'], $batch_data[0]['all_name']);

         $data = [];

         $data['uid'] = $batch_data[0]['uid'];
         $data['all_name'] = $batch_data[0]['all_name'];
         $data['filler_man'] = $batch_data[0]['filler_man'];
         $data['year'] = $batch_data[0]['year'];
         $data['month'] = $batch_data[0]['month'];
         $data['ip'] = $_SERVER["REMOTE_ADDR"];
         $data['gmt_create'] = time();


         $data['Loans'] = $data['Pattern_1'] = $data['Pattern_2'] = $data['Pattern_3'] = $data['Pattern_4'] = $data['Pattern_5'] = $data['Baddebt_Month'] = 0;
         foreach ($batch_data as $value) {
             $data['Loans'] += $value['Loans'];
             if ($value['Pattern'] == 1) {
                 $data['Pattern_1'] += $value['Loans'];
             }
             if ($value['Pattern'] == 2) {
                 $data['Pattern_2'] += $value['Loans'];
             }
             if ($value['Pattern'] == 3) {
                 $data['Pattern_3'] += $value['Pattern'];
                 $data['Baddebt_Month'] += $value['Loans'];
             }
             if ($value['Pattern'] == 4) {
                 $data['Pattern_4'] += $value['Pattern'];
                 $data['Baddebt_Month'] += $value['Loans'];
             }
             if ($value['Pattern'] == 5) {
                 $data['Pattern_5'] += $value['Pattern'];
                 $data['Baddebt_Month'] += $value['Loans'];
             }

         }

         if ($data['Loans']) {
             $data['Baddebt_Rate'] = fix_2($data['Baddebt_Month'] / $data['Loans']);
         } else {
             $data['Baddebt_Rate'] = 0;
         }

         //获取上月,年初,去年同期数据
         if ($batch_data[0]['month'] == 1) {
             $st_last_month = $BankBaddebtStService->get_by_month_year($batch_data[0]['year'] - 1, 12, $batch_data[0]['all_name']);
         } else {
             $st_last_month = $BankBaddebtStService->get_by_month_year($batch_data[0]['year'], $batch_data[0]['month'] - 1, $batch_data[0]['all_name']);
         }
         $st_last_year_end = $BankBaddebtStService->get_by_month_year($batch_data[0]['year'] - 1, 12, $batch_data[0]['all_name']);
         $st_last_year = $BankBaddebtStService->get_by_month_year($batch_data[0]['year'] - 1, $batch_data[0]['month'], $batch_data[0]['all_name']);
         $data['Baddebt_Lastmon'] = isset($st_last_month['Baddebt_Month']) ? $st_last_month['Baddebt_Month'] : 0;
         $data['Baddebt_Initial'] = isset($st_last_year_end['Baddebt_Month']) ? $st_last_year_end['Baddebt_Month'] : 0;
         $data['Baddebt_Lastyear'] = isset($st_last_year['Baddebt_Month']) ? $st_last_year['Baddebt_Month'] : 0;
         $data['Baddebt_Rate_Lastmon'] = isset($st_last_month['Baddebt_Rate']) ? $st_last_month['Baddebt_Rate'] : 0;
         $data['Baddebt_Rate_Initial'] = isset($st_last_year_end['Baddebt_Rate']) ? $st_last_year_end['Baddebt_Rate'] : 0;
         $data['Baddebt_Rate_Lastyear'] = isset($st_last_year['Baddebt_Rate']) ? $st_last_year['Baddebt_Rate'] : 0;
         if ($st) {//更新
             $ret = $BankBaddebtStService->update_by_id($st['id'], $data);
         } else {//新增
             $ret = $BankBaddebtStService->add_one($data);
         }

         if (!$ret->success) {
             $this->error('生成不良贷款统计记录失败~');
         }

     }


     public function update_area_a_st($year, $month) {
         $type = \Common\Model\FinancialBankAreaStModel::TYPE_A;
         $BankAreaStService = \Common\Service\BankAreaStService::get_instance();
         $st = $BankAreaStService->get_by_month_year($year, $month, $type);
         if ($st) {
             $BankAreaStService->delete_by_month_year($year, $month, $type);
         }
         //获取本月贷款明细
         $BankLoanDetailService = \Common\Service\BankLoanDetailService::get_instance();
         $extra  = ['status' => 2];
         $month_data = $BankLoanDetailService->get_by_month_year_all_names($year, $month, [], true, false, $extra);
         //获取上月贷款明细
         if ($month == 1) {
             $last_month_data = $BankLoanDetailService->get_by_month_year_all_names($year - 1, 12, [], true, false, $extra);
         } else {
             $last_month_data = $BankLoanDetailService->get_by_month_year_all_names($year, $month - 1, [], true, false, $extra);
         }

         $last_month_data_area_map = result_to_complex_map($last_month_data, 'Area');
         $month_data_area_map = result_to_complex_map($month_data, 'Area');

         //获取本月不良贷款处置明细
         $BankBaddebtDisposeService = \Common\Service\BankBaddebtDisposeService::get_instance();
         $BaddebtDispose_month_data = $BankBaddebtDisposeService->get_by_month_year_all_names($year, $month, [], false, $extra);
         $BaddebtDispose_month_area_map = result_to_complex_map($BaddebtDispose_month_data, 'Area');
         $batch_data = [];
         foreach ($month_data_area_map as $key => $area_month_data) {
             if (!isset($last_month_data_area_map[$key])) { //新的街道的
                 //获取本月和本月新增数据
                 $month_count = count($area_month_data);
                 $new_month_count = count($area_month_data);

                 $month_loan = $new_month_loan = 0;
                 foreach ($area_month_data as $loan) {
                     $month_loan += $loan['Loans'];
                 }
                 foreach ($area_month_data as $loan) {
                     $new_month_loan += $loan['Loans'];
                 }

             } else { //在老的街道里
                 //比较诧异
                 $last_month_data_map = result_to_map($last_month_data_area_map[$key], 'all_name');
                 $new_data = [];
                 foreach ($area_month_data as $data) {
                     if (!isset($last_month_data_map[$data['all_name']])) {
                         $new_data[] = $data;
                     }
                 }

                 $month_count = count($area_month_data);
                 $new_month_count = count($new_data);

                 $month_loan = $new_month_loan = 0;
                 foreach ($area_month_data as $loan) {
                     $month_loan += $loan['Loans'];
                 }
                 foreach ($new_data as $loan) {
                     $new_month_loan += $loan['Loans'];
                 }


             }

             //获取本月不良贷款处置数
             $BaddebtDispose_loans = $BaddebtDispose_count = 0;
             if (isset($BaddebtDispose_month_area_map[$key])) {
                 $BaddebtDispose_count = count($BaddebtDispose_month_area_map[$key]);
                 $BaddebtDispose_loans = 0;
                 foreach ($BaddebtDispose_month_area_map[$key] as $BaddebtDispose) {
                     $BaddebtDispose_loans += $BaddebtDispose['Recover'];
                 }
             }

             $temp = [];
             $temp['Baddebt_Firms'] = $month_count;
             $temp['Baddebt_Balance'] = $month_loan;
             $temp['Baddebt_Firms_add'] = $new_month_count;
             $temp['Baddebt_Balance_add'] = $new_month_loan;
             $temp['Recover'] = $BaddebtDispose_loans;
             $temp['Recover_Firms'] = $BaddebtDispose_count;
             $temp['type'] = $type;

             $temp['Area'] = $key;
             $temp['uid'] = UID;
             $temp['year'] = $year;
             $temp['month'] = $month;
             $data['ip'] = $_SERVER["REMOTE_ADDR"];
             $data['gmt_create'] = time();

             $batch_data[] = $temp;
         }

         if ($batch_data) {

             $ret = $BankAreaStService->add_batch($batch_data);

             if ($ret->success) {
//                 action_user_log('新增贷款明细报表');
//                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }
         }

     }

     public function update_area_b_st($year, $month) {
         $type = \Common\Model\FinancialBankAreaStModel::TYPE_B;
         $BankAreaStService = \Common\Service\BankAreaStService::get_instance();
         $st = $BankAreaStService->get_by_month_year($year, $month, $type);
         if ($st) {
             $BankAreaStService->delete_by_month_year($year, $month, $type);
         }
         $extra  = ['status' => 2];
         //获取本月贷款明细
         $BankLoanDetailService = \Common\Service\BankLoanDetailService::get_instance();
         $month_data = $BankLoanDetailService->get_by_month_year_all_names($year, $month, [], false, true, $extra);
         //获取上月贷款明细
         if ($month == 1) {
             $last_month_data = $BankLoanDetailService->get_by_month_year_all_names($year - 1, 12, [], false, true, $extra);
         } else {
             $last_month_data = $BankLoanDetailService->get_by_month_year_all_names($year, $month - 1, [], false, true, $extra);
         }

         $last_month_data_area_map = result_to_complex_map($last_month_data, 'Area');
         $month_data_area_map = result_to_complex_map($month_data, 'Area');

         //获取本月逾期化解明细
         $BankOverdueResolveService = \Common\Service\BankOverdueResolveService::get_instance();
         $BankOverdue_month_data = $BankOverdueResolveService->get_by_month_year_all_names($year, $month, [], $extra);
         $BankOverdue_month_area_map = result_to_complex_map($BankOverdue_month_data, 'Area');
         $batch_data = [];
         foreach ($month_data_area_map as $key => $area_month_data) {
             if (!isset($last_month_data_area_map[$key])) { //新的街道的
                 //获取本月和本月新增数据
                 $month_count = count($area_month_data);
                 $new_month_count = count($area_month_data);

                 $month_loan = $new_month_loan = 0;
                 foreach ($area_month_data as $loan) {
                     $month_loan += $loan['Loans'];
                 }
                 foreach ($area_month_data as $loan) {
                     $new_month_loan += $loan['Loans'];
                 }

             } else { //在老的街道里
                 //比较诧异
                 $last_month_data_map = result_to_map($last_month_data_area_map[$key], 'all_name');
                 $new_data = [];
                 foreach ($area_month_data as $data) {
                     if (!isset($last_month_data_map[$data['all_name']])) {
                         $new_data[] = $data;
                     }
                 }

                 $month_count = count($area_month_data);
                 $new_month_count = count($new_data);

                 $month_loan = $new_month_loan = 0;
                 foreach ($area_month_data as $loan) {
                     $month_loan += $loan['Loans'];
                 }
                 foreach ($new_data as $loan) {
                     $new_month_loan += $loan['Loans'];
                 }


             }

             //获取本月逾期化解数
             $BankOverdue_loans = $BankOverdue_count = 0;
             if (isset($BankOverdue_month_area_map[$key])) {
                 $BankOverdue_count = count($BankOverdue_month_area_map[$key]);
                 $BankOverdue_loans = 0;
                 foreach ($BankOverdue_month_area_map[$key] as $BankOverdue) {
                     $BankOverdue_loans += $BankOverdue['Resolve'];
                 }
             }

             $temp = [];
             $temp['Overdue_Firms'] = $month_count;
             $temp['Overdue_Balance'] = $month_loan;
             $temp['Overdue_Firms_add'] = $new_month_count;
             $temp['Overdue_Balance_add'] = $new_month_loan;
             $temp['Resolve_Amount'] = $BankOverdue_loans;
             $temp['Resolve_Firms'] = $BankOverdue_count;
             $temp['type'] = $type;

             $temp['Area'] = $key;
             $temp['uid'] = UID;
             $temp['year'] = $year;
             $temp['month'] = $month;
             $data['ip'] = $_SERVER["REMOTE_ADDR"];
             $data['gmt_create'] = time();

             $batch_data[] = $temp;
         }

         if ($batch_data) {

             $ret = $BankAreaStService->add_batch($batch_data);

             if ($ret->success) {
//                 action_user_log('新增贷款明细报表');
//                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }
         }

     }


     public function add_history_loan_details_submit_monthly() {
         $this->is_history = true;
         $this->loan_details_submit_monthly();
     }

     public function loan_details_submit_log() {
         $this->local_service = \Common\Service\BankLoanDetailService::get_instance();
         $where = [];
         $all_name = '';
         if (I('get.all_name')) {
             $all_name = I('get.all_name');
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);

         if ($departments) {
             $where['all_name'] = $departments[0]['all_name'];
             $all_name = $departments[0]['all_name'];
             $this->assign('only_my_department', false);
         } else {
             $this->assign('only_my_department', true);
         }
         $this->assign('all_name', $all_name);
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_loan_details_submit_log($data);
         $data = $this->convert_data_loan_details_st($data);
         $service = '\Common\Service\BankLoanDetailService';
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     protected function convert_data_loan_details_submit_log(&$data) {
         if ($data) {
             $AreaService = \Common\Service\AreaService::get_instance();
             $areas = $AreaService->get_all();
             $areas_map = result_to_map($areas);
             $pattern_map = \Common\Model\FinancialBankLoanDetailModel::$PATTERN_MAP;
             foreach ($data as $k => $v) {
                 $data[$k]['area_name'] = isset($areas_map[$v['Area']]['name']) ? $areas_map[$v['Area']]['name'] : '未知';
                 $data[$k]['pattern'] = isset($pattern_map[$v['Pattern']]) ? $pattern_map[$v['Pattern']] : '未知';
             }



         }
     }

     protected function convert_data_loan_details_st($data) {
         $result = [];
         if ($data) {
             $new_data = [];
             foreach ($data as $k => $v) {
                 $new_data[$v['all_name'] . $v['year'] . $v['month']][] = $v;
             }

             foreach ($new_data as $k => $v) {
                 $temp = $v[0];
                 foreach ($v as $li) {
                     $temp['all_Loans'] += $li['Loans'];
                     $temp['all_Over_Credit'] += $li['Over_Credit'];
                     $temp['all_Over_Mortgage'] += $li['Over_Mortgage'];
                     $temp['all_Over_Pledge'] += $li['Over_Pledge'];
                     $temp['all_Over_Margin'] += $li['Over_Margin'];
                 }
                 $result[] = $temp;
             }

         }
         return $result;
     }

     /**
      * 季度报
      */
     public function quarterly_submit()
     {
         $this->local_service = \Common\Service\BankQuarterlyService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;

             $data['Loans_A_Sub'] = join(',', $data['Loans_A_Sub']);
             $data['Loans_B_Sub'] = join(',', $data['Loans_B_Sub']);
             $data['Loans_C_Sub'] = join(',', $data['Loans_C_Sub']);
             $data['Loans_D_Sub'] = join(',', $data['Loans_D_Sub']);
             $data['Loans_E_Sub'] = join(',', $data['Loans_E_Sub']);
             $data['Loans_F_Sub'] = join(',', $data['Loans_F_Sub']);
             $data['Loans_G_Sub'] = join(',', $data['Loans_G_Sub']);
             $data['Loans_H_Sub'] = join(',', $data['Loans_H_Sub']);
             $data['Loans_Ratio'] = fix_2($data['Deposits'] / $data['Loans']);
             $data['Other_Item_Sub'] = join(',', $data['Other_Item_Sub']);
             $data['Staff_Sub'] = join(',', $data['Staff_Sub']);



             if (!$this->is_history) {
                 $data['year'] = intval(date('Y'));
                 $data['month'] = intval(date('m'));
             } else {
                 $time = intval(strtotime($data['year'] . '-' . $data['month']));
                 if (!$time || $time > strtotime('201712')) {
                     $this->error('历史数据时间必须小于201712');
                 }
             }
             if ($id) {
                 $ret = $this->local_service->update_by_id($id, $data);
                 if ($ret->success) {
                     action_user_log('修改季度报表');
                     $this->success('修改成功！');
                 } else {
                     $this->error($ret->message);
                 }
             } else {
                 $check_ret = $this->check_by_month_year($data['year'], $data['month'], $data['all_name']);
                 if ($check_ret === true){
                     //新增 不做处理
                 } elseif($check_ret) {
                     if ($data['force_modify']) {//强制修改
                         $id = $check_ret['id'];
                         $ret = $this->local_service->update_by_id($id, $data);
                         if ($ret->success) {
                             action_user_log('修改季度报表');
                             $this->success('修改成功！');
                         } else {
                             $this->error($ret->message);
                         }
                     } else {
                         $this->error('该月已提交报表,请不要重复提交');
                     }
                 } else {
                     $this->error('参数错误');
                 }
                 $ret = $this->local_service->add_one($data);
                 if ($ret->success) {
                     action_user_log('新增季度报表');
                     $this->success('添加成功！');
                 } else {
                     $this->error($ret->message);
                 }
             }
         } else {
             $this->title = '季度填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '季度填报[正在编辑历史数据]';
             }

             $this->assign('title', $this->title);

             //获取所有相关的公司
             $DepartmentService = \Common\Service\DepartmentService::get_instance();

             $departments = $DepartmentService->get_my_list(UID, $this->type);


             if (!$departments) {
                 $departments = $DepartmentService->get_all_list($this->type);
             } else {
                 $data = $departments[0];
             }
             $departments = result_to_array($departments, 'all_name');
             $this->assign('departments', $departments);

             //获取当期的数据
             $info = [];
             if (!$this->is_history) {
                 if (isset($data['all_name']) && $data['all_name']) {
                     $info = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);

                     $info['Loans_A_Sub'] = explode(',', $info['Loans_A_Sub']);
                     $info['Loans_B_Sub'] = explode(',', $info['Loans_B_Sub']);
                     $info['Loans_C_Sub'] = explode(',', $info['Loans_C_Sub']);
                     $info['Loans_D_Sub'] = explode(',', $info['Loans_D_Sub']);
                     $info['Loans_E_Sub'] = explode(',', $info['Loans_E_Sub']);
                     $info['Loans_F_Sub'] = explode(',', $info['Loans_F_Sub']);
                     $info['Loans_G_Sub'] = explode(',', $info['Loans_G_Sub']);
                     $info['Loans_H_Sub'] = explode(',', $info['Loans_H_Sub']);

                     $info['Other_Item_Sub'] = explode(',', $info['Other_Item_Sub']);
                     $info['Staff_Sub'] = explode(',', $info['Staff_Sub']);
                 }
             }
             $this->assign('info', $info);

             if (!$info && !$this->is_history && isset($data['all_name']) && $data['all_name']) {
                 //获取季度存贷款
                 $BankCreditService = \Common\Service\BankCreditService::get_instance();
                 $datas = $BankCreditService->get_quarterly_data($data['all_name']);
                 //var_dump($datas);
                 if ($datas) {
                     $loans = $deposits = 0;
                     foreach ($datas as $credit) {
                         $loans += $credit['Loans'];
                         $deposits += $credit['Deposits'];
                     }

                     $this->assign('loans',$loans);
                     $this->assign('deposits',$deposits);
                 }

             }

             $this->display();
         }
     }

     public function add_history_quarterly_submit() {
         $this->is_history = true;
         $this->quarterly_submit();
     }


     public function quarterly_submit_log() {
         $this->local_service = \Common\Service\BankQuarterlyService::get_instance();
         $where = [];
         if (I('get.all_name')) {
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);

         if ($departments) {
             $where['all_name'] = $departments[0]['all_name'];
             $this->assign('only_my_department', false);
         } else {
             $this->assign('only_my_department', true);
         }
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);

         foreach ($data as &$info) {

             $info['Loans_A_Sub'] = explode(',', $info['Loans_A_Sub']);
             $info['Loans_B_Sub'] = explode(',', $info['Loans_B_Sub']);
             $info['Loans_C_Sub'] = explode(',', $info['Loans_C_Sub']);
             $info['Loans_D_Sub'] = explode(',', $info['Loans_D_Sub']);
             $info['Loans_E_Sub'] = explode(',', $info['Loans_E_Sub']);
             $info['Loans_F_Sub'] = explode(',', $info['Loans_F_Sub']);
             $info['Loans_G_Sub'] = explode(',', $info['Loans_G_Sub']);
             $info['Loans_H_Sub'] = explode(',', $info['Loans_H_Sub']);

             $info['Other_Item_Sub'] = explode(',', $info['Other_Item_Sub']);
             $info['Staff_Sub'] = explode(',', $info['Staff_Sub']);
         }

//         $this->convert_data_credit_submit_log($data);
         $service = '\Common\Service\BankQuarterlyService';
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     /**
      * 逾期化解明细
      */
     public function overdue_resolve_submit_monthly()
     {

         $this->local_service = \Common\Service\BankOverdueResolveService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;
//             if (!$data['logs1']) {
//                 $this->error('请填写完整的信息~');
//             }
             if (!$this->is_history) {
                 $data['year'] = intval(date('Y'));
                 $data['month'] = intval(date('m'));
             } else {
                 $time = intval(strtotime($data['year'] . '-' . $data['month']));
                 if (!$time || $time > strtotime('201712')) {
                     $this->error('历史数据时间必须小于201712');
                 }
             }
             $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name']);
             if ($ret){
                 //删除
                 if ($this->is_history && !$data['force_modify']) {
                     $this->error('该月报表已经提交,如需修改,请勾选强制修改');
                 }
                 $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name']);
             }

             $batch_data = [];
             $good_key = I('post.good_key');
             $cache_data = S($good_key);
             if ($cache_data) {
                 foreach ($cache_data as $value) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Enterprise'] = $value[0];
                     $temp['Principal'] = $value[1];
                     $temp['Area'] = $value[2];
                     $temp['Overdue'] = $value[3];
                     $temp['Resolve'] = $value[4];
                     $temp['Remarks'] = (string) $value[5];
                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];

                     $batch_data[] = $temp;
                 }
             }
//             foreach ($data['logs1'] as $k => $v) {
//                 if ($v) {
//                     $temp = [];
//                     $temp['all_name'] = $data['all_name'];
//                     $temp['year'] = $data['year'];
//                     $temp['month'] = $data['month'];
//                     $temp['uid'] = $data['uid'];
//                     $temp['filler_man'] = $data['filler_man'];
//                     $temp['gmt_create'] = time();
//                     $temp['Enterprise'] = $v;
//                     $temp['Principal'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
//                     $temp['Area'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
//                     $temp['Overdue'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
//                     $temp['Resolve'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
//                     $temp['Remarks'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : '';
//
//                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
//                     $batch_data[] = $temp;
//                 }
//
//             }
             $ret = $this->local_service->add_batch($batch_data);
             if ($ret->success) {
                 //$this->update_st($batch_data);
                 //$this->update_area_a_st($data['year'], $data['month']);
                 //$this->update_area_b_st($data['year'], $data['month']);
                 action_user_log('新增逾期化解细报表');
                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }


         } else {
             $this->title = '逾期化解明细月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '逾期化解明细月填报[正在编辑历史数据]';
             }

             $this->assign('title', $this->title);

             //获取所有相关的公司
             $DepartmentService = \Common\Service\DepartmentService::get_instance();

             $departments = $DepartmentService->get_my_list(UID, $this->type);


             if (!$departments) {
                 $departments = $DepartmentService->get_all_list($this->type);
             } else {
                 $data = $departments[0];
             }
             $departments = result_to_array($departments, 'all_name');
             $this->assign('departments', $departments);

             //获取当期的数据
             $infos = [];
             if (!$this->is_history) {
                 if (isset($data['all_name']) && $data['all_name']) {
                     $infos = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);
                     //$this->convert_data_detail_submit_monthly($infos);
                 }
             }
             //获取区域
             $AreaService = \Common\Service\AreaService::get_instance();
             if ($infos) {

                 $infos = $AreaService->set_area_options($infos);

             }
             $this->assign('infos', $infos);
             $this->assign('area_options', $AreaService->set_area_options());



             $this->display();
         }
     }

     public function add_history_overdue_resolve_submit_monthly() {
         $this->is_history = true;
         $this->overdue_resolve_submit_monthly();
     }

     public function overdue_resolve_submit_log() {
         $this->local_service = \Common\Service\BankOverdueResolveService::get_instance();
         $where = [];
         $all_name = '';
         if (I('get.all_name')) {
             $all_name = I('get.all_name');
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);

         if ($departments) {
             $where['all_name'] = $departments[0]['all_name'];
             $all_name = $departments[0]['all_name'];
             $this->assign('only_my_department', false);
         } else {
             $this->assign('only_my_department', true);
         }
         $this->assign('all_name', $all_name);
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_overdue_resolve_submit_log($data);
         $data = $this->convert_data_overdue_resolve_st($data);
         $service = '\Common\Service\BankOverdueResolveService';
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     protected function convert_data_overdue_resolve_submit_log(&$data) {
         if ($data) {
             $AreaService = \Common\Service\AreaService::get_instance();
             $areas = $AreaService->get_all();
             $areas_map = result_to_map($areas);
             foreach ($data as $k => $v) {
                 $data[$k]['area_name'] = isset($areas_map[$v['Area']]['name']) ? $areas_map[$v['Area']]['name'] : '未知';
                 // $data[$k]['recover_time'] = time_to_date($v['Recover_Time']);
                 //$data[$k]['recover_method'] = isset($recover_method_map[$v['Recover_Method']]) ? $recover_method_map[$v['Recover_Method']] : '未知';
             }
         }
     }

     protected function convert_data_overdue_resolve_st($data) {
         $result = [];
         if ($data) {
             $new_data = [];
             foreach ($data as $k => $v) {
                 $new_data[$v['all_name'] . $v['year'] . $v['month']][] = $v;
             }

             foreach ($new_data as $k => $v) {
                 $temp = $v[0];
                 foreach ($v as $li) {
                     $temp['all_Overdue'] += $li['Overdue'];
                     $temp['all_Resolve'] += $li['Resolve'];
                 }
                 $result[] = $temp;
             }

         }
         return $result;
     }

     /**
      * 统计
      */
     public function statistics()
     {
         $action = I('action');
         $year = I('year') ? I('year') : intval(date('Y'));
         $month = I('month') ? I('month') : intval(date('m'));
         $p = I('p') ? I('p') : 1;
         $table_id = I('table') ?  I('table') : 1;
         if ($action == 'gain_statistics') {
             $error = [];
             $ret = $this->statistics_1($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表1:'.$ret->message;
             }

             $ret = $this->statistics_2($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表2:'.$ret->message;
             }

             $ret = $this->statistics_3($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表3:'.$ret->message;
             }

             $ret = $this->statistics_4($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表4:'.$ret->message;
             }

             $ret = $this->statistics_5($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表5:'.$ret->message;
             }

             $ret = $this->statistics_6($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表6:'.$ret->message;
             }

             $ret = $this->statistics_7($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表7:'.$ret->message;
             }

             $ret = $this->statistics_8($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表8:'.$ret->message;
             }

             $ret = $this->statistics_9($year, $month);
             if (!$ret->success) {
                 $error[] = '生成表9:'.$ret->message;
             }

             if ($error) {
                 $this->error('生成统计部分错误,产生以下问题:'.join(';',$error));
             }
             $this->success('生成统计成功!');
         }

        //获取统计列表
         $statistics = [
             ['data'=>null,'name'=>'慈溪市金融机构本外币信贷收支情况表(表1)'],
             ['data'=>null,'name'=>'慈溪市金融机构本外币存贷情况表(表2)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良贷款情况表(表3)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良贷款50万(含以上)明细表(表4)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良资产清收情况表(表5)'],
             ['data'=>null,'name'=>'慈溪市金融机构关注类贷款明细表(表6)'],
             ['data'=>null,'name'=>'慈溪市银行贷款利率执行水平监测表(表7)'],
             ['data'=>null,'name'=>'企业贷款利率执行水平监测表(表8)'],
             ['data'=>null,'name'=>'资产质量相关情况调查表(表9)']

         ];
         $Service = \Common\Service\BankCreditAStNewService::get_instance();
         $statistics[0]['data'] = $Service->get_by_month_year($year, $month);
         $Service = \Common\Service\BankCreditBStNewService::get_instance();
         $statistics[1]['data'] = $Service->get_by_month_year($year, $month);
         $Service = \Common\Service\BankBaddebtStNewService::get_instance();
         $statistics[2]['data'] = $Service->get_by_month_year($year, $month);

         $Service = \Common\Service\BankBaddebtDetailStNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         list($statistics[3]['data'], $count_3) = $Service->get_by_where($where, 'gmt_create desc', $p);
         $Service = \Common\Service\BankBaddebtDisposeStNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         list($statistics[4]['data'], $count_4) = $Service->get_by_where($where, 'gmt_create desc', $p);
         $Service = \Common\Service\BankFocusDetailStNewService::get_instance();
         $statistics[5]['data'] = $Service->get_by_month_year($year, $month);
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         list($statistics[5]['data'], $count_5) = $Service->get_by_where($where, 'gmt_create desc', $p);

         $Service = \Common\Service\BankQuarterlyQuantityAStNewService::get_instance();
         $statistics[6]['data'] = $Service->get_by_month_year($year, $month);
         $Service = \Common\Service\BankQuarterlyQuantityBStNewService::get_instance();
         $statistics[7]['data'] = $Service->get_by_month_year($year, $month);
         $Service = \Common\Service\BankQuarterlyQuantityCStNewService::get_instance();
         $statistics[8]['data'] = $Service->get_by_month_year($year, $month);


         if (in_array($table_id, [4,5,6])) {
             if ($table_id == 4) $count = $count_3;
             if ($table_id == 5) $count = $count_4;
             if ($table_id == 6) $count = $count_5;
             $PageInstance = new \Think\Page($count, \Common\Service\BaseService::$page_size);
             if($count>\Common\Service\BaseService::$page_size){
                 $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
             }
             $page_html = $PageInstance->show();
             $this->assign('page_html',$page_html);
         }

         $sub_type_map = \Common\Model\FinancialDepartmentModel::$SUB_TYPE_MAP;

         foreach ($statistics as $k => $_data) {
             $_data = $_data['data'];
             if (!$_data || ($k+1) != $table_id) {
                 continue;
             }
             foreach ($_data as $_k => $_v) {
                 $statistics[$k]['data'][$_k]['content'] = json_decode($_v['content']);
             }

             if (in_array($k, [0,1,2,6,7,8])) {
                 $statistics[$k]['data'] = result_to_complex_map($statistics[$k]['data'], 'department_sub_type');
                 $temp = [];
                 $all = [];
                 foreach ($statistics[$k]['data'] as $_sub_type => $data) {
                     $sub_type_name = isset($sub_type_map[$_sub_type]) ? $sub_type_map[$_sub_type] : '未知';
                     $temp[$sub_type_name] = $data;
                     foreach ($data as $in_value) {
                         foreach ($in_value['content'] as $field => $value) {
                             if (is_array($value)) {
                                 foreach ($value as $_key => $_value) {
                                     $all[$field][$_key] += $_value;
                                 }
                             } else {
                                 $all[$field] += $value;
                             }
                         }

                     }
                 }
                 $statistics[$k]['data'] = $temp;
                 $statistics[$k]['data']['合计'] = $all;
             }
         }

         $statistics = json_decode(json_encode($statistics), TRUE);
         //echo json_encode($statistics);die();
         $this->assign('statistics', $statistics);
         $this->display();
     }

     private function statistics_1($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankCreditAStNewService = \Common\Service\BankCreditAStNewService::get_instance();
         $BankCreditNewService = \Common\Service\BankCreditNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $credit_news = $BankCreditNewService->get_by_where_all($where);

         if ($credit_news) {
             
             //获取去年同期数据
             $where_past = [];
             $where_past['year'] = $year - 1;
             $where_past['month'] = $month;
             $credit_news_past = $BankCreditNewService->get_by_where_all($where_past);
             $credit_news_past_map = result_to_map($credit_news_past, 'all_name');
             
             //获得年初数据
             $where_year_begin = [];
             $where_year_begin['year'] = $year - 1;
             $where_year_begin['month'] = 12;
             $credit_news_year_begin = $BankCreditNewService->get_by_where_all($where_year_begin);
             $credit_news_year_begin_map = result_to_map($credit_news_year_begin, 'all_name');

             $all_names = result_to_array($credit_news, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($credit_news as $_data) {
                 foreach ($_data as $_k => $_v) {
                     $_arr = explode('|', $_v);
                     if (count($_arr) == 3) {
                         $_data[$_k] = $_arr;
                         if (isset($credit_news_past_map[$_data['all_name']])) {
                             $_v_past = explode('|', $credit_news_past_map[$_data['all_name']][$_k]);
                             if ($_v_past[0]) {
                                 $_data[$_k][3] = fix_2($_data[$_k][0] / $_v_past[0] - 1);
                             } else {
                                 $_data[$_k][3] = 0;//去年记录为0
                             }
                         } else {
                             $_data[$_k][3] = 0;//去年无记录
                         }

                         if (isset($credit_news_year_begin_map[$_data['all_name']])) {
                             $_v_year_begin = explode('|', $credit_news_year_begin_map[$_data['all_name']][$_k]);
                             $_data[$_k][4] = $_v_year_begin[0];//年初余额
                         } else {
                             $_data[$_k][4] = $_data[$_k][0] - $_data[$_k][2];
                         }
                         $_data[$_k][5] = $_data[$_k][0] - $_data[$_k][4];//比年初
                     }
                 }

                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;

                 $fields = ['Deposits','Deposits_A','Deposits_A1','Deposits_A2',
                     'Deposits_B','Deposits_B1','Deposits_B2','Deposits_C','Deposits_C1',
                     'Deposits_C2','Deposits_C3','Deposits_C4','Deposits_D',
                     'Loans','Loans_A','Loans_B','Loans_C','Loans_D',
                     'Loans_D1','Loans_D2','Loans_E','Loans_F','Loans_G',
                     'Loans_H','Loans_I','Loans_I1','Loans_I2'
                 ];
                 $content = [];
                 foreach ($fields as $_field) {
                     $content[$_field] = [$_data[$_field][4], $_data[$_field][0], $_data[$_field][5], $_data[$_field][3]];
                 }
                 $temp['content'] = json_encode($content);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankCreditAStNewService->del_by_month_year($year,$month);
             return $BankCreditAStNewService->add_batch($data);

         }
     }

     private function statistics_2($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankCreditBStNewService = \Common\Service\BankCreditBStNewService::get_instance();
         $BankCreditNewService = \Common\Service\BankCreditNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $credit_news = $BankCreditNewService->get_by_where_all($where);

         if ($credit_news) {
             //获取同比数据
             $where_past = [];
             $where_past['year'] = $year - 1;
             $where_past['month'] = $month;
             $credit_news_past = $BankCreditNewService->get_by_where_all($where_past);
             $credit_news_past_map = result_to_map($credit_news_past, 'all_name');

             //获得年初数据
             $where_year_begin = [];
             $where_year_begin['year'] = $year - 1;
             $where_year_begin['month'] = 12;
             $credit_news_year_begin = $BankCreditNewService->get_by_where_all($where_year_begin);
             $credit_news_year_begin_map = result_to_map($credit_news_year_begin, 'all_name');

             //获得上月数据
             $where_last_month = [];
             if ($month == 1) {
                 $where_last_month['year'] = $year - 1;
                 $where_last_month['month'] = 12;
             } else {
                 $where_last_month['year'] = $year;
                 $where_last_month['month'] = $month - 1;
             }
             $credit_news_last_month = $BankCreditNewService->get_by_where_all($where_last_month);
             $credit_news_last_month_map = result_to_map($credit_news_last_month, 'all_name');

             $all_names = result_to_array($credit_news, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($credit_news as $_data) {
                 foreach ($_data as $_k => $_v) {
                     $_arr = explode('|', $_v);
                     if (count($_arr) == 3) {
                         $_data[$_k] = $_arr;
                         if (isset($credit_news_past_map[$_data['all_name']])) {
                             $_v_past = explode('|', $credit_news_past_map[$_data['all_name']][$_k]);
                             $_data[$_k][3] = $_data[$_k][0] - $_v_past[0];//上月余额
                         } else {
                             $_data[$_k][3] = $_data[$_k][0];
                         }

                         if (isset($credit_news_last_month_map[$_data['all_name']])) {
                             $_v_last_month = explode('|', $credit_news_last_month_map[$_data['all_name']][$_k]);
                             $_data[$_k][4] = $_v_last_month[0];//上月余额
                         } else {
                             $_data[$_k][4] = $_data[$_k][0] - $_data[$_k][1];
                         }


                         if (isset($credit_news_year_begin_map[$_data['all_name']])) {
                             $_v_year_begin = explode('|', $credit_news_year_begin_map[$_data['all_name']][$_k]);
                             $_data[$_k][5] = $_v_year_begin[0];//年初余额
                         } else {
                             $_data[$_k][5] = $_data[$_k][0] - $_data[$_k][2];
                         }

                         $_data[$_k][1] = $_data[$_k][0] - $_data[$_k][4];//比上月
                         $_data[$_k][2] = $_data[$_k][0] - $_data[$_k][5];//比年初
                     }
                 }

                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;

                 $fields = ['Deposits', 'Loans'];
                 $content = [];
                 foreach ($fields as $_field) {
                     $content[$_field] = [$_data[$_field][5] * 10000, $_data[$_field][4] * 10000, $_data[$_field][0] * 10000, $_data[$_field][1] * 10000, $_data[$_field][2] * 10000, $_data[$_field][3]];
                 }
                 $content['Deposits_Loans'] = [get_rate($_data['Deposits'][0], $_data['Loans'][0]), get_rate($_data['Deposits'][2], $_data['Loans'][2])];
                 $temp['content'] = json_encode($content);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankCreditBStNewService->del_by_month_year($year,$month);
             return $BankCreditBStNewService->add_batch($data);

         }
     }

     private function statistics_3($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankBaddebtStNewService = \Common\Service\BankBaddebtStNewService::get_instance();
         $BankBaddebtNewService = \Common\Service\BankBaddebtNewService::get_instance();
         $BankCreditNewService = \Common\Service\BankCreditNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankBaddebtNewService->get_by_where_all($where);

         if ($datas) {

             //获取同比数据
             $where_credit = [];
             $where_credit['year'] = $year;
             $where_credit['month'] = $month;
             $data_credit = $BankCreditNewService->get_by_where_all($where_credit);
             $data_credit_map = result_to_map($data_credit, 'all_name');

             //获取同比数据
             $where_past = [];
             $where_past['year'] = $year - 1;
             $where_past['month'] = $month;
             $data_past = $BankBaddebtNewService->get_by_where_all($where_past);
             $data_past_map = result_to_map($credit_news_past, 'all_name');

             //获得年初数据
             $where_year_begin = [];
             $where_year_begin['year'] = $year - 1;
             $where_year_begin['month'] = 12;
             $data_year_begin = $BankBaddebtNewService->get_by_where_all($where_year_begin);
             $data_year_begin_map = result_to_map($data_year_begin, 'all_name');

             //获得上月数据
             $where_last_month = [];
             if ($month == 1) {
                 $where_last_month['year'] = $year - 1;
                 $where_last_month['month'] = 12;
             } else {
                 $where_last_month['year'] = $year;
                 $where_last_month['month'] = $month - 1;
             }
             $data_last_month = $BankCreditNewService->get_by_where_all($where_last_month);
             $data_last_month_map = result_to_map($data_last_month, 'all_name');

             $all_names = result_to_array($datas, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($datas as $_data) {
                 if (isset($data_credit_map[$_data['all_name']])) {
                     $_data['Loans'] = $data_credit_map[$_data['all_name']]['Loans'] * 10000;
                 } else {
                     $_data['Loans'] = 0;
                 }
                 $_data['Baddebt_A'] =  $_data['Loans'] - $_data['Baddebt_B'] - $_data['Baddebt_C'] - $_data['Baddebt_D'] - $_data['Baddebt_E'];
                 //$_data['Baddebt_A'] = ($_data['Baddebt_A'] >= 0) ? $_data['Baddebt_A'] : 0;
                 if (isset($data_year_begin_map[$_data['all_name']])) {
                     $_data['Baddebt_CDE_year_begin'] = $data_year_begin_map[$_data['all_name']]['Baddebt_CDE'];
                     $_data['Baddebt_Month_Rate_year_begin'] = $data_year_begin_map[$_data['all_name']]['Baddebt_Month_Rate'];
                 } else {
                     $_data['Baddebt_CDE_year_begin'] = 0;
                     $_data['Baddebt_Month_Rate_year_begin'] = 0;
                 }

                 if (isset($data_last_month_map[$_data['all_name']])) {
                     $_data['Baddebt_CDE_last_month'] = $data_last_month_map[$_data['all_name']]['Baddebt_CDE'];
                     $_data['Baddebt_Month_Rate_last_month'] = $data_last_month_map[$_data['all_name']]['Baddebt_Month_Rate'];

                 } else {
                     $_data['Baddebt_CDE_last_month'] = 0;
                     $_data['Baddebt_Month_Rate_last_month'] = 0;
                 }

                 if (isset($data_past_map[$_data['all_name']])) {
                     $_data['Baddebt_CDE_past'] = $data_past_map[$_data['all_name']]['Baddebt_CDE'];
                     $_data['Baddebt_Month_Rate_past'] = $data_past_map[$_data['all_name']]['Baddebt_Month_Rate'];

                 } else {
                     $_data['Baddebt_CDE_past'] = 0;
                     $_data['Baddebt_Month_Rate_past'] = 0;
                 }

                 $_data['Baddebt_CDE_year_begin_modify'] = $_data['Baddebt_CDE'] - $_data['Baddebt_CDE_year_begin'];
                 $_data['Baddebt_CDE_last_month_modify'] = $_data['Baddebt_CDE'] - $_data['Baddebt_CDE_last_month'];
                 $_data['Baddebt_CDE_past_modify'] = $_data['Baddebt_CDE'] - $_data['Baddebt_CDE_past'];

                 $_data['Baddebt_Month_Rate_year_begin_modify'] = $_data['Baddebt_Month_Rate'] - $_data['Baddebt_Month_Rate_year_begin'];
                 $_data['Baddebt_Month_Rate_last_month_modify'] = $_data['Baddebt_Month_Rate'] - $_data['Baddebt_Month_Rate_last_month'];
                 $_data['Baddebt_Month_Rate_past_modify'] = $_data['Baddebt_Month_Rate'] - $_data['Baddebt_Month_Rate_past'];



                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;

                 $temp['content'] = json_encode($_data);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankBaddebtStNewService->del_by_month_year($year,$month);
             return $BankBaddebtStNewService->add_batch($data);

         }
     }


     private function statistics_4($year, $month) {
         //生成统计
         $BankBaddebtDetailStNewService = \Common\Service\BankBaddebtDetailStNewService::get_instance();
         $BankBaddebtDetailNewService = \Common\Service\BankBaddebtDetailNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankBaddebtDetailNewService->get_by_where_all($where);

         if ($datas) {

             $data = [];
             foreach ($datas as $_data) {
                if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                    continue;
                }
                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['enterprise'] = $_data['Enterprise'];
                 $temp['content'] = json_encode($_data);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankBaddebtDetailStNewService->del_by_month_year($year,$month);
             return $BankBaddebtDetailStNewService->add_batch($data);

         }
     }

     private function statistics_5($year, $month) {
         //生成统计
         $BankBaddebtDisposeStNewService = \Common\Service\BankBaddebtDisposeStNewService::get_instance();
         $BankBaddebtDisposeNewService = \Common\Service\BankBaddebtDisposeNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankBaddebtDisposeNewService->get_by_where_all($where);

         if ($datas) {

             $data = [];
             foreach ($datas as $_data) {
                 if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                     continue;
                 }
                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['enterprise'] = $_data['Enterprise'];
                 $temp['content'] = json_encode($_data);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankBaddebtDisposeStNewService->del_by_month_year($year,$month);
             return $BankBaddebtDisposeStNewService->add_batch($data);

         }
     }

     private function statistics_6($year, $month) {
         //生成统计
         $BankFocusDetailStNewService = \Common\Service\BankFocusDetailStNewService::get_instance();
         $BankFocusDetailNewService = \Common\Service\BankFocusDetailNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankFocusDetailNewService->get_by_where_all($where);

         if ($datas) {

             $data = [];
             foreach ($datas as $_data) {
                 if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                     continue;
                 }
                 $temp = [];
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $temp['enterprise'] = $_data['Enterprise'];
                 $temp['content'] = json_encode($_data);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankFocusDetailStNewService->del_by_month_year($year,$month);
             return $BankFocusDetailStNewService->add_batch($data);

         }
     }

     private function statistics_7($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankQuarterlyQuantityAStNewService = \Common\Service\BankQuarterlyQuantityAStNewService::get_instance();
         $BankQuaterlyQuantityANewService = \Common\Service\BankQuaterlyQuantityANewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankQuaterlyQuantityANewService->get_by_where_all($where);

         if ($datas) {
             $all_names = result_to_array($datas, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($datas as $_data) {
                 if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                     continue;
                 }
                 $temp = [];
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $content = [];
                 foreach ($_data as $_k => $_v) {
                     $_arr = explode('|', $_v);
                     if (count($_arr) == 4) {
                         $content['first'][] = $_arr[0];
                         $content['second'][] = $_arr[1];
                         $content['third'][] = $_arr[2];
                     }
                 }

                 $temp['content'] = json_encode($content);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankQuarterlyQuantityAStNewService->del_by_month_year($year,$month);
             return $BankQuarterlyQuantityAStNewService->add_batch($data);

         }
     }


     private function statistics_8($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankQuarterlyQuantityBStNewService = \Common\Service\BankQuarterlyQuantityBStNewService::get_instance();
         $BankQuaterlyQuantityBNewService = \Common\Service\BankQuaterlyQuantityBNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankQuaterlyQuantityBNewService->get_by_where_all($where);

         if ($datas) {
             $all_names = result_to_array($datas, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($datas as $_data) {
                 if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                     continue;
                 }
                 $temp = [];
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $content = [];
                 foreach ($_data as $_k => $_v) {
                     $_arr = explode('|', $_v);
                     if (count($_arr) == 5) {
                         $content['first'][] = $_arr[0];
                         $content['second'][] = $_arr[1];
                         $content['third'][] = $_arr[2];
                         $content['forth'][] = $_arr[3];
                     }
                 }

                 $temp['content'] = json_encode($content);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankQuarterlyQuantityBStNewService->del_by_month_year($year,$month);
             return $BankQuarterlyQuantityBStNewService->add_batch($data);

         }
     }

     private function statistics_9($year, $month) {
         $DepartmentService = \Common\Service\DepartmentService::get_instance();
         //生成统计
         $BankQuarterlyQuantityCStNewService = \Common\Service\BankQuarterlyQuantityCStNewService::get_instance();
         $BankQuaterlyQuantityCNewService = \Common\Service\BankQuaterlyQuantityCNewService::get_instance();
         $where = [];
         $where['year'] = $year;
         $where['month'] = $month;
         $datas = $BankQuaterlyQuantityCNewService->get_by_where_all($where);

         if ($datas) {
             $all_names = result_to_array($datas, 'all_name');
             $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
             $departments_map = result_to_map($departments, 'all_name');
             $data = [];
             foreach ($datas as $_data) {
                 if ($_data['Enterprise'] == '小计' || $_data['Enterprise'] == '合计' || $_data['Enterprise'] == '50万元以下汇总') {
                     continue;
                 }
                 $temp = [];
                 $temp['department_sub_type'] = isset($departments_map[$_data['all_name']]['sub_type']) ? $departments_map[$_data['all_name']]['sub_type'] : 0;
                 $temp['all_name'] = $_data['all_name'];
                 $temp['year'] = $year;
                 $temp['month'] = $month;
                 $content = [];
                 foreach ($_data as $_k => $_v) {
                     $_arr = explode('|', $_v);
                     if (count($_arr) == 4) {
                         $content['first'][] = $_arr[0];
                         $content['second'][] = $_arr[1];
                         $content['third'][] = $_arr[2];
                         $content['forth'][] = $_arr[3];
                     }
                 }

                 $temp['content'] = json_encode($content);
                 $temp['gmt_create'] = time();
                 $data[] = $temp;

             }

             $BankQuarterlyQuantityCStNewService->del_by_month_year($year,$month);
             return $BankQuarterlyQuantityCStNewService->add_batch($data);

         }
     }


     /**
      * 信贷统计
      */
     public function credit_statistics()
     {
         $this->local_service = \Common\Service\BankCreditService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankCreditService';
         $page = I('get.p', 1);
         $where['status'] = 2;
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1, 999999);

         //获取上年末数据
         $where_last_year_end = $where;
         $where_last_year_end['year'] --;
         $where_last_year_end['month'] = 12;
         list($data_last_year_end, $count) = $this->local_service->get_by_where($where_last_year_end, 'id desc', 1, 999999);
         //获取同期数据
         $where_last_year = $where;
         $where_last_year['year'] --;

         list($data_last_year, $count) = $this->local_service->get_by_where($where_last_year, 'id desc', 1, 999999);

         $data = $this->convert_data_credit_statistics($data, $data_last_year_end, $data_last_year);

         $this->assign('list', $data);



         $this->display();
     }

     protected function convert_data_credit_statistics($data1, $data2, $data3) {
         if ($data1) {
             $data2_map = result_to_map($data2, 'all_name');
             $data3_map = result_to_map($data3, 'all_name');

             foreach ($data1 as $key => $value) {
                 if (isset($data2_map[$value['all_name']])) {
                     $data1[$key]['last_year_end'] = $data2_map[$value['all_name']];
                 }

                 if (isset($data3_map[$value['all_name']])) {
                     $data1[$key]['last_year'] = $data3_map[$value['all_name']];
                 }
             }
         }
         return $data1;
     }

     /**
      * 不良贷款统计
      */
     public function baddebt_statistics()
     {
         $this->local_service = \Common\Service\BankBaddebtStService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankCreditService';
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1);

         $data = $this->convert_data_department_types($data);

         $this->assign('list', $data);

         $this->display();
     }


     /**
      * 存贷统计
      */
     public function loan_statistics()
     {
         $this->local_service = \Common\Service\BankCreditService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankCreditService';
         $page = I('get.p', 1);
         $where['status'] = 2;
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1, 999999);

         //获取不良贷款额
         $BankLoanDetailService = \Common\Service\BankLoanDetailService::get_instance();
         $all_names = isset($where['all_name']) ? [$where['all_name']] : [];
         $where['status'] = 2;
         $LoanDetails = $BankLoanDetailService->get_by_month_year_all_names($where['year'], $where['month'], $all_names, false, false, $where);
         $LoanDetails_map = result_to_map($LoanDetails, 'all_name');

         $data = $this->convert_data_loan_statistics($data, $LoanDetails_map);

         $data = $this->convert_data_department_types($data);
         //var_dump($data);die();
         $this->assign('list', $data);

         $this->display();
     }

     protected function convert_data_loan_statistics($data1, $LoanDetails_map) {
         if ($data1) {

             foreach ($data1 as $key => $value) {
                 $data1[$key]['baddbet_loan'] = 0;
                 if (isset($LoanDetails_map[$value['all_name']]) && $LoanDetails_map[$value['all_name']]) {
                     foreach ($LoanDetails_map[$value['all_name']] as $loan) {
                         if ($loan['pattern'] > \Common\Model\FinancialBankLoanDetailModel::TYPE_B) {
                             $data1[$key]['baddbet_loan'] += $loan['Loans'];
                         }

                     }

                 }
             }
         }
         return $data1;
     }



     /**
      * 不良贷款(分乡镇)统计
      */
     public function baddebt_cities_statistics()
     {
         $this->local_service = \Common\Service\BankAreaStService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankAreaStService';
         $page = I('get.p', 1);
         $where['type'] = \Common\Model\FinancialBankAreaStModel::TYPE_A;
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1);

         $data = $this->convert_data_areas($data);

         $this->assign('list', $data);

         $this->display();
     }

     /**
      * 不良贷款(分乡镇)统计
      */
     public function overdue_resolve_statistics()
     {
         $this->local_service = \Common\Service\BankAreaStService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankAreaStService';
         $page = I('get.p', 1);
         $where['type'] = \Common\Model\FinancialBankAreaStModel::TYPE_B;
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1);

         $data = $this->convert_data_areas($data);

         $this->assign('list', $data);

         $this->display();
     }

     /**
      * 不良贷款处置统计
      */
     public function baddebt_dispose_statistics()
     {
         $this->local_service = \Common\Service\BankBaddebtDisposeService::get_instance();
         $this->title = '';
         $this->assign('title', $this->title);
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
         }

         if (!$get['year']) {
             $get['year'] = intval(date('Y'));
         }
         if (!$get['month']) {
             $get['month'] = intval(date('m'));
         }
         $where['year'] = $get['year'];
         $where['month'] = $get['month'];
         $service = '\Common\Service\BankBaddebtDisposeService';
         $page = I('get.p', 1);
         $where['status'] = 2;
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', 1, true);
         //获取不良贷款额
         $BankBaddebtStService = \Common\Service\BankBaddebtStService::get_instance();
         list($sts, $count) = $BankBaddebtStService->get_by_where($where);

         $data = $this->convert_data_baddebt_dispose($data, $sts);
         $data = $this->convert_data_department_types($data);




         $this->assign('list', $data);

         $this->display();
     }

     public function convert_data_baddebt_dispose($data, $sts) {
         $new_data = [];

         if ($data) {
             $data = result_to_complex_map($data, 'all_name');
             if ($sts) {
                 $sts_map = result_to_map($sts, 'all_name');
             }
             foreach ($data as $key => $values) {
                 $temp = [];
                 $temp['all_name'] = $key;
                 if (isset($sts_map[$key])) {
                     $temp['Baddebt_Initial'] = $sts_map[$key]['Baddebt_Initial'];
                     $temp['Baddebt_add'] = $sts_map[$key]['Baddebt_Month'] - $sts_map[$key]['Baddebt_Initial'];
                 } else {
                     $temp['Baddebt_Initial'] = '未知';
                     $temp['Baddebt_add'] = '未知';
                 }
                 $temp['recover'] = $temp['recover_a'] = $temp['recover_b'] = $temp['recover_c'] = $temp['recover_d'] = $temp['recover_e'] = $temp['recover_f'] = $temp['recover_g'] = $temp['recover_h'] = 0;
                 foreach ($values as $value) {
                     $temp['recover'] += $value['Recover'];
                     $temp['remarks'] .= $value['Remarks'] . '。';
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_A) {
                         $temp['recover_a'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_B) {
                         $temp['recover_b'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_C) {
                         $temp['recover_c'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_D) {
                         $temp['recover_d'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_E) {
                         $temp['recover_e'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_F) {
                         $temp['recover_f'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_G) {
                         $temp['recover_g'] += $value['Recover'];
                     }
                     if ($value['Recover_Method'] == \Common\Model\FinancialBankBaddebtDisposeModel::TYPE_H) {
                         $temp['recover_h'] += $value['Recover'];
                     }
                 }
                 $new_data[] = $temp;
             }
         }
         return $new_data;
     }


     public function add_unit(){
         $this->title = '';
         parent::add_unit();

         $this->add();
         $this->display();
     }

    public function index() {
        $this->local_service = \Common\Service\DepartmentService::get_instance();
        $this->local_service_name = 'DepartmentService';
        $where = [];
        /**
        if (I('get.sex')) {
            $where['sex'] = ['EQ', I('get.sex')];
        }

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        */
         if (I('get.all_name')) {
            $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
          $where['type'] = $this->type;
        $page = I('get.p', 1);
        list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $service = '\Common\Service\\'.$this->local_service_name;
        $PageInstance = new \Think\Page($count, $service::$page_size);
        if($total>$service::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }



    public function del() {
        $this->local_service = \Common\Service\DepartmentService::get_instance();
        $this->local_service_name = 'DepartmentService';
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->local_service->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除银行机构单位');
        $this->success('删除成功！');
    }

    public function add() {
        $this->local_service = \Common\Service\DepartmentService::get_instance();
        $this->local_service_name = 'DepartmentService';
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $bank_type_options = $DepartmentService->get_sub_type_options();
        if ($id = I('get.id')) {
            $info = $this->local_service->get_info_by_id($id);
            if ($info) {
                $bank_type_options = $DepartmentService->get_sub_type_options($info['sub_type']);
                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }

        $this->assign('bank_type_options', $bank_type_options);
    }

    public function update() {
        $this->local_service = \Common\Service\DepartmentService::get_instance();
        $this->local_service_name = 'DepartmentService';
        if (IS_POST) {
            $id = I('get.id');
                        $data = I('post.');
                        if ($id) {
                            $ret = $this->local_service->update_by_id($id, $data);
                            if ($ret->success) {
                                action_user_log('修改银行机构单位');
                                $this->success('修改成功！', U('index'));
                            } else {
                                $this->error($ret->message);
                            }
                        } else {
                              $ret = $this->local_service->add_one($data, 1);
                                 if (!$ret->success) {
                                 $this->error($ret->message);
                              }

                            $data['type'] = $this->type;
                            $ret = $this->local_service->add_one($data);
                            if ($ret->success) {
                                action_user_log('添加银行机构单位');
                                $this->success('添加成功！', U('index'));
                            } else {
                                $this->error($ret->message);
                            }
                        }


        }
    }
    public function convert_data(&$data) {

        //根据部门获取多有uid
        $DepartmentUids = D('FinancialDepartmentUid')->where(['did'=>['in', result_to_array($data)]])->select();
        $uids = result_to_array($DepartmentUids, 'uid');
        //$uids = result_to_array($data, 'uid');
        $DepartmentUids_map = result_to_complex_map($DepartmentUids, 'did');
        $User   =   \Common\Service\MemberService::get_instance();
        $users    =   $User->get_by_uids($uids);
        $users_map = result_to_map($users, 'uid');

        $AuthGroup = D('AuthGroup');
        $groups = $AuthGroup->getUsersGroup($uids);

        $groups_map = result_to_map($groups, 'uid');
        // var_dump($DepartmentUids);die();
        foreach ($data as $k=>$v) {
            if (isset($DepartmentUids_map[$v['id']]) && $DepartmentUids_map[$v['id']]) {
                $data[$k]['user'] = [];
                foreach ($DepartmentUids_map[$v['id']] as $_DepartmentUids) {
                    if (isset($users_map[$_DepartmentUids['uid']])){
                        if (isset($groups_map[$_DepartmentUids['uid']])) {
                            $users_map[$_DepartmentUids['uid']]['gid'] = $groups_map[$_DepartmentUids['uid']]['group_id'];
                        }
                        $data[$k]['user'][] = $users_map[$_DepartmentUids['uid']];
                    }
                }
                //$data[$k]['user'] = $users_map[$v['uid']];
            }
            if (isset($sub_type_map[$v['sub_type']])) {
                $data[$k]['sub_type_name'] = $sub_type_map[$v['sub_type']];
            }
        }
    }


    public function get_enterprise_info() {
        $name = I('get.name');
        $EnterpriseService = \Common\Service\EnterpriseService::get_instance();
        $info = $EnterpriseService->get_info_by_name($name);

        if ($info) {
            $info['Jurisdictions'] = str_replace('所', '', $info['Jurisdictions']);
            $info['Jurisdictions'] = str_replace('分局', '', $info['Jurisdictions']);

            $AreaService = \Common\Service\AreaService::get_instance();


            $area = $AreaService->get_like_name($info['Jurisdictions']);

            if ($area) {
                $info['Jurisdictions'] = $area['id'];
            } else {
                $info['Jurisdictions'] = '';
            }
        }

        $this->ajaxReturn($info);

    }

     public function get_enterprise_info_by_name($name) {
         $EnterpriseService = \Common\Service\EnterpriseService::get_instance();
         $info = $EnterpriseService->get_info_by_name($name);

         if ($info) {
             $info['Jurisdictions'] = str_replace('所', '', $info['Jurisdictions']);
             $info['Jurisdictions'] = str_replace('分局', '', $info['Jurisdictions']);

             $AreaService = \Common\Service\AreaService::get_instance();
             $area = $AreaService->get_like_name($info['Jurisdictions']);

             if ($area) {
                 $info['Jurisdictions'] = $area['id'];
             } else {
                 $info['Jurisdictions'] = '';
             }
         }

         return $info;

     }

    public function convert_data_department_types($data) {
        //获取所有相关的公司
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $all_names = result_to_array($data, 'all_name');
        $departments = $DepartmentService->get_by_all_names($all_names, \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank);
        $departments_map = result_to_map($departments, 'all_name');
        $sub_type_map = \Common\Model\FinancialDepartmentModel::$SUB_TYPE_MAP;
        foreach ($data as $key => $value) {

            $data[$key]['sub_type_name'] = isset($sub_type_map[$departments_map[$value['all_name']]['sub_type']]) ? $sub_type_map[$departments_map[$value['all_name']]['sub_type']] : '未分组';
        }

        return result_to_complex_map($data, 'sub_type_name');
    }

    public function convert_data_areas($data) {
        if ($data) {
            $AreaService = \Common\Service\AreaService::get_instance();
            $areas = $AreaService->get_all();
            $areas_map = result_to_map($areas);
            foreach ($data as $key => $_data) {
                $data[$key]['area_name'] = isset($areas_map[$_data['Area']]['name']) ? $areas_map[$_data['Area']]['name'] : '未知';
            }
        }
        return $data;
    }


//    public function submit_verify() {
//        $id = I('get.id');
//        $type = I('get.type');
//
//        switch ($type) {
//            case 'credit':
//                $service = \Common\Service\BankCreditService::get_instance();
//                $info = $service->get_info_by_id($id);
//                if (!$info || !$this->check_is_my_department($info['all_name'])) {
//                    $this->error('没有该信息或者权限不够');
//                }
//                break;
//            case 'baddebt_dispose':
//                $service = \Common\Service\BankBaddebtDisposeService::get_instance();
//                $all_name = I('get.all_name');
//                if (!$this->check_is_my_department($all_name)) {
//                    $this->error('没有该信息或者权限不够');
//                }
//
//                $year = I('get.year');
//                $month = I('get.month');
//                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);
//
//                if (!$ret->success) {
//                    $this->error($ret->message);
//                }
//                //action_user_log('提交状态');
//                $this->success('提交成功~');
//                break;
//            case 'loan_details':
//                $service = \Common\Service\BankLoanDetailService::get_instance();
//                $all_name = I('get.all_name');
//                if (!$this->check_is_my_department($all_name)) {
//                    $this->error('没有该信息或者权限不够');
//                }
//
//                $year = I('get.year');
//                $month = I('get.month');
//                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);
//
//                if (!$ret->success) {
//                    $this->error($ret->message);
//                }
//                //action_user_log('提交状态');
//                $this->success('提交成功~');
//                break;
//            case 'quarterly':
//                $service = \Common\Service\BankQuarterlyService::get_instance();
//                $info = $service->get_info_by_id($id);
//                if (!$info || !$this->check_is_my_department($info['all_name'])) {
//                    $this->error('没有该信息或者权限不够');
//                }
//                break;
//            case 'overdue':
//                $service = \Common\Service\BankOverdueResolveService::get_instance();
//                $all_name = I('get.all_name');
//                if (!$this->check_is_my_department($all_name)) {
//                    $this->error('没有该信息或者权限不够');
//                }
//
//                $year = I('get.year');
//                $month = I('get.month');
//                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);
//
//                if (!$ret->success) {
//                    $this->error($ret->message);
//                }
//                //action_user_log('提交状态');
//                $this->success('提交成功~');
//                break;
//            default:
//                break;
//        }
//
//        if ($service) {
//            $ret = $service->update_by_id($id, ['status' => 1]);
//            if (!$ret->success) {
//                $this->error($ret->message);
//            }
//            //action_user_log('提交状态');
//            $this->success('提交成功~');
//        }
//        $this->error('参数错误');
//    }


     public function approve() {
         $id = I('get.id');
         $type = I('get.type');

         switch ($type) {
             case 'credit':
                 $service = \Common\Service\BankCreditService::get_instance();
                 $info = $service->get_info_by_id($id);
                 if (!$info || !$this->check_is_my_department($info['all_name'])) {
                     $this->error('没有该信息或者权限不够');
                 }
                 break;
             case 'baddebt_dispose':
                 $service = \Common\Service\BankBaddebtDisposeService::get_instance();

                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>2]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');
                 $this->success('通过成功~');
                 break;
             case 'loan_details':
                 $service = \Common\Service\BankLoanDetailService::get_instance();
                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>2]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');

                 $datas = $service->get_by_month_year($year, $month, $all_name);
                 $this->update_loan_st($datas);
                 $this->update_area_a_st($year, $month);
                 $this->success('通过成功~');
                 break;
             case 'quarterly':
                 $service = \Common\Service\BankQuarterlyService::get_instance();
                 $info = $service->get_info_by_id($id);
                 if (!$info || !$this->check_is_my_department($info['all_name'])) {
                     $this->error('没有该信息或者权限不够');
                 }
                 break;
             case 'overdue':
                 $service = \Common\Service\BankOverdueResolveService::get_instance();
                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>2]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');
                 $this->update_area_b_st($year, $month);
                 $this->success('通过成功~');
                 break;
             default:
                 break;
         }
         if ($service) {
             $ret = $service->update_by_id($id, ['status' => 2]);
             if (!$ret->success) {
                 $this->error($ret->message);
             }
             //action_user_log('提交状态');
             $this->success('通过成功~');
         }
         $this->error('参数错误');
     }


     public function reject() {
         $id = I('get.id');
         $type = I('get.type');

         switch ($type) {
             case 'credit':
                 $service = \Common\Service\BankCreditService::get_instance();
                 $info = $service->get_info_by_id($id);
                 if (!$info || !$this->check_is_my_department($info['all_name'])) {
                     $this->error('没有该信息或者权限不够');
                 }
                 break;
             case 'baddebt_dispose':

                 $service = \Common\Service\BankBaddebtDisposeService::get_instance();

                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>0]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');
                 $this->success('退回成功~');
                 break;
             case 'loan_details':
                 $service = \Common\Service\BankLoanDetailService::get_instance();
                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status' => 0]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');
                 $this->success('退回成功~');
                 break;
             case 'quarterly':
                 $service = \Common\Service\BankQuarterlyService::get_instance();
                 $info = $service->get_info_by_id($id);
                 if (!$info || !$this->check_is_my_department($info['all_name'])) {
                     $this->error('没有该信息或者权限不够');
                 }
                 break;
             case 'overdue':
                 $service = \Common\Service\BankOverdueResolveService::get_instance();
                 $all_name = I('get.all_name');
                 if (!$this->check_is_my_department($all_name)) {
                     $this->error('没有该信息或者权限不够');
                 }

                 $year = I('get.year');
                 $month = I('get.month');
                 $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>0]);

                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 //action_user_log('提交状态');
                 $this->success('退回成功~');
                 break;
             default:
                 break;
         }
         if ($service) {
             $ret = $service->update_by_id($id, ['status' => 0]);
             if (!$ret->success) {
                 $this->error($ret->message);
             }
             //action_user_log('提交状态');
             $this->success(' 退回成功~');
         }
         $this->error('参数错误');
     }


     protected function check_is_my_department($all_name) {
         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);


         if (!$departments) {
             $departments = $DepartmentService->get_all_list($this->type);
         }

         $all_names = result_to_array($departments, 'all_name');
         if (in_array($all_name, $all_names)) {
             return true;
         } else {
             return false;
         }

     }

     public function upload_excel() {
         set_time_limit(0);
         /** Include path **/
         set_include_path(APP_PATH . '/Common/Lib/PHPExcel/Classes/');

         /** PHPExcel_IOFactory */
         include 'PHPExcel/IOFactory.php';
         $objPHP = new \PHPExcel_Reader_Excel5();
         $objPHPExcel = $objPHP->load($_FILES['file']['tmp_name']);

         $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//         var_dump($sheetData);die();

         $data = $bad_data = [];
         $type = I('get.type');
         $AreaService = \Common\Service\AreaService::get_instance();
         $key = '';
         $page_html = '';
         if ($type == 'baddebt_dispose') {
             if (count($sheetData[1]) != 7) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<count($sheetData) + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);
                 for ($j=1;$j<count($sheetData[1]) + 1;$j++) {
                     $val = $sheetData[$i][$j-1];
                     if ($j == 3) {
                         //处理街道
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {

                             if (isset($info['Jurisdictions']) && $info['Jurisdictions']) {
                                 $val = $info['Jurisdictions'];

                             } else {
                                 $is_bad_row = true;
                             }
                         }

                     }

                     if ($j == 6 && !$is_bad_row) {
                         //处理收回方式
                         if ($val == '现金收回') {
                             $temp[] = 1;
                             continue;
                         } elseif ($val == '上划') {
                             $temp[] = 2;
                             continue;
                         } elseif ($val == '以资抵债') {
                             $temp[] = 3;
                             continue;
                         } elseif ($val == '重组上调') {
                             $temp[] = 4;
                             continue;
                         } elseif ($val == '资产证券化') {
                             $temp[] = 5;
                             continue;
                         } elseif ($val == '转让') {
                             $temp[] = 6;
                             continue;
                         } elseif ($val == '核销') {
                             $temp[] = 7;
                             continue;
                         } elseif ($val == '其他') {
                             $temp[] = 8;
                             continue;
                         } else {

                         }

                     }


                     $temp[] = $val;
                 }
                 if ($is_bad_row) {
                     $bad_data[] = $temp;
                 } else {
                     $data[] = $temp;
                 }

             }

             if ($bad_data) {
                 $key = uniqid();
                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','收回不良贷款金额','收回不良贷款时间','收回方式（现金收回、上划、以资抵债、重组上调、资产证券化、转让、核销、其他）','备注']);
                 S($key, $bad_data, 120);
             }

         } elseif ($type == 'loan_details') {
             if (count($sheetData[1]) != 21) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<count($sheetData) + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);
                 for ($j=1;$j<count($sheetData[1]) + 1;$j++) {
                     $val = $sheetData[$i][$j-1];
                     if ($j== 2) {
                         //获取企业信息
                         $info = $this->get_enterprise_info_by_name($val);
                     }

                     if ($j == 5) {
                         if (!$val) {

                             if (isset($info['Legal']) && $info['Legal']) {
                                 $val = $info['Legal'];
                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     } elseif ($j == 6) {
                         if (!$val) {
                             if (isset($info['Phone']) && $info['Phone']) {
                                 $val = $info['Phone'];
                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     } elseif ($j == 7) {
                         if (!$val) {
                             if (isset($info['Address']) && $info['Address']) {
                                 $val = $info['Address'];
                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     } elseif ($j == 8) {
                         //处理街道
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {

                             if (isset($info['Jurisdictions']) && $info['Jurisdictions']) {
                                 $val = $info['Jurisdictions'];

                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     } elseif ($j == 9) {
                         if (!$val) {
                             if (isset($info['Industry']) && $info['Industry']) {
                                 $val = $info['Industry'];
                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     } elseif ($j == 10) {
                     }



                     if ($j == 18 && !$is_bad_row) {
                         //处理收回方式
                         if ($val == '正常') {
                             $temp[] = 1;
                             continue;
                         } elseif ($val == '关注') {
                             $temp[] = 2;
                             continue;
                         } elseif ($val == '次级') {
                             $temp[] = 3;
                             continue;
                         } elseif ($val == '可疑') {
                             $temp[] = 4;
                             continue;
                         } elseif ($val == '损失') {
                             $temp[] = 5;
                             continue;
                         } else {

                         }

                     }


                     $temp[] = $val;
                 }
                 if ($is_bad_row) {
                     $bad_data[] = $temp;
                 } else {
                     $data[] = $temp;
                 }

             }
             if ($bad_data) {
                 $key = uniqid();
                 array_unshift($bad_data,["合同号","企业名称","贷款余额（万元）","执行年利率","法定代表人（自动）","联系电话（自动）","注册地址（自动）","所属镇（街道）（自动）","所属行业（自动）","发放日期","到期日期","担保方式（保证、抵押、质押、信用）","信用余额","抵押余额","质押余额","保证余额","其中：保证人","五级形态（填写：正常、关注、次级、可疑损失）","本金逾期天数","是否需要地方政府协调配合（逾期、不良贷款需填写）","备注"]);
                 S($key, $bad_data, 120);
             }


         } elseif ($type == 'overdue_resolve') {
             if (count($sheetData[1]) != 6) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<count($sheetData) + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);
                 for ($j=1;$j<count($sheetData[1]) + 1;$j++) {
                     $val = $sheetData[$i][$j-1];
                     if ($j == 3) {
                         //处理街道
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {

                             if (isset($info['Jurisdictions']) && $info['Jurisdictions']) {
                                 $val = $info['Jurisdictions'];

                             } else {
                                 $is_bad_row = true;
                             }
                         }
                     }


                     $temp[] = $val;
                 }
                 if ($is_bad_row) {
                     $bad_data[] = $temp;
                 } else {
                     $data[] = $temp;
                 }

             }

             if ($bad_data) {
                 $key = uniqid();
                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','逾期贷款金额','化解金额','备注']);
                 S($key, $bad_data, 120);
             }

         } elseif ($type == 'baddebt_detail_new') {
             //var_dump($sheetData);die();
             if (count($sheetData[4]) != 14) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=5;$i<count($sheetData) + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);

                 if (!$sheetData[$i][1] || '填报人:' == $sheetData[$i][1] ) {
                     break;
                 }

                 $temp['Enterprise'] = (string)$sheetData[$i][2];
                 $temp['Principal'] = (string)$sheetData[$i][3];
                 $temp['Address'] = (string)$sheetData[$i][4];
                 $temp['Loans'] = (string)$sheetData[$i][5];
                 $temp['Loans_Type'] = (string)$sheetData[$i][6];
                 $temp['Startdate'] = strtotime($sheetData[$i][7]);
                 $temp['Enddate'] = strtotime($sheetData[$i][8]);
                 $temp['Industry'] = (string)$sheetData[$i][9];
                 $temp['Reason'] = (string)$sheetData[$i][10];
                 $temp['Handle'] = (string)$sheetData[$i][11];
                 $temp['Info'] = (string)$sheetData[$i][12];
                 $temp['Plan'] = (string)$sheetData[$i][13];
                 $temp['Recommend'] = (string)$sheetData[$i]['Recommend'];

                 $data[] = $temp;


             }

//             if ($bad_data) {
//                 $key = uniqid();
//                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','逾期贷款金额','化解金额','备注']);
//                 S($key, $bad_data, 120);
//             }



         } elseif ($type == 'baddebt_dispose_new') {
            // print_r($sheetData);die();
             if (count($sheetData[4]) != 13) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             $end = 0;
             for($i=5;$i<count($sheetData) + 1;$i++) {
                 if ($end == 1) {
                     break;
                 }
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);

                 if (!$sheetData[$i][0]) {
                    continue;
                 }

                 if (strpos($sheetData[$i][0],'填表人') !== false) {
                     break;
                 }

                 $temp['Enterprise'] = (string)$sheetData[$i][0];
                 $temp['Loans'] = (string)$sheetData[$i][1];
                 $temp['Recover'] = (string)$sheetData[$i][2];
                 $temp['Recover_Ot_1'] = (string)$sheetData[$i][3];
                 $temp['Recover_Ot_2'] = (string)$sheetData[$i][4];
                 $temp['Recover_Ot_3'] = (string)$sheetData[$i][5];
                 $temp['Recover_Ot_4'] = (string)$sheetData[$i][6];
                 $temp['Recover_Ot_5'] = (string)$sheetData[$i][7];
                 $temp['Recover_Ot_6'] = (string)$sheetData[$i][8];
                 $temp['Recover_Ot'] = (string)$sheetData[$i][9];
                 $temp['Recover_Ot_other_name'] = (string)$sheetData[$i][10];
                 $temp['Recover_Ot_other'] = (string)$sheetData[$i][11];

                 $data[] = $temp;
             }

//             if ($bad_data) {
//                 $key = uniqid();
//                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','逾期贷款金额','化解金额','备注']);
//                 S($key, $bad_data, 120);
//             }



         } elseif ($type == 'focus_detail_new') {

             if (count($sheetData[4]) != 12) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=5;$i<count($sheetData) + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 $sheetData[$i] = array_values($sheetData[$i]);

                 if (strpos($sheetData[$i][0],'填报人') !== false) {
                     break;
                 }
                 if (!$sheetData[$i][0]) {
                     continue;
                 }

                 $temp['Enterprise'] = (string)$sheetData[$i][0];
                 $temp['Loans'] = (string)$sheetData[$i][1];
                 $temp['Overdue_Loans'] = (string)$sheetData[$i][2];
                 $temp['Startdate'] = strtotime($sheetData[$i][3]);
                 $temp['Enddate'] = strtotime($sheetData[$i][4]);
                 $temp['Industry'] = (string)$sheetData[$i][5];
                 $temp['Scale'] = (string)$sheetData[$i][6];
                 $temp['Principal'] = (string)$sheetData[$i][7];
                 $temp['Address'] = (string)$sheetData[$i][8];
                 $temp['Phone'] = (string)$sheetData[$i][9];
                 $temp['Area'] = (string)$sheetData[$i][10];
                 $temp['Remark'] = (string)$sheetData[$i][11];

                 $data[] = $temp;

             }



         }


         unlink($_FILES['file']['tmp_name']);

         if ($data) {
             $good_key = uniqid();
             S($good_key, $data, 600);//缓存好的数据
             $this->ajaxReturn(['status'=>true, 'key'=>$key, 'good_key' => $good_key, 'data' => $data]);
         } else {
             $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
         }

     }

     public function get_excel(){
         $key = I('key');
         $bad_data = S($key);
         exportexcel($bad_data,'退回数据', '退回数据');
     }
        //废弃
     public function log_export_excel(){
         $type = I('type');
         $all_name = I('get.all_name');
         if ($type == 'loan_details') {
             $BankLoanDetailService = \Common\Service\BankLoanDetailService::get_instance();
             $data = $BankLoanDetailService->get_by_month_year_all_names(intval(date('Y')), intval(date('m')), $all_name);
             $this->convert_data_loan_details_submit_log($data);
             $excel_data = [];
             $excel_data[] = ["id","公司名称","填报月","填表人","合同号:","企业名称:","贷款余额（万元）:","执行年利率:","法定代表人（自动）:","联系电话（自动）:","注册地址（自动）:","所属镇（街道）（自动）:","所属行业（自动）:","发放日期:","到期日期:","担保方式（保证、抵押、质押、信用）:","信用余额:","抵押余额:","质押余额:","保证余额:","其中：保证人:","五级形态（填写：正常、关注、次级、可疑损失）:","本金逾期天数:","是否需要地方政府协调配合（逾期、不良贷款需填写）:","备注:","提交时间","ip","状态"];
             foreach ($data as $value) {
                 $temp = [];
                 $temp[] = $value['id'];
                 $temp[] = $value['all_name'];
                 $temp[] = $value['year'] . '年' . $value['month'].'月';
                 $temp[] = $value['filler_man'];
                 $temp[] = $value['Contract'];
                 $temp[] = $value['Enterprise'];
                 $temp[] = $value['Loans'];
                 $temp[] = $value['Interest'];
                 $temp[] = $value['Principal'];
                 $temp[] = $value['Phone'];
                 $temp[] = $value['Address'];
                 $temp[] = $value['Area'];
                 $temp[] = $value['Industry'];
                 $temp[] = $value['Startdate|time_to_date'];
                 $temp[] = time_to_date($value['Enddate']);
                 $temp[] = $value['Guarantee'];
                 $temp[] = $value['Over_Credit'];
                 $temp[] = $value['Over_Mortgage'];
                 $temp[] = $value['Over_Pledge'];
                 $temp[] = $value['Over_Margin'];
                 $temp[] = $value['Guarantor'];
                 $temp[] = $value['pattern'];
                 $temp[] = $value['OverdueDays'];
                 $temp[] = $value['Coordination'];
                 $temp[] = $value['Remarks'];
                 $temp[] = date_time($value['gmt_create']);
                 $temp[] = $value['ip'];
                 $temp[] = ($value['status'] == 2)?'已通过':'';
                 $excel_data[] = $temp;
             }

         } elseif ($type == 'overdue_resolve') {
             $Service = \Common\Service\BankOverdueResolveService::get_instance();
             $data = $Service->get_by_month_year_all_names(intval(date('Y')), intval(date('m')));
             $this->convert_data_overdue_resolve_submit_log($data);
             $excel_data = [];
             $excel_data[] = ["id","公司名称","填报月","填表人","企业名称:","法人代表或实际控制人:","企业所属乡镇（街道）:","逾期金额:","化解金额:","备注:","提交时间","ip","状态"];
             foreach ($data as $value) {
                 $temp = [];
                 $temp[] = $value['id'];
                 $temp[] = $value['all_name'];
                 $temp[] = $value['year'] . '年' . $value['month'].'月';
                 $temp[] = $value['filler_man'];
                 $temp[] = $value['Enterprise'];
                 $temp[] = $value['Principal'];
                 $temp[] = $value['area_name'];
                 $temp[] = $value['Overdue'];
                 $temp[] = $value['Resolve'];
                 $temp[] = $value['Remarks'];
                 $temp[] = date_time($value['gmt_create']);
                 $temp[] = $value['ip'];
                 $temp[] = ($value['status'] == 2)?'已通过':'';
                 $excel_data[] = $temp;
             }
         } elseif ($type == 'baddebt_dispose') {
             $Service = \Common\Service\BankBaddebtDisposeService::get_instance();
             $data = $Service->get_by_month_year_all_names(intval(date('Y')), intval(date('m')));
             $this->convert_data_baddebt_dispose_submit_log($data);
             $excel_data = [];
             $excel_data[] = ["id","公司名称","填报月","填表人","企业名称:","法人代表或实际控制人:","企业所属乡镇（街道）:","收回不良贷款金额","收回不良贷款时间:","收回方式","备注:","提交时间","ip","状态"];
             foreach ($data as $value) {
                 $temp = [];
                 $temp[] = $value['id'];
                 $temp[] = $value['all_name'];
                 $temp[] = $value['year'] . '年' . $value['month'].'月';
                 $temp[] = $value['filler_man'];
                 $temp[] = $value['Enterprise'];
                 $temp[] = $value['Principal'];
                 $temp[] = $value['area_name'];
                 $temp[] = $value['Recover'];
                 $temp[] = $value['recover_time'];
                 $temp[] = $value['recover_method'];
                 $temp[] = $value['Remarks'];
                 $temp[] = date_time($value['gmt_create']);
                 $temp[] = $value['ip'];
                 $temp[] = ($value['status'] == 2)?'已通过':'';
                 $excel_data[] = $temp;
             }
         }

         exportexcel($excel_data,'明细记录', '明细记录');
     }


     public function export_excel() {
         set_time_limit(0);
         /** Include path **/
         set_include_path(APP_PATH . '/Common/Lib/PHPExcel/Classes/');

         /** PHPExcel_IOFactory */
         include 'PHPExcel.php';
         include 'PHPExcel/IOFactory.php';
         include 'PHPExcel/Style/Alignment.php';
         $PHPExcel = new \PHPExcel();
         $PHPExcel->getProperties()->setCreator("cixijinrongban")
             ->setLastModifiedBy("cixijinrongban")
             ->setTitle("慈溪金融办")
             ->setSubject("慈溪金融办")
             ->setDescription("慈溪金融办报表")
             ->setKeywords("金融办报表")
             ->setCategory("金融办报表");
         $title = '报表';
         $year = I('year') ? I('year') : intval(date('Y'));
         $month = I('month') ? I('month') : intval(date('m'));
         $statistics = [
             ['data'=>null,'name'=>'慈溪市金融机构本外币信贷收支情况表(表1)'],
             ['data'=>null,'name'=>'慈溪市金融机构本外币存贷情况表(表2)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良贷款情况表(表3)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良贷款50万(含以上)明细表(表4)'],
             ['data'=>null,'name'=>'慈溪市金融机构不良资产清收情况表(表5)'],
             ['data'=>null,'name'=>'慈溪市金融机构关注类贷款明细表(表6)'],
             ['data'=>null,'name'=>'慈溪市银行贷款利率执行水平监测表(表7)'],
             ['data'=>null,'name'=>'企业贷款利率执行水平监测表(表8)'],
             ['data'=>null,'name'=>'资产质量相关情况调查表(表9)']

         ];
         switch (I('type')) {
             case 1:
                 $title = $statistics[0]['name'];
                 $Service = \Common\Service\BankCreditAStNewService::get_instance();
                 $statistics[0]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')
                     ->setCellValue('C2', '')
                     ->setCellValue('D2', '一、各项存款')
                     ->setCellValue('E2', '1、对公存款')
                     ->setCellValue('F2', '(1)活期存款')
                     ->setCellValue('G2', '(2)定期存款')
                     ->setCellValue('H2', '2、储蓄存款')
                     ->setCellValue('I2', '(1)活期储蓄')
                     ->setCellValue('J2', '(2)定期储蓄')
                     ->setCellValue('K2', '3、保证金存款')
                     ->setCellValue('L2', '(1)开证保证金')
                     ->setCellValue('M2', '(2)签发银票保证金')
                     ->setCellValue('N2', '(3)商票保贴保证金')
                     ->setCellValue('O2', '(4)开立保函保证金')
                     ->setCellValue('P2', '4、其他存款')
                     ->setCellValue('Q2', '二、各项贷款')
                     ->setCellValue('R2', '1、小企业贷款')
                     ->setCellValue('S2', '2、担保公司担保贷款')
                     ->setCellValue('T2', '3、政府平台公司贷款')
                     ->setCellValue('U2', '4、涉农贷款')
                     ->setCellValue('V2', '(1)农业贷款')
                     ->setCellValue('W2', '(2)农户贷款')
                     ->setCellValue('X2', '5、固定资产贷款')
                     ->setCellValue('Y2', '6、房地产开发贷款')
                     ->setCellValue('Z2', '7、个人住房贷款')
                     ->setCellValue('AA2', '8、个人经营性贷款')
                     ->setCellValue('AB2', '9、票据贴现')
                     ->setCellValue('AC2', '(1)银票贴现')
                     ->setCellValue('AD2', '(2)商票贴现');

                // echo json_encode($statistics);die();
                 if ($statistics[0]['data']) {
                     $start = 3;
                     foreach ($statistics[0]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num * 4 - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$unit_start.':B'.($unit_start+3));


                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, '上年末余额');
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($unit_start+1), '本期余额');
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($unit_start+2), '比上年末');
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($unit_start+3), '同比增长%');

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['Deposits'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['Deposits_A'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['Deposits_A1'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['Deposits_A2'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['Deposits_B'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['Deposits_B1'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['Deposits_B2'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $unit['content']['Deposits_C'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $unit['content']['Deposits_C1'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $unit['content']['Deposits_C2'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $unit['content']['Deposits_C3'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $unit['content']['Deposits_C4'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $unit['content']['Deposits_D'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$unit_start, $unit['content']['Loans'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$unit_start, $unit['content']['Loans_A'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.$unit_start, $unit['content']['Loans_B'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$unit_start, $unit['content']['Loans_C'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.$unit_start, $unit['content']['Loans_D'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.$unit_start, $unit['content']['Loans_D1'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.$unit_start, $unit['content']['Loans_D2'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.$unit_start, $unit['content']['Loans_E'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.$unit_start, $unit['content']['Loans_F'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.$unit_start, $unit['content']['Loans_G'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.$unit_start, $unit['content']['Loans_H'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.$unit_start, $unit['content']['Loans_I'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.$unit_start, $unit['content']['Loans_I1'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.$unit_start, $unit['content']['Loans_I2'][0]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($unit_start+1), $unit['content']['Deposits'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($unit_start+1), $unit['content']['Deposits_A'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($unit_start+1), $unit['content']['Deposits_A1'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($unit_start+1), $unit['content']['Deposits_A2'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($unit_start+1), $unit['content']['Deposits_B'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($unit_start+1), $unit['content']['Deposits_B1'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($unit_start+1), $unit['content']['Deposits_B2'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($unit_start+1), $unit['content']['Deposits_C'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($unit_start+1), $unit['content']['Deposits_C1'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($unit_start+1), $unit['content']['Deposits_C2'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($unit_start+1), $unit['content']['Deposits_C3'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($unit_start+1), $unit['content']['Deposits_C4'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($unit_start+1), $unit['content']['Deposits_D'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($unit_start+1), $unit['content']['Loans'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($unit_start+1), $unit['content']['Loans_A'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($unit_start+1), $unit['content']['Loans_B'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($unit_start+1), $unit['content']['Loans_C'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($unit_start+1), $unit['content']['Loans_D'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($unit_start+1), $unit['content']['Loans_D1'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($unit_start+1), $unit['content']['Loans_D2'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($unit_start+1), $unit['content']['Loans_E'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($unit_start+1), $unit['content']['Loans_F'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($unit_start+1), $unit['content']['Loans_G'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($unit_start+1), $unit['content']['Loans_H'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($unit_start+1), $unit['content']['Loans_I'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($unit_start+1), $unit['content']['Loans_I1'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($unit_start+1), $unit['content']['Loans_I2'][1]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($unit_start+2), $unit['content']['Deposits'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($unit_start+2), $unit['content']['Deposits_A'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($unit_start+2), $unit['content']['Deposits_A1'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($unit_start+2), $unit['content']['Deposits_A2'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($unit_start+2), $unit['content']['Deposits_B'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($unit_start+2), $unit['content']['Deposits_B1'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($unit_start+2), $unit['content']['Deposits_B2'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($unit_start+2), $unit['content']['Deposits_C'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($unit_start+2), $unit['content']['Deposits_C1'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($unit_start+2), $unit['content']['Deposits_C2'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($unit_start+2), $unit['content']['Deposits_C3'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($unit_start+2), $unit['content']['Deposits_C4'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($unit_start+2), $unit['content']['Deposits_D'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($unit_start+2), $unit['content']['Loans'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($unit_start+2), $unit['content']['Loans_A'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($unit_start+2), $unit['content']['Loans_B'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($unit_start+2), $unit['content']['Loans_C'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($unit_start+2), $unit['content']['Loans_D'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($unit_start+2), $unit['content']['Loans_D1'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($unit_start+2), $unit['content']['Loans_D2'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($unit_start+2), $unit['content']['Loans_E'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($unit_start+2), $unit['content']['Loans_F'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($unit_start+2), $unit['content']['Loans_G'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($unit_start+2), $unit['content']['Loans_H'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($unit_start+2), $unit['content']['Loans_I'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($unit_start+2), $unit['content']['Loans_I1'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($unit_start+2), $unit['content']['Loans_I2'][2]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($unit_start+3), $unit['content']['Deposits'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($unit_start+3), $unit['content']['Deposits_A'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($unit_start+3), $unit['content']['Deposits_A1'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($unit_start+3), $unit['content']['Deposits_A2'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($unit_start+3), $unit['content']['Deposits_B'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($unit_start+3), $unit['content']['Deposits_B1'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($unit_start+3), $unit['content']['Deposits_B2'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($unit_start+3), $unit['content']['Deposits_C'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($unit_start+3), $unit['content']['Deposits_C1'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($unit_start+3), $unit['content']['Deposits_C2'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($unit_start+3), $unit['content']['Deposits_C3'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($unit_start+3), $unit['content']['Deposits_C4'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($unit_start+3), $unit['content']['Deposits_D'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($unit_start+3), $unit['content']['Loans'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($unit_start+3), $unit['content']['Loans_A'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($unit_start+3), $unit['content']['Loans_B'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($unit_start+3), $unit['content']['Loans_C'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($unit_start+3), $unit['content']['Loans_D'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($unit_start+3), $unit['content']['Loans_D1'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($unit_start+3), $unit['content']['Loans_D2'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($unit_start+3), $unit['content']['Loans_E'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($unit_start+3), $unit['content']['Loans_F'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($unit_start+3), $unit['content']['Loans_G'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($unit_start+3), $unit['content']['Loans_H'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($unit_start+3), $unit['content']['Loans_I'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($unit_start+3), $unit['content']['Loans_I1'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($unit_start+3), $unit['content']['Loans_I2'][3]);

                                     $unit_start+= 4;
                                 }
                             }

                             $start = $start + $num * 4;
                         } else {
                            // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.($start+3));
                             $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                 [
                                     'alignment' => [
                                         'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                     ]
                                 ]
                             );

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');
                             $PHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$start.':B'.($start+3));

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$start, '上年末余额');
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($start+1), '本期余额');
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($start+2), '比上年末');
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.($start+3), '同比增长%');

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$start, $statistics[0]['data'][$key]['Deposits'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$start, $statistics[0]['data'][$key]['Deposits_A'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$start, $statistics[0]['data'][$key]['Deposits_A1'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$start, $statistics[0]['data'][$key]['Deposits_A2'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$start, $statistics[0]['data'][$key]['Deposits_B'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$start, $statistics[0]['data'][$key]['Deposits_B1'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$start, $statistics[0]['data'][$key]['Deposits_B2'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$start, $statistics[0]['data'][$key]['Deposits_C'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$start, $statistics[0]['data'][$key]['Deposits_C1'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$start, $statistics[0]['data'][$key]['Deposits_C2'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$start, $statistics[0]['data'][$key]['Deposits_C3'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$start, $statistics[0]['data'][$key]['Deposits_C4'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$start, $statistics[0]['data'][$key]['Deposits_D'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$start, $statistics[0]['data'][$key]['Loans'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$start, $statistics[0]['data'][$key]['Loans_A'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.$start, $statistics[0]['data'][$key]['Loans_B'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$start, $statistics[0]['data'][$key]['Loans_C'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.$start, $statistics[0]['data'][$key]['Loans_D'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.$start, $statistics[0]['data'][$key]['Loans_D1'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.$start, $statistics[0]['data'][$key]['Loans_D2'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.$start, $statistics[0]['data'][$key]['Loans_E'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.$start, $statistics[0]['data'][$key]['Loans_F'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.$start, $statistics[0]['data'][$key]['Loans_G'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.$start, $statistics[0]['data'][$key]['Loans_H'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.$start, $statistics[0]['data'][$key]['Loans_I'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.$start, $statistics[0]['data'][$key]['Loans_I1'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.$start, $statistics[0]['data'][$key]['Loans_I2'][0]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($start+1), $statistics[0]['data'][$key]['Deposits'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($start+1), $statistics[0]['data'][$key]['Deposits_A'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($start+1), $statistics[0]['data'][$key]['Deposits_A1'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($start+1), $statistics[0]['data'][$key]['Deposits_A2'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($start+1), $statistics[0]['data'][$key]['Deposits_B'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($start+1), $statistics[0]['data'][$key]['Deposits_B1'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($start+1), $statistics[0]['data'][$key]['Deposits_B2'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($start+1), $statistics[0]['data'][$key]['Deposits_C'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($start+1), $statistics[0]['data'][$key]['Deposits_C1'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($start+1), $statistics[0]['data'][$key]['Deposits_C2'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($start+1), $statistics[0]['data'][$key]['Deposits_C3'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($start+1), $statistics[0]['data'][$key]['Deposits_C4'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($start+1), $statistics[0]['data'][$key]['Deposits_D'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($start+1), $statistics[0]['data'][$key]['Loans'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($start+1), $statistics[0]['data'][$key]['Loans_A'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($start+1), $statistics[0]['data'][$key]['Loans_B'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($start+1), $statistics[0]['data'][$key]['Loans_C'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($start+1), $statistics[0]['data'][$key]['Loans_D'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($start+1), $statistics[0]['data'][$key]['Loans_D1'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($start+1), $statistics[0]['data'][$key]['Loans_D2'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($start+1), $statistics[0]['data'][$key]['Loans_E'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($start+1), $statistics[0]['data'][$key]['Loans_F'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($start+1), $statistics[0]['data'][$key]['Loans_G'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($start+1), $statistics[0]['data'][$key]['Loans_H'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($start+1), $statistics[0]['data'][$key]['Loans_I'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($start+1), $statistics[0]['data'][$key]['Loans_I1'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($start+1), $statistics[0]['data'][$key]['Loans_I2'][1]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($start+2), $statistics[0]['data'][$key]['Deposits'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($start+2), $statistics[0]['data'][$key]['Deposits_A'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($start+2), $statistics[0]['data'][$key]['Deposits_A1'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($start+2), $statistics[0]['data'][$key]['Deposits_A2'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($start+2), $statistics[0]['data'][$key]['Deposits_B'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($start+2), $statistics[0]['data'][$key]['Deposits_B1'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($start+2), $statistics[0]['data'][$key]['Deposits_B2'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($start+2), $statistics[0]['data'][$key]['Deposits_C'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($start+2), $statistics[0]['data'][$key]['Deposits_C1'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($start+2), $statistics[0]['data'][$key]['Deposits_C2'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($start+2), $statistics[0]['data'][$key]['Deposits_C3'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($start+2), $statistics[0]['data'][$key]['Deposits_C4'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($start+2), $statistics[0]['data'][$key]['Deposits_D'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($start+2), $statistics[0]['data'][$key]['Loans'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($start+2), $statistics[0]['data'][$key]['Loans_A'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($start+2), $statistics[0]['data'][$key]['Loans_B'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($start+2), $statistics[0]['data'][$key]['Loans_C'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($start+2), $statistics[0]['data'][$key]['Loans_D'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($start+2), $statistics[0]['data'][$key]['Loans_D1'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($start+2), $statistics[0]['data'][$key]['Loans_D2'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($start+2), $statistics[0]['data'][$key]['Loans_E'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($start+2), $statistics[0]['data'][$key]['Loans_F'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($start+2), $statistics[0]['data'][$key]['Loans_G'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($start+2), $statistics[0]['data'][$key]['Loans_H'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($start+2), $statistics[0]['data'][$key]['Loans_I'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($start+2), $statistics[0]['data'][$key]['Loans_I1'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($start+2), $statistics[0]['data'][$key]['Loans_I2'][2]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.($start+3), $statistics[0]['data'][$key]['Deposits'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.($start+3), $statistics[0]['data'][$key]['Deposits_A'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.($start+3), $statistics[0]['data'][$key]['Deposits_A1'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.($start+3), $statistics[0]['data'][$key]['Deposits_A2'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($start+3), $statistics[0]['data'][$key]['Deposits_B'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.($start+3), $statistics[0]['data'][$key]['Deposits_B1'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.($start+3), $statistics[0]['data'][$key]['Deposits_B2'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.($start+3), $statistics[0]['data'][$key]['Deposits_C'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.($start+3), $statistics[0]['data'][$key]['Deposits_C1'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.($start+3), $statistics[0]['data'][$key]['Deposits_C2'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.($start+3), $statistics[0]['data'][$key]['Deposits_C3'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.($start+3), $statistics[0]['data'][$key]['Deposits_C4'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.($start+3), $statistics[0]['data'][$key]['Deposits_D'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.($start+3), $statistics[0]['data'][$key]['Loans'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.($start+3), $statistics[0]['data'][$key]['Loans_A'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.($start+3), $statistics[0]['data'][$key]['Loans_B'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.($start+3), $statistics[0]['data'][$key]['Loans_C'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.($start+3), $statistics[0]['data'][$key]['Loans_D'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.($start+3), $statistics[0]['data'][$key]['Loans_D1'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.($start+3), $statistics[0]['data'][$key]['Loans_D2'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.($start+3), $statistics[0]['data'][$key]['Loans_E'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($start+3), $statistics[0]['data'][$key]['Loans_F'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($start+3), $statistics[0]['data'][$key]['Loans_G'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.($start+3), $statistics[0]['data'][$key]['Loans_H'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.($start+3), $statistics[0]['data'][$key]['Loans_I'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.($start+3), $statistics[0]['data'][$key]['Loans_I1'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.($start+3), $statistics[0]['data'][$key]['Loans_I2'][3]);



                         }


                     }


                 }


                 $PHPExcel->getActiveSheet()->mergeCells('A1:AD1');
                 $PHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 2:
                 $title = $statistics[1]['name'];
                 $Service = \Common\Service\BankCreditBStNewService::get_instance();
                 $statistics[1]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')
                     ->setCellValue('C2', '各项存款')
                     ->setCellValue('I2', '各项贷款')
                     ->setCellValue('O2', '本月存贷比')
                     ->setCellValue('C3', '年初余额')
                     ->setCellValue('D3', '上月余额')
                     ->setCellValue('E3', '月末余额')
                     ->setCellValue('F3', '比上月')
                     ->setCellValue('G3', '比年初')
                     ->setCellValue('H3', '同比')
                     ->setCellValue('I3', '年初余额')
                     ->setCellValue('J3', '上月余额')
                     ->setCellValue('K3', '月末余额')
                     ->setCellValue('L3', '比上月')
                     ->setCellValue('M3', '比年初')
                     ->setCellValue('N3', '同比')
                     ->setCellValue('O3', '余额比%')
                     ->setCellValue('P3', '增量比%');

                 // echo json_encode($statistics);die();
                 if ($statistics[1]['data']) {
                     $start = 4;
                     foreach ($statistics[1]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num  - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Deposits'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['Deposits'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['Deposits'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['Deposits'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['Deposits'][4]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['Deposits'][5]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['Loans'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['Loans'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $unit['content']['Loans'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $unit['content']['Loans'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $unit['content']['Loans'][4]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $unit['content']['Loans'][5]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $unit['content']['Deposits_Loans'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $unit['content']['Deposits_Loans'][1]);
                                     $unit_start++;
                                 }
                             }

                             $start = $start + $num ;
                         } else {
                             // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$start,  $statistics[1]['data'][$key]['Deposits'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$start,  $statistics[1]['data'][$key]['Deposits'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$start,  $statistics[1]['data'][$key]['Deposits'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$start,  $statistics[1]['data'][$key]['Deposits'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$start,  $statistics[1]['data'][$key]['Deposits'][4]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$start,  $statistics[1]['data'][$key]['Deposits'][5]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$start,  $statistics[1]['data'][$key]['Loans'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$start,  $statistics[1]['data'][$key]['Loans'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$start,  $statistics[1]['data'][$key]['Loans'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$start,  $statistics[1]['data'][$key]['Loans'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$start,  $statistics[1]['data'][$key]['Loans'][4]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$start,  $statistics[1]['data'][$key]['Loans'][5]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$start,  $statistics[1]['data'][$key]['Deposits_Loans'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$start,  $statistics[1]['data'][$key]['Deposits_Loans'][1]);

                         }


                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:P1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:H2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I2:N2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('O2:P2');

                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 3:
                 $title = $statistics[2]['name'];
                 $Service = \Common\Service\BankBaddebtStNewService::get_instance();
                 $statistics[2]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')
                     ->setCellValue('C2', '贷款余额')
                     ->setCellValue('D2', '其中:五级分类')
                     ->setCellValue('D3', '正常')
                     ->setCellValue('E3', '关注')
                     ->setCellValue('F3', '次级')
                     ->setCellValue('G3', '可疑')
                     ->setCellValue('H3', '损失')
                     ->setCellValue('I2', '年初后三类不良贷款额')
                     ->setCellValue('J2', '本月末后三类不良贷款额')
                     ->setCellValue('K2', '比年初')
                     ->setCellValue('L2', '比上月')
                     ->setCellValue('M2', '比去年同期')
                     ->setCellValue('N2', '年初不良贷款率')
                     ->setCellValue('O2', '本月末不良贷款率')
                     ->setCellValue('P2', '比年初%')
                     ->setCellValue('Q2', '比上月%')
                     ->setCellValue('R2', '比去年同期%');

                 // echo json_encode($statistics);die();
                 if ($statistics[2]['data']) {
                     $start = 4;
                     foreach ($statistics[2]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num  - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Loans']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['Baddebt_A']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['Baddebt_B']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['Baddebt_C']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['Baddebt_D']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['Baddebt_E']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['Baddebt_CDE_year_begin']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['Baddebt_CDE']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $unit['content']['Baddebt_CDE_year_begin_modify']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $unit['content']['Baddebt_CDE_last_month_modify']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $unit['content']['Baddebt_CDE_past_modify']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $unit['content']['Baddebt_Month_Rate_year_begin']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $unit['content']['Baddebt_Month_Rate']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $unit['content']['Baddebt_Month_Rate_year_begin_modify']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$unit_start, $unit['content']['Baddebt_Month_Rate_last_month_modify']);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$unit_start, $unit['content']['Baddebt_Month_Rate_past_modify']);

                                     $unit_start++;
                                 }
                             }

                             $start = $start + $num ;
                         } else {
                             // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $statistics[2]['data'][$key]['Loans']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $statistics[2]['data'][$key]['Baddebt_A']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $statistics[2]['data'][$key]['Baddebt_B']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $statistics[2]['data'][$key]['Baddebt_C']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $statistics[2]['data'][$key]['Baddebt_D']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $statistics[2]['data'][$key]['Baddebt_E']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $statistics[2]['data'][$key]['Baddebt_CDE_year_begin']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $statistics[2]['data'][$key]['Baddebt_CDE']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $statistics[2]['data'][$key]['Baddebt_CDE_year_begin_modify']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $statistics[2]['data'][$key]['Baddebt_CDE_last_month_modify']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $statistics[2]['data'][$key]['Baddebt_CDE_past_modify']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $statistics[2]['data'][$key]['Baddebt_Month_Rate_year_begin']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $statistics[2]['data'][$key]['Baddebt_Month_Rate']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $statistics[2]['data'][$key]['Baddebt_Month_Rate_year_begin_modify']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$unit_start, $statistics[2]['data'][$key]['Baddebt_Month_Rate_last_month_modify']);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$unit_start, $statistics[2]['data'][$key]['Baddebt_Month_Rate_past_modify']);

                         }


                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:R1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:C3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('D2:H2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I2:I3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('J2:J3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('K2:K3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('L2:L3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('M2:M3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('N2:N3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('O2:O3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('P2:P3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('Q2:Q3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('R2:R3');


                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 4:
                 $title = $statistics[3]['name'];
                 $Service = \Common\Service\BankBaddebtDetailStNewService::get_instance();
                 $statistics[3]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '金融机构')
                     ->setCellValue('B2', '企业名称(包括个人)')
                     ->setCellValue('C2', '法定代表人')
                     ->setCellValue('D2', '地址')
                     ->setCellValue('E2', '不良贷款余额')
                     ->setCellValue('F2', '不良贷款分类')
                     ->setCellValue('G2', '行业分类')
                     ->setCellValue('H2', '列入不良时间')
                     ->setCellValue('I2', '收回不良时间')
                     ->setCellValue('J2', '不良产生的原因')
                     ->setCellValue('K2', '清收措施')
                     ->setCellValue('L2', '企业基本情况(1000万以上填写)')
                     ->setCellValue('M2', '下步处置计划(1000万以上填写)')
                     ->setCellValue('N2', '要求/建议(1000万以上填写)');


                  //echo json_encode($statistics);die();
                 if ($statistics[3]['data']) {
                     $unit_start = 3;
                     foreach ($statistics[3]['data'] as $key => $unit) {
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$unit_start, $unit['all_name']);

                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['enterprise']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Principal']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start,  $unit['content']['Address']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start,  $unit['content']['Loans']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start,  $unit['content']['Loans_Type']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start,  $unit['content']['Industry']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start,  time_to_date($unit['content']['Startdate']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start,  time_to_date($unit['content']['Enddate']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start,  $unit['content']['Reason']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start,  $unit['content']['Handle']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start,  $unit['content']['Info']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start,  $unit['content']['Plan']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start,  $unit['content']['Recommend']);
                         $unit_start++;
                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:N1');


                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 5:
                 $title = $statistics[4]['name'];
                 $Service = \Common\Service\BankBaddebtDisposeStNewService::get_instance();
                 $statistics[4]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '金融机构')
                     ->setCellValue('B2', '企业或个人名称')
                     ->setCellValue('C2', '不良贷款余额')
                     ->setCellValue('D2', '清收金额合计(一月至累计)')
                     ->setCellValue('E2', '其中:清收方式及金额')
                     ->setCellValue('E3', '以资抵债金额')
                     ->setCellValue('F3', '法院清收金额')
                     ->setCellValue('G3', '核销金额')
                     ->setCellValue('H3', '划转上级行金额')
                     ->setCellValue('I3', '政策性剥离金额')
                     ->setCellValue('J3', '打包出售(注明打包对象)')
                     ->setCellValue('K3', '金额')
                     ->setCellValue('L3', '其他(注明清收方式)')
                     ->setCellValue('M3', '金额');



                 //echo json_encode($statistics);die();
                 if ($statistics[4]['data']) {
                     $unit_start = 4;
                     foreach ($statistics[4]['data'] as $key => $unit) {
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$unit_start, $unit['all_name']);

                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['enterprise']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Loans']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start,  $unit['content']['Recover']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start,  $unit['content']['Recover_Ot_1']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start,  $unit['content']['Recover_Ot_2']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start,  $unit['content']['Recover_Ot_3']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start,  ($unit['content']['Recover_Ot_4']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start,  ($unit['content']['Recover_Ot_5']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start,  $unit['content']['Recover_Ot_6']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start,  $unit['content']['Recover_Ot']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start,  $unit['content']['Recover_Ot_other_name']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start,  $unit['content']['Recover_Ot_other']);
                         $unit_start++;
                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:M1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('E2:M2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:C3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('D2:D3');

                 $PHPExcel->getActiveSheet(0)->getStyle('E2')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );

                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 6:
                 $title = $statistics[5]['name'];
                 $Service = \Common\Service\BankFocusDetailStNewService::get_instance();
                 $statistics[5]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '金融机构')
                     ->setCellValue('B2', '企业或个人名称')
                     ->setCellValue('C2', '贷款余额')
                     ->setCellValue('D2', '逾期贷款余额')
                     ->setCellValue('E2', '发放日期')
                     ->setCellValue('F2', '到期日期')
                     ->setCellValue('G2', '行业分类')
                     ->setCellValue('H2', '企业规模(工信部标准)')
                     ->setCellValue('I2', '法定代表人')
                     ->setCellValue('J2', '联系电话')
                     ->setCellValue('K2', '注册地址')
                     ->setCellValue('L2', '所属镇街道')
                     ->setCellValue('M2', '备注');


                 //echo json_encode($statistics);die();
                 if ($statistics[5]['data']) {
                     $unit_start = 3;
                     foreach ($statistics[5]['data'] as $key => $unit) {
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$unit_start, $unit['all_name']);

                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['enterprise']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Loans']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start,  $unit['content']['Overdue_Loans']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start,  time_to_date($unit['content']['Startdate']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start,  time_to_date($unit['content']['Enddate']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start,  $unit['content']['Industry']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start,  ($unit['content']['Scale']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start,  ($unit['content']['Principal']));
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start,  $unit['content']['Address']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start,  $unit['content']['Phone']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start,  $unit['content']['Area']);
                         $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start,  $unit['content']['Remark']);
                         $unit_start++;
                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:M1');


                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );



                 break;
             case 7:
                 $title = $statistics[6]['name'];
                 $Service = \Common\Service\BankQuarterlyQuantityAStNewService::get_instance();
                 $statistics[6]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')
                     ->setCellValue('C2', '一年以内')
                     ->setCellValue('C3', '贷款利率')
                     ->setCellValue('E3', '最高利率')
                     ->setCellValue('G3', '最低利率')
                     ->setCellValue('I3', '贷款方式')
                     ->setCellValue('I4', '信用')
                     ->setCellValue('K4', '抵押(质押)')
                     ->setCellValue('M4', '保证')
                     ->setCellValue('O4', '抵押(质押+保证)')
                     ->setCellValue('C5', '发生额')
                     ->setCellValue('D5', '加权平均利率')
                     ->setCellValue('E5', '发生额')
                     ->setCellValue('F5', '利率')
                     ->setCellValue('G5', '发生额')
                     ->setCellValue('H5', '利率')
                     ->setCellValue('I5', '发生额')
                     ->setCellValue('J5', '加权平均利率')
                     ->setCellValue('K5', '发生额')
                     ->setCellValue('L5', '加权平均利率')
                     ->setCellValue('M5', '发生额')
                     ->setCellValue('N5', '加权平均利率')
                     ->setCellValue('O5', '发生额')
                     ->setCellValue('P5', '加权平均利率')

                     ->setCellValue('Q2', '一年至五年(含5年)')
                     ->setCellValue('Q3', '贷款利率')
                     ->setCellValue('S3', '最高利率')
                     ->setCellValue('U3', '最低利率')
                     ->setCellValue('W3', '贷款方式')
                     ->setCellValue('W4', '信用')
                     ->setCellValue('Y4', '抵押(质押)')
                     ->setCellValue('AA4', '保证')
                     ->setCellValue('AC4', '抵押(质押+保证)')
                     ->setCellValue('Q5', '发生额')
                     ->setCellValue('R5', '加权平均利率')
                     ->setCellValue('S5', '发生额')
                     ->setCellValue('T5', '利率')
                     ->setCellValue('U5', '发生额')
                     ->setCellValue('V5', '利率')
                     ->setCellValue('W5', '发生额')
                     ->setCellValue('X5', '加权平均利率')
                     ->setCellValue('Y5', '发生额')
                     ->setCellValue('Z5', '加权平均利率')
                     ->setCellValue('AA5', '发生额')
                     ->setCellValue('AB5', '加权平均利率')
                     ->setCellValue('AC5', '发生额')
                     ->setCellValue('AD5', '加权平均利率')

                     ->setCellValue('AE2', '五年以上')
                     ->setCellValue('AE3', '贷款利率')
                     ->setCellValue('AG3', '最高利率')
                     ->setCellValue('AI3', '最低利率')
                     ->setCellValue('AK3', '贷款方式')
                     ->setCellValue('AK4', '信用')
                     ->setCellValue('AM4', '抵押(质押)')
                     ->setCellValue('AO4', '保证')
                     ->setCellValue('AQ4', '抵押(质押+保证)')
                     ->setCellValue('AE5', '发生额')
                     ->setCellValue('AF5', '加权平均利率')
                     ->setCellValue('AG5', '发生额')
                     ->setCellValue('AH5', '利率')
                     ->setCellValue('AI5', '发生额')
                     ->setCellValue('AJ5', '利率')
                     ->setCellValue('AK5', '发生额')
                     ->setCellValue('AL5', '加权平均利率')
                     ->setCellValue('AM5', '发生额')
                     ->setCellValue('AN5', '加权平均利率')
                     ->setCellValue('AO5', '发生额')
                     ->setCellValue('AP5', '加权平均利率')
                     ->setCellValue('AQ5', '发生额')
                     ->setCellValue('AR5', '加权平均利率')

                     ;


                 // echo json_encode($statistics);die();
                 if ($statistics[6]['data']) {
                     $start = 6;
                     foreach ($statistics[6]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num  - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['first'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['first'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['first'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['first'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['first'][4]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['first'][5]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['first'][6]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['first'][7]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $unit['content']['first'][8]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $unit['content']['first'][9]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $unit['content']['first'][10]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $unit['content']['first'][11]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $unit['content']['first'][12]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $unit['content']['first'][13]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$unit_start, $unit['content']['second'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$unit_start, $unit['content']['second'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.$unit_start, $unit['content']['second'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$unit_start, $unit['content']['second'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.$unit_start, $unit['content']['second'][4]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.$unit_start, $unit['content']['second'][5]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.$unit_start, $unit['content']['second'][6]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.$unit_start, $unit['content']['second'][7]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.$unit_start, $unit['content']['second'][8]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.$unit_start, $unit['content']['second'][9]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.$unit_start, $unit['content']['second'][10]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.$unit_start, $unit['content']['second'][11]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.$unit_start, $unit['content']['second'][12]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.$unit_start, $unit['content']['second'][13]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AE'.$unit_start, $unit['content']['third'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AF'.$unit_start, $unit['content']['third'][1]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AG'.$unit_start, $unit['content']['third'][2]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AH'.$unit_start, $unit['content']['third'][3]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AI'.$unit_start, $unit['content']['third'][4]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AJ'.$unit_start, $unit['content']['third'][5]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AK'.$unit_start, $unit['content']['third'][6]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AL'.$unit_start, $unit['content']['third'][7]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AM'.$unit_start, $unit['content']['third'][8]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AN'.$unit_start, $unit['content']['third'][9]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AO'.$unit_start, $unit['content']['third'][10]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AP'.$unit_start, $unit['content']['third'][11]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AQ'.$unit_start, $unit['content']['third'][12]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('AR'.$unit_start, $unit['content']['third'][13]);

                                     $unit_start++;
                                 }
                             }

                             $start = $start + $num ;
                         } else {
                             // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');


                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $statistics[6]['data']['合计']['first'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $statistics[6]['data']['合计']['first'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $statistics[6]['data']['合计']['first'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $statistics[6]['data']['合计']['first'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $statistics[6]['data']['合计']['first'][4]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $statistics[6]['data']['合计']['first'][5]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $statistics[6]['data']['合计']['first'][6]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $statistics[6]['data']['合计']['first'][7]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $statistics[6]['data']['合计']['first'][8]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $statistics[6]['data']['合计']['first'][9]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $statistics[6]['data']['合计']['first'][10]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $statistics[6]['data']['合计']['first'][11]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $statistics[6]['data']['合计']['first'][12]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $statistics[6]['data']['合计']['first'][13]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$unit_start, $statistics[6]['data']['合计']['second'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$unit_start, $statistics[6]['data']['合计']['second'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('S'.$unit_start, $statistics[6]['data']['合计']['second'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$unit_start, $statistics[6]['data']['合计']['second'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('U'.$unit_start, $statistics[6]['data']['合计']['second'][4]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('V'.$unit_start, $statistics[6]['data']['合计']['second'][5]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('W'.$unit_start, $statistics[6]['data']['合计']['second'][6]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('X'.$unit_start, $statistics[6]['data']['合计']['second'][7]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.$unit_start, $statistics[6]['data']['合计']['second'][8]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.$unit_start, $statistics[6]['data']['合计']['second'][9]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AA'.$unit_start, $statistics[6]['data']['合计']['second'][10]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AB'.$unit_start, $statistics[6]['data']['合计']['second'][11]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AC'.$unit_start, $statistics[6]['data']['合计']['second'][12]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AD'.$unit_start, $statistics[6]['data']['合计']['second'][13]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AE'.$unit_start, $statistics[6]['data']['合计']['third'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AF'.$unit_start, $statistics[6]['data']['合计']['third'][1]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AG'.$unit_start, $statistics[6]['data']['合计']['third'][2]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AH'.$unit_start, $statistics[6]['data']['合计']['third'][3]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AI'.$unit_start, $statistics[6]['data']['合计']['third'][4]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AJ'.$unit_start, $statistics[6]['data']['合计']['third'][5]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AK'.$unit_start, $statistics[6]['data']['合计']['third'][6]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AL'.$unit_start, $statistics[6]['data']['合计']['third'][7]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AM'.$unit_start, $statistics[6]['data']['合计']['third'][8]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AN'.$unit_start, $statistics[6]['data']['合计']['third'][9]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AO'.$unit_start, $statistics[6]['data']['合计']['third'][10]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AP'.$unit_start, $statistics[6]['data']['合计']['third'][11]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AQ'.$unit_start, $statistics[6]['data']['合计']['third'][12]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('AR'.$unit_start, $statistics[6]['data']['合计']['third'][13]);

                         }


                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:AR1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A5');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B5');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:P2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('Q2:AD2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AE2:AR2');


                 $PHPExcel->getActiveSheet(0)->mergeCells('C3:D4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('E3:F4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('G3:H4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I3:P3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I4:J4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('K4:L4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('M4:N4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('O4:P4');

                 $PHPExcel->getActiveSheet(0)->mergeCells('Q3:R4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('S3:T4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('U3:V4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('W3:AD3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('W4:X4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('Y4:Z4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AA4:AB4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AC4:AD4');

                 $PHPExcel->getActiveSheet(0)->mergeCells('AE3:AF4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AG3:AH4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AI3:AJ4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AK3:AR3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AK4:AL4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AM4:AN4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AO4:AP4');
                 $PHPExcel->getActiveSheet(0)->mergeCells('AQ4:AR4');

                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );

                 $PHPExcel->getActiveSheet(0)->getStyle('I3')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );
                 $PHPExcel->getActiveSheet(0)->getStyle('W3')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );
                 $PHPExcel->getActiveSheet(0)->getStyle('AK3')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );

                 break;
             case 8:
                 $title = $statistics[7]['name'];
                 $Service = \Common\Service\BankQuarterlyQuantityBStNewService::get_instance();
                 $statistics[7]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')
                     
                     ->setCellValue('C2', '大型企业')
                     ->setCellValue('C3', '本季发生额')
                     ->setCellValue('D3', '加权平均利率')

                     ->setCellValue('E2', '中型企业')
                     ->setCellValue('E3', '本季发生额')
                     ->setCellValue('F3', '加权平均利率')

                     ->setCellValue('G2', '小型企业')
                     ->setCellValue('G3', '本季发生额')
                     ->setCellValue('H3', '加权平均利率')

                     ->setCellValue('I2', '微型企业')
                     ->setCellValue('I3', '本季发生额')
                     ->setCellValue('J3', '加权平均利率')

                 ;


                 // echo json_encode($statistics);die();
                 if ($statistics[7]['data']) {
                     $start = 4;
                     foreach ($statistics[7]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num  - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['first'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['first'][1]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['second'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['second'][1]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['third'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['third'][1]);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['forth'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['forth'][1]);
                                     $unit_start++;
                                 }
                             }

                             $start = $start + $num ;
                         } else {
                             // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');


                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $statistics[7]['data']['合计']['first'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $statistics[7]['data']['合计']['first'][1]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $statistics[7]['data']['合计']['second'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $statistics[7]['data']['合计']['second'][1]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $statistics[7]['data']['合计']['third'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $statistics[7]['data']['合计']['third'][1]);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $statistics[7]['data']['合计']['forth'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $statistics[7]['data']['合计']['forth'][1]);

                         }


                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:J1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:D2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('E2:F2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('G2:H2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I2:J2');


                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );


                 break;
             case 9:
                 $title = $statistics[8]['name'];
                 $Service = \Common\Service\BankQuarterlyQuantityCStNewService::get_instance();
                 $statistics[8]['data'] = $Service->get_by_month_year($year, $month);
                 $statistics = $this->convert_statistics_datas($statistics);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A1', $title);
                 $PHPExcel->setActiveSheetIndex(0)
                     ->setCellValue('A2', '类型')
                     ->setCellValue('B2', '金融机构名称!')

                     ->setCellValue('C2', '关注类贷款余额')
                     ->setCellValue('D2', '剪刀差')
                     ->setCellValue('E2', '逾期90天以上未纳入不良')
                     ->setCellValue('F2', '应收未收利息')


                 ;


                 // echo json_encode($statistics);die();
                 if ($statistics[8]['data']) {
                     $start = 3;
                     foreach ($statistics[8]['data'] as $key => $value) {
                         if ($key != '合计') {
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                             $num = $value ? count($value) : 0;
                             $end = $start + $num  - 1;
                             if ($end > $start) {
                                 $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                 $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                     [
                                         'alignment' => [
                                             'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                         ]
                                     ]
                                 );
                             }
                             if ($value) {
                                 $unit_start = $start;
                                 foreach ($value as $unit) {
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['first'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['second'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['third'][0]);
                                     $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['forth'][0]);


                                     $unit_start++;
                                 }
                             }

                             $start = $start + $num ;
                         } else {
                             // echo json_encode($statistics[0]['data'][$key]);die();
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');


                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $statistics[8]['data']['合计']['first'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $statistics[8]['data']['合计']['second'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $statistics[8]['data']['合计']['third'][0]);
                             $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $statistics[8]['data']['合计']['forth'][0]);

                         }


                     }


                 }


                 $PHPExcel->getActiveSheet(0)->mergeCells('A1:J1');
                 $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                 $PHPExcel->getActiveSheet(0)->mergeCells('C2:D2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('E2:F2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('G2:H2');
                 $PHPExcel->getActiveSheet(0)->mergeCells('I2:J2');


                 $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                     [
                         'alignment' => [
                             'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                         ]
                     ]
                 );


                 break;
         }


         header('Content-Type: application/vnd.ms-excel');
         header('Content-Disposition: attachment;filename="'.$title.'.xls"');
         header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0

         $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
         $objWriter->save('php://output');

     }

     private function convert_statistics_datas($statistics) {
         $sub_type_map = \Common\Model\FinancialDepartmentModel::$SUB_TYPE_MAP;

         foreach ($statistics as $k => $_data) {
             $_data = $_data['data'];
             if (!$_data ) {
                 continue;
             }
             foreach ($_data as $_k => $_v) {
                 $statistics[$k]['data'][$_k]['content'] = json_decode($_v['content']);
             }

             if (in_array($k, [0,1,2,6,7,8])) {
                 $statistics[$k]['data'] = result_to_complex_map($statistics[$k]['data'], 'department_sub_type');
                 $temp = [];
                 $all = [];
                 foreach ($statistics[$k]['data'] as $_sub_type => $data) {
                     $sub_type_name = isset($sub_type_map[$_sub_type]) ? $sub_type_map[$_sub_type] : '未知';
                     $temp[$sub_type_name] = $data;
                     foreach ($data as $in_value) {
                         foreach ($in_value['content'] as $field => $value) {
                             if (is_array($value)) {
                                 foreach ($value as $_key => $_value) {
                                     $all[$field][$_key] += $_value;
                                 }
                             } else {
                                 $all[$field] += $value;
                             }
                         }

                     }
                 }
                 $statistics[$k]['data'] = $temp;
                 $statistics[$k]['data']['合计'] = $all;
             }
         }

         $statistics = json_decode(json_encode($statistics), TRUE);
         return $statistics;
     }

     /**
      * 信贷情况月报
      */
     public function baddebt_new_submit_monthly()
     {
         $this->local_service = \Common\Service\BankBaddebtNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;

         parent::submit_monthly();
     }

     /**
      * 贷款明细
      */
     public function baddebt_detail_new_submit_monthly()
     {

         $this->local_service = \Common\Service\BankBaddebtDetailNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;

         parent::detail_submit_monthly();

     }

     /**
      * 贷款明细
      */
     public function baddebt_dispose_new_submit_monthly()
     {

         $this->local_service = \Common\Service\BankBaddebtDisposeNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;
         parent::detail_submit_monthly();

     }


     /**
      * 贷款明细
      */
     public function focus_detail_new_submit_monthly()
     {

         $this->local_service = \Common\Service\BankFocusDetailNewService::get_instance();
         $this->verify_type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;
         parent::detail_submit_monthly();

     }

     protected function get_add_data_baddebt_detail_new_submit_monthly($data, $cache_data='') {
         $batch_data = [];

         if ($cache_data) {
             foreach ($cache_data as $k => $value) {
                 if ($value) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();

                     $temp['Enterprise'] = $value['Enterprise'];
                     $temp['Principal'] = $value['Principal'];
                     $temp['Address'] = $value['Address'];
                     $temp['Loans'] = $value['Loans'];
                     $temp['Loans_Type'] = $value['Loans_Type'];
                     $temp['Startdate'] = $value['Startdate'];
                     $temp['Enddate'] = $value['Enddate'];
                     $temp['Industry'] = $value['Industry'];
                     $temp['Reason'] = $value['Reason'];
                     $temp['Handle'] = $value['Handle'];
                     $temp['Info'] = $value['Info'];
                     $temp['Plan'] = $value['Plan'];
                     $temp['Recommend'] = $value['Recommend'];

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }

         }

         return $batch_data;
     }


     protected function get_add_data_baddebt_dispose_new_submit_monthly($data, $cache_data='') {
         $batch_data = [];

         if ($cache_data) {
             foreach ($cache_data as $k => $value) {
                 if ($value) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();

                     $temp['Enterprise'] = $value['Enterprise'];
                     $temp['Loans'] = $value['Loans'];
                     $temp['Recover'] = $value['Recover'];
                     $temp['Recover_Ot_1'] = $value['Recover_Ot_1'];
                     $temp['Recover_Ot_2'] = $value['Recover_Ot_2'];
                     $temp['Recover_Ot_3'] = $value['Recover_Ot_3'];
                     $temp['Recover_Ot_4'] = $value['Recover_Ot_4'];
                     $temp['Recover_Ot_5'] = $value['Recover_Ot_5'];
                     $temp['Recover_Ot_6'] = $value['Recover_Ot_6'];
                     $temp['Recover_Ot'] = $value['Recover_Ot'];
                     $temp['Recover_Ot_other_name'] = $value['Recover_Ot_other_name'];
                     $temp['Recover_Ot_other'] = $value['Recover_Ot_other'];

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }

         }

         return $batch_data;
     }

     protected function get_add_data_focus_detail_new_submit_monthly($data, $cache_data='') {
         $batch_data = [];

         if ($cache_data) {
             foreach ($cache_data as $k => $value) {
                 if ($value) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();

                     $temp['Enterprise'] = $value['Enterprise'];
                     $temp['Loans'] = $value['Loans'];
                     $temp['Overdue_Loans'] = $value['Overdue_Loans'];
                     $temp['Startdate'] = $value['Startdate'];
                     $temp['Enddate'] = $value['Enddate'];
                     $temp['Industry'] = $value['Industry'];
                     $temp['Scale'] = $value['Scale'];
                     $temp['Principal'] = $value['Principal'];
                     $temp['Address'] = $value['Address'];
                     $temp['Phone'] = $value['Phone'];
                     $temp['Area'] = $value['Area'];
                     $temp['Remark'] = $value['Remark'];

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }

         }

         return $batch_data;
     }


     public function get_detail_page_html() {

         $p = I('p');
         $all_name = I('all_name');
         $year= I('year');
         $month = I('month');
         $type = I('bank_type', 3);
         //获取明细
         $tmp = '';
         if ($type == 3) {
             $Service = \Common\Service\BankBaddebtDetailNewService::get_instance();
             $tmp = 'get_detail_page_html3';
         } elseif ($type == 4) {
             $Service = \Common\Service\BankBaddebtDisposeNewService::get_instance();
             $tmp = 'get_detail_page_html4';
         } elseif ($type == 5) {
             $Service = \Common\Service\BankFocusDetailNewService::get_instance();
             $tmp = 'get_detail_page_html5';
         }

         $_where = [];

         $_where['all_name'] = $all_name;
         $_where['year'] = $year;
         $_where['month'] = $month;
         $infos = $Service->get_by_where_all($_where);
         if ($infos) {
             $data_1_map = [];
             //$this->convert_data_detail_submit_monthly($infos);
             foreach ($infos as $da) {
                 $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
             }
         }

         if (isset($data_1_map[$year.'_'.$month.'_'.$all_name])) {
             $data = $data_1_map[$year.'_'.$month.'_'.$all_name];

             $count = count($data);
             $page_size = \Common\Service\BaseService::$page_size;

             $PageInstance = new \Think\Page($count, $page_size);
             if($count>$page_size){
                 $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
             }
             $page_html = $PageInstance->show();
             $this->assign('page_html', $page_html);
             $page = $p;
             $data = array_slice($data, $page_size * ($page-1), $page_size);
             $this->assign('data', $data);

         }

         $this->display($tmp);


     }

     public function submit_verify() {

         //获取所有相关的公司
         $DepartmentService = \Common\Service\DepartmentService::get_instance();

         $departments = $DepartmentService->get_my_list(UID, $this->type);


         if (!$departments) {
             $departments = $DepartmentService->get_all_list($this->type);
         }
         $data = $departments[0];
         $all_name = $data['all_name'];
         $all_name = I('all_name') ? I('all_name') : $all_name;
         $year = I('year') ? I('year') : intval(date('Y'));
         $month = I('month') ? I('month') : intval(date('m'));

         //获取公司各表格填报状态
         $status = [0,0,0,0,0];
         $BankCreditNewService = \Common\Service\BankCreditNewService::get_instance();
         if ($BankCreditNewService->get_by_month_year($year, $month, $all_name)) {
             $status[0] = 1;
         }
         $BankBaddebtNewService = \Common\Service\BankBaddebtNewService::get_instance();
         if ($BankBaddebtNewService->get_by_month_year($year, $month, $all_name)) {
             $status[1] = 1;
         }
         $BankBaddebtDetailNewService = \Common\Service\BankBaddebtDetailNewService::get_instance();
         if ($BankBaddebtDetailNewService->get_by_month_year($year, $month, $all_name)) {
             $status[2] = 1;
         }
         $BankBaddebtDisposeNewService = \Common\Service\BankBaddebtDisposeNewService::get_instance();
         if ($BankBaddebtDisposeNewService->get_by_month_year($year, $month, $all_name)) {
             $status[3] = 1;
         }
         $BankFocusDetailNewService = \Common\Service\BankFocusDetailNewService::get_instance();
         if ($BankFocusDetailNewService->get_by_month_year($year, $month, $all_name)) {
             $status[4] = 1;
         }

         $VerifyService = \Common\Service\VerifyService::get_instance();
         $type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;
         $verify_info = $VerifyService->get_info($year,$month,$all_name,$type);

         if (IS_POST) {
             foreach ($status as $_v) {
                 if (!$_v) {
                     $this->error('对不起,您还无法提交审核,请确保月报表都已经保存!');
                 }
             }

             if ($verify_info && $verify_info['status'] != 0) {
                 $this->error('对不起,您无法提交审核,该月审核记录已经提交!');
             }

             if ($verify_info) {
                 $data = [];
                 $data['status'] = 1;
                 $data['uid'] = UID;
                 $ret = $VerifyService->update_by_id($verify_info['id'], $data);
                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 action_user_log('提交银行月报审核,id:'.$verify_info['id']);
             } else {
                 $data = [];
                 $data['year'] = $year;
                 $data['month'] = $month;
                 $data['all_name'] = $all_name;
                 $data['type'] = $type;
                 $data['status'] = 1;
                 $data['uid'] = UID;
                 $ret = $VerifyService->add_one($data);
                 if (!$ret->success) {
                     $this->error($ret->message);
                 }
                 action_user_log('提交银行月报审核,id:'.$ret->data);
             }


             $this->success('提交成功');
         }


         $departments = result_to_array($departments, 'all_name');
         $this->assign('departments', $departments);

         $this->assign('status', $status);

         $can_submit = 0;
         if (!isset($verify_info['status']) || $verify_info['status'] == 0) {
             $can_submit = 1;
         }
         $this->assign('can_submit', $can_submit);

         $this->display();


     }


 }