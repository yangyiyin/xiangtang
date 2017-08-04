<?php
/**
 * Created by newModule.
 * Time: 2017-08-01 08:14:09
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialInvestmentController extends FinancialBaseController  {
     protected function _initialize() {
         parent::_initialize();
           $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment;
     }

     public function submit_monthly()
     {
                if (IS_POST) {
                     $id = I('get.id');
                     $data = I('post.');
                     $data['uid'] = UID;
                    $data['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_A;
                    foreach ($data['Staff_Sub'] as $sub) {
                        if ($sub == '' || !is_numeric($sub)) {
                            $this->error('请检查从业人员相关数据是否正确');
                        }
                    }
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
                             action_user_log('修改股权投资和创业投资机构单位月报表');
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
                                     action_user_log('修改股权投资和创业投资机构单位月报表');
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
                             action_user_log('新增股权投资和创业投资机构单位月报表');
                             $this->success('添加成功！');
                         } else {
                             $this->error($ret->message);
                         }
                     }
                 } else {
                     $this->title = '股权投资和创业投资机构单位月填报('. date('Y-m') .'月)';
                     if ($this->is_history) {
                         $this->title = '股权投资和创业投资机构单位月填报[正在编辑历史数据]';
                     }

                     parent::submit_monthly();

                     $this->display();
                 }
     }

     public function detail_submit_monthly() {
         $this->local_service = \Common\Service\InvestmentDetailsService::get_instance();
         if (IS_POST) {
             $id = I('get.id');
             $data = I('post.');
             $data['uid'] = UID;
             $data['Types'] = \Common\Model\FinancialInvestmentDetailsModel::TYPE_A;
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

             $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name'], \Common\Model\FinancialInvestmentDetailsModel::TYPE_A);
             if ($ret){
                 //删除
                 if ($this->is_history && !$data['force_modify']) {
                     $this->error('该月报表已经提交,如需修改,请勾选强制修改');
                 }
                 $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name'], \Common\Model\FinancialInvestmentDetailsModel::TYPE_A);
             }
             $batch_data = [];
             foreach ($data['logs1'] as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Name'] = $v;
                     $temp['Area'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : 0;
                     $temp['Amount'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : 0;
                     $temp['Remarks'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : '';
                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }
             $ret = $this->local_service->add_batch($batch_data);
             if ($ret->success) {
                 action_user_log('新增股权投资机构明细月报表');
                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }
         } else {
             $this->title = '股权投资机构所投资公司明细月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '股权投资机构所投资公司明细月填报[正在编辑历史数据]';
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
                     $infos = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name'], \Common\Model\FinancialInvestmentDetailsModel::TYPE_A);
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


     /**
      * 退出明细
      */
     public function exit_detail_submit_monthly() {
         $this->local_service = \Common\Service\InvestmentExitService::get_instance();
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
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Name'] = $v;
                     $temp['Startdate'] = isset($data['logs2'][$k]) ? strtotime($data['logs2'][$k]) : 0;
                     $temp['Exitdate'] = isset($data['logs3'][$k]) ? strtotime($data['logs3'][$k]) : 0;
                     $temp['Investment'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
                     $temp['Recycling'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
                     $temp['ExitMethod'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : 0;


                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }
             $ret = $this->local_service->add_batch($batch_data);
             if ($ret->success) {
                 action_user_log('新增股权投资机构退出明细月报表');
                 $this->success('添加成功！');
             } else {
                 $this->error($ret->message);
             }
         } else {
             $this->title = '股权投资机构退出明细月填报('. date('Y-m') .'月)';
             if ($this->is_history) {
                 $this->title = '股权投资机构退出明细月填报[正在编辑历史数据]';
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

             $exit_method_options = $this->local_service->get_exit_method_options();
             $this->assign('exit_method_options', $exit_method_options);
             if ($infos) {
                 $infos = $this->local_service->get_exit_method_options($infos);
             }

             //获取区域
             $this->assign('infos', $infos);
             $this->display();
         }
     }

     public function statistics()
     {
         $this->title = '';
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
         $service = '\Common\Service\\'.$this->local_service_name;
         $page = I('get.p', 1);
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentModel::TYPE_A];
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_statistics($data, $where['year'], $where['month']);
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);



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
        action_user_log('删除股权投资和创业投资机构单位');
        $this->success('删除成功！');
    }

    public function add() {
        $this->local_service = \Common\Service\DepartmentService::get_instance();
        $this->local_service_name = 'DepartmentService';
        if ($id = I('get.id')) {
            $info = $this->local_service->get_info_by_id($id);
            if ($info) {

                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
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
                                action_user_log('修改股权投资和创业投资机构单位');
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
                                    $gid = C('GROUP_Financial' . 'Investment');
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
                                action_user_log('添加股权投资和创业投资机构单位');
                                $this->success('添加成功！', U('index'));
                            } else {
                                $this->error($ret->message);
                            }
                        }


        }
    }

     public function submit_log() {

         $where = [];
         if (I('get.all_name')) {
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         $page = I('get.p', 1);
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentModel::TYPE_A];
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_submit_log($data);
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

     public function detail_log() {
         $this->local_service =\Common\Service\InvestmentDetailsService::get_instance();
         $where = [];
         if (I('get.all_name')) {
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         $page = I('get.p', 1);
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentDetailsModel::TYPE_A];
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $data = $this->convert_data_detail_log($data);
         $service = '\Common\Service\\'.$this->local_service_name;
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();
         //print_r($data);die();
         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     public function exit_log() {
         $this->local_service =\Common\Service\InvestmentExitService::get_instance();
         $where = [];
         if (I('get.all_name')) {
             $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
         }
         $page = I('get.p', 1);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $data = $this->convert_data_exit_log($data);
         $service = '\Common\Service\\'.$this->local_service_name;
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();
         //print_r($data);die();
         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $this->display();
     }

     protected function convert_data_detail_log($data) {
         if ($data) {
             $AreaService = \Common\Service\AreaService::get_instance();
             $areas = $AreaService->get_all();
             $areas_map = result_to_map($areas);
             foreach ($data as $key => $info) {
                 $data[$key]['area_name'] = isset($areas_map[$info['Area']]['name']) ? $areas_map[$info['Area']]['name'] : '未知';
             }

         }
         return $data;

     }

     protected function convert_data_exit_log($data) {
         if ($data) {
             $map = \Common\Model\FinancialInvestmentExitModel::$EXISTS_METHOD_MAP;
             foreach ($data as $key => $info) {
                 $data[$key]['exit_method_name'] = isset($map[$info['ExitMethod']]) ? $map[$info['ExitMethod']] : '未知';
             }

         }
         return $data;

     }

     public function convert_data(&$data) {
        $uids = result_to_array($data, 'uid');
        $User   =   new UserApi();
        $users    =   $User->get_by_uids($uids);
        $users_map = result_to_map($users, 'id');
        foreach ($data as $k=>$v) {
            if (isset($users_map[$v['uid']])) {
                $data[$k]['user'] = $users_map[$v['uid']];
            }
        }
    }


     protected function convert_data_submit_monthly(&$info) {
         if ($info) {
             $info['Staff_Sub'] = explode(',', $info['Staff_Sub']);
         }
     }

     protected function convert_data_submit_log(&$data) {
         if ($data) {
             foreach ($data as $key => $info) {
                 $data[$key]['Staff_Sub'] = explode(',', $info['Staff_Sub']);
             }

         }

     }

     protected function convert_data_statistics(&$data, $year, $month) {
         if ($data) {
             $all_names = result_to_array($data, 'all_name');
             $DepartmentService = \Common\Service\DepartmentService::get_instance();
             $departments = $DepartmentService->get_by_all_names($all_names, \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment);
             $departments_map = result_to_map($departments, 'all_name');

             //获取退回明细
             $InvestmentExitService = \Common\Service\InvestmentExitService::get_instance();
             $all_names = result_to_array($data, 'all_name');
             $exits = $InvestmentExitService->get_by_names_time($all_names, $year, $month);
             $exits_map = result_to_complex_map($exits, 'all_name');
            // echo_json_die($exits_map);die();
             foreach ($data as $key => $info) {
                 $data[$key]['Staff_Sub'] = explode(',', $info['Staff_Sub']);
                 $data[$key]['capital'] = isset($departments_map[$info['all_name']]['capital']) ? $departments_map[$info['all_name']]['capital'] : '未知';
                if (isset($exits_map[$info['all_name']])) {
                    $its_exit = $exits_map[$info['all_name']];
                    $data[$key]['exits_num'] = count($its_exit);
                    $data[$key]['exits_a_num'] = $data[$key]['exits_b_num'] = $data[$key]['exits_c_num'] = $data[$key]['exits_d_num'] = $data[$key]['exits_e_num'] = 0;
                    $data[$key]['exits_a_Investment'] = $data[$key]['exits_b_Investment'] = $data[$key]['exits_c_Investment'] = $data[$key]['exits_d_Investment'] = $data[$key]['exits_e_Investment'] = 0;
                    $data[$key]['exits_a_Recycling'] = $data[$key]['exits_b_Recycling'] = $data[$key]['exits_c_Recycling'] = $data[$key]['exits_d_Recycling'] = $data[$key]['exits_e_Recycling'] = 0;

                    foreach ($its_exit as $exit) {
                        if ($exit['ExitMethod'] == \Common\Model\FinancialInvestmentExitModel::EXIT_METHOD_A) {
                            $data[$key]['exits_a_num']++;
                            $data[$key]['exits_a_Investment'] += $exit['Investment'];
                            $data[$key]['exits_a_Recycling'] += $exit['Recycling'];
                        }
                        if ($exit['ExitMethod'] == \Common\Model\FinancialInvestmentExitModel::EXIT_METHOD_B) {
                            $data[$key]['exits_b_num']++;
                            $data[$key]['exits_b_Investment'] += $exit['Investment'];
                            $data[$key]['exits_b_Recycling'] += $exit['Recycling'];
                        }
                        if ($exit['ExitMethod'] == \Common\Model\FinancialInvestmentExitModel::EXIT_METHOD_C) {
                            $data[$key]['exits_c_num']++;
                            $data[$key]['exits_c_Investment'] += $exit['Investment'];
                            $data[$key]['exits_c_Recycling'] += $exit['Recycling'];
                        }
                        if ($exit['ExitMethod'] == \Common\Model\FinancialInvestmentExitModel::EXIT_METHOD_D) {
                            $data[$key]['exits_d_num']++;
                            $data[$key]['exits_d_Investment'] += $exit['Investment'];
                            $data[$key]['exits_d_Recycling'] += $exit['Recycling'];
                        }
                        if ($exit['ExitMethod'] == \Common\Model\FinancialInvestmentExitModel::EXIT_METHOD_E) {
                            $data[$key]['exits_e_num']++;
                            $data[$key]['exits_e_Investment'] += $exit['Investment'];
                            $data[$key]['exits_e_Recycling'] += $exit['Recycling'];
                        }

                    }


                }
             }

         }

     }
 }