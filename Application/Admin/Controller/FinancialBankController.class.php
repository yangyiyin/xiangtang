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
             if (!$data['logs1']) {
                 $this->error('请填写完整的信息~');
             }
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
             foreach ($data['logs1'] as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Enterprise'] = $v;
                     $temp['Principal'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
                     $temp['Area'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
                     $temp['Recover'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
                     $temp['Recover_Time'] = isset($data['logs5'][$k]) ? strtotime($data['logs5'][$k]) : 0;
                     $temp['Recover_Method'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : 0;
                     $temp['Remarks'] = isset($data['logs7'][$k]) ? $data['logs7'][$k] : '';

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }
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
     public function credit_submit_monthly()
     {
         $this->local_service = \Common\Service\BankCreditService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;
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
                     action_user_log('修改信贷情况月报表');
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
                             action_user_log('修改信贷情况月报表');
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
                     action_user_log('新增信贷情况月报表');
                     $this->success('添加成功！');
                 } else {
                     $this->error($ret->message);
                 }
             }
         } else {
             $this->title = '信贷情况月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '信贷情况月填报[正在编辑历史数据]';
             }

             parent::submit_monthly();

             $this->display();
         }
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

     /**
      * 贷款明细
      */
     public function loan_details_submit_monthly()
     {

         $this->local_service = \Common\Service\BankLoanDetailService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;
             if (!$data['logs1']) {
                 $this->error('请填写完整的信息~');
             }
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
             foreach ($data['logs1'] as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Contract'] = $v;
                     $temp['Enterprise'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
                     $temp['Loans'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
                     $temp['Interest'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
                     $temp['Principal'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
                     $temp['Phone'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : 0;
                     $temp['Address'] = isset($data['logs7'][$k]) ? $data['logs7'][$k] : 0;
                     $temp['Area'] = isset($data['logs8'][$k]) ? $data['logs8'][$k] : 0;
                     $temp['Industry'] = isset($data['logs9'][$k]) ? $data['logs9'][$k] : '';
                     $temp['Startdate'] = isset($data['logs10'][$k]) ? strtotime($data['logs10'][$k]) : '';
                     $temp['Enddate'] = isset($data['logs11'][$k]) ? strtotime($data['logs11'][$k]) : '';
                     $temp['Guarantee'] = isset($data['logs12'][$k]) ? $data['logs12'][$k] : '';
                     $temp['Over_Credit'] = isset($data['logs13'][$k]) ? $data['logs13'][$k] : '';
                     $temp['Over_Mortgage'] = isset($data['logs14'][$k]) ? $data['logs14'][$k] : '';
                     $temp['Over_Pledge'] = isset($data['logs15'][$k]) ? $data['logs15'][$k] : '';
                     $temp['Over_Margin'] = isset($data['logs16'][$k]) ? $data['logs16'][$k] : '';
                     $temp['Guarantor'] = isset($data['logs17'][$k]) ? $data['logs17'][$k] : '';
                     $temp['Pattern'] = isset($data['logs18'][$k]) ? $data['logs18'][$k] : '';
                     $temp['OverdueDays'] = isset($data['logs19'][$k]) ? $data['logs19'][$k] : '';
                     $temp['Coordination'] = isset($data['logs20'][$k]) ? $data['logs20'][$k] : '';
                     $temp['Remarks'] = isset($data['logs21'][$k]) ? $data['logs21'][$k] : '';


                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }
             $ret = $this->local_service->add_batch($batch_data);
             if ($ret->success) {
//                 $this->update_loan_st($batch_data);
//                 $this->update_area_a_st($data['year'], $data['month']);
//                 $this->update_area_b_st($data['year'], $data['month']);
                 action_user_log('新增贷款明细报表');
                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }


         } else {
             $this->title = '贷款明细月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '贷款明细月填报[正在编辑历史数据]';
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

                 $infos = $this->local_service->pattern_options($infos);
             }
             $this->assign('infos', $infos);
             $this->assign('area_options', $AreaService->set_area_options());
             $this->assign('pattern_options', $this->local_service->pattern_options());



             $this->display();
         }
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
             if (!$data['logs1']) {
                 $this->error('请填写完整的信息~');
             }
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
             foreach ($data['logs1'] as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Enterprise'] = $v;
                     $temp['Principal'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
                     $temp['Area'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
                     $temp['Overdue'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
                     $temp['Resolve'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
                     $temp['Remarks'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : '';

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }
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
         $this->title = '';
         parent::statistics();


         $this->display();
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


    public function submit_verify() {
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
                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);

                if (!$ret->success) {
                    $this->error($ret->message);
                }
                //action_user_log('提交状态');
                $this->success('提交成功~');
                break;
            case 'loan_details':
                $service = \Common\Service\BankLoanDetailService::get_instance();
                $all_name = I('get.all_name');
                if (!$this->check_is_my_department($all_name)) {
                    $this->error('没有该信息或者权限不够');
                }

                $year = I('get.year');
                $month = I('get.month');
                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);

                if (!$ret->success) {
                    $this->error($ret->message);
                }
                //action_user_log('提交状态');
                $this->success('提交成功~');
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
                $ret = $service->update_by_year_month_all_name($year, $month, $all_name, ['status'=>1]);

                if (!$ret->success) {
                    $this->error($ret->message);
                }
                //action_user_log('提交状态');
                $this->success('提交成功~');
                break;
            default:
                break;
        }

        if ($service) {
            $ret = $service->update_by_id($id, ['status' => 1]);
            if (!$ret->success) {
                $this->error($ret->message);
            }
            //action_user_log('提交状态');
            $this->success('提交成功~');
        }
        $this->error('参数错误');
    }


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
         require APP_PATH . '/Common/Lib/php-excel-reader/excel_reader2.php';
         $excel = new \Spreadsheet_Excel_Reader($_FILES['file']['tmp_name']);

         $data = $bad_data = [];
         $type = I('get.type');
         $AreaService = \Common\Service\AreaService::get_instance();
         $key = '';
         if ($type == 'baddebt_dispose') {
             if ($excel->colcount() != 7) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<$excel->rowcount() + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 for ($j=1;$j<$excel->colcount() + 1;$j++) {

                     if ($j == 3) {
                         //处理街道
                         $val = $excel->val($i,$j);
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {
                             $is_bad_row = true;
                         }

                     }

                     if ($j == 6) {
                         //处理收回方式
                         $val = $excel->val($i,$j);
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


                     $temp[] = $excel->val($i,$j);
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
             if ($excel->colcount() != 21) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<$excel->rowcount() + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 for ($j=1;$j<$excel->colcount() + 1;$j++) {

                     if ($j == 8) {
                         //处理街道
                         $val = $excel->val($i,$j);
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {
                             $is_bad_row = true;
                         }

                     }

                     if ($j == 18) {
                         //处理收回方式
                         $val = $excel->val($i,$j);
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


                     $temp[] = $excel->val($i,$j);
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
             if ($excel->colcount() != 6) {
                 $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
             }
             for($i=3;$i<$excel->rowcount() + 1;$i++) {
                 $temp = [];
                 $is_bad_row = false;
                 for ($j=1;$j<$excel->colcount() + 1;$j++) {

                     if ($j == 3) {
                         //处理街道
                         $val = $excel->val($i,$j);
                         if ($val) {
                             $area = $AreaService->get_like_name($val);
                         } else {
                             $area = '';
                         }

                         if ($area) {
                             $temp[] = $area['id'];
                             continue;
                         } else {
                             $is_bad_row = true;
                         }

                     }


                     $temp[] = $excel->val($i,$j);
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

         }


         unlink($_FILES['file']['tmp_name']);

         if ($data) {
             $this->ajaxReturn(['status'=>true, 'data' => $data, 'key'=>$key]);
         } else {
             $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
         }

     }

     public function get_excel(){
         $key = I('key');
         $bad_data = S($key);
         exportexcel($bad_data,'退回数据', '退回数据');
     }

     public function log_export_excel(){
         $type = I('type');
         if ($type == 'loan_details') {
             $BankLoanDetailService = \Common\Service\BankLoanDetailService::get_instance();
             $data = $BankLoanDetailService->get_by_month_year_all_names(intval(date('Y')), intval(date('m')));
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
 }