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
         parent::_initialize();
           $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank;
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
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_baddebt_dispose_submit_log($data);
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
                 //$this->update_st($batch_data);
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
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_loan_details_submit_log($data);
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
                // $data[$k]['recover_time'] = time_to_date($v['Recover_Time']);
                 //$data[$k]['recover_method'] = isset($recover_method_map[$v['Recover_Method']]) ? $recover_method_map[$v['Recover_Method']] : '未知';
             }
         }
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
                     $this->convert_data_submit_monthly($info);
                 }
             }
             $this->assign('info', $info);

             if (!$info && !$this->is_history) {
                 //获取季度存贷款
                 $BankCreditService = \Common\Service\BankCreditService::get_instance();
                 $datas = $BankCreditService->get_quarterly_data();
                 if ($datas) {
                     $loans = $deposits = 0;
                     foreach ($datas as $credit) {
                         $loans += $credit['']
                     }
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
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
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


     public function statistics()
     {
         $this->title = '';
         parent::statistics();


         $this->display();
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
                            $password = '123456';
                            $username = $data['username'];
                             if (!$username) {
                                $this->error('后台登录名不能为空');
                             }
                            /* 调用注册接口注册用户 */
                            $User   =   new UserApi();
                            $uid    =   $User->register($username, $password, '');
                            if(0 < $uid){ //注册成功
                                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1, 'reg_time' => time());
                                if(!M('Member')->add($user)){
                                    $this->error('添加失败！');
                                } else {
                                    $gid = C('GROUP_Financial' . 'Bank');
                                    if( empty($uid) ){
                                        $this->error('参数有误');
                                    }
                                    $AuthGroup = D('AuthGroup');
                                    if( $gid && !$AuthGroup->checkGroupId($gid)){
                                        $this->error($AuthGroup->error);
                                    }
                                    if ( $AuthGroup->addToGroup($uid,$gid) ){

                                    }else{
                                        $this->error($AuthGroup->getError());
                                    }

                                    $data['uid'] = $uid;

                                }
                            } else { //注册失败，显示错误信息
                                $this->error('添加失败!'.$uid.',登录名可能重复,请重试');
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
        $uids = result_to_array($data, 'uid');
        $User   =   new UserApi();
        $users    =   $User->get_by_uids($uids);
        $users_map = result_to_map($users, 'id');
        $sub_type_map = \Common\Model\FinancialDepartmentModel::$SUB_TYPE_MAP;
        foreach ($data as $k=>$v) {
            if (isset($users_map[$v['uid']])) {
                $data[$k]['user'] = $users_map[$v['uid']];
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



 }