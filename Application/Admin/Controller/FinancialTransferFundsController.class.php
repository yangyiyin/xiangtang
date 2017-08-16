<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 17:20:08
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialTransferFundsController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds;
         parent::_initialize();

     }

     public function submit_monthly()
     {
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
                            $temp['Bank'] = $v;
                            $temp['Account'] = isset($data['logs2'][$k]) ? $data['logs2'][$k] : '';
                            $temp['Unit'] = isset($data['logs3'][$k]) ? $data['logs3'][$k] : '';
                            $temp['Legal_Person'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : '';
                            $temp['Amount'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
                            $temp['S_Date'] = isset($data['logs6'][$k]) ? strtotime($data['logs6'][$k]) : 0;
                            $temp['E_Date'] = isset($data['logs7'][$k]) ? strtotime($data['logs7'][$k]) : 0;
                            $temp['Days'] = isset($data['logs8'][$k]) ? $data['logs8'][$k] : 0;
                            $temp['Remarks'] = isset($data['logs9'][$k]) ? $data['logs9'][$k] : '';

                            $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                            $batch_data[] = $temp;
                        }

                    }
                    $ret = $this->local_service->add_batch($batch_data);
                    if ($ret->success) {
                        $this->update_st($batch_data);
                        action_user_log('新增转贷资金月报表');
                        $this->success('添加成功！');
                    } else {
                        $this->error($ret->message);
                    }


                 } else {
                     $this->title = '转贷资金单位月填报('. date('Y-m') .'月)';
                     if ($this->is_history) {
                         $this->title = '转贷资金单位月填报[正在编辑历史数据]';
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
                    $this->assign('infos', $infos);

                     $this->display();
                 }
     }

     protected function update_st($batch_data) {
         $TransferFundsStService = \Common\Service\TransferFundsStService::get_instance();

         $st = $TransferFundsStService->get_by_month_year($batch_data[0]['year'], $batch_data[0]['month'], $batch_data[0]['all_name']);

         $data = [];

         foreach ($batch_data as $d) {
             $data['M_Amount'] += $d['Amount'];
             $data['M_Quantity'] ++;
         }

         //获取年和历史的
         $data_a = $this->local_service->get_type_a_data($batch_data[0]['year'], $batch_data[0]['month'], $batch_data[0]['all_name']);
         $data_b = $this->local_service->get_type_b_data($batch_data[0]['year'], $batch_data[0]['month'], $batch_data[0]['all_name']);

         foreach ($data_a as $d) {
             $data['Y_Amount'] += $d['Amount'];
             $data['Y_Quantity'] ++;
         }

         foreach ($data_b as $d) {
             $data['T_Amount'] += $d['Amount'];
             $data['T_Quantity'] ++;
         }

         $data['all_name'] = $batch_data[0]['all_name'];
         $data['filler_man'] = $batch_data[0]['filler_man'];
         $data['year'] = $batch_data[0]['year'];
         $data['month'] = $batch_data[0]['month'];
         $data['uid'] = $batch_data[0]['uid'];
         $data['ip'] = $batch_data[0]['ip'];


         if ($st) {
             $ret = $TransferFundsStService->update_by_id($st['id'], $data);
             if (!$ret->success) {
                 $this->error('更新转贷资金统计表失败~');
             }
             action_user_log('修改转贷资金统计表');
         } else {
             $ret = $TransferFundsStService->add_one($data);
             if (!$ret->success) {
                 $this->error('添加转贷资金统计表失败~');
             }
             action_user_log('添加转贷资金统计表');
         }

     }

     public function statistics()
     {
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
         $service = '\Common\Service\TransferFundsStService';
         $this->local_service = \Common\Service\TransferFundsStService::get_instance();

         $page = I('get.p', 1);
         $where_all = [];
         $where_all['year'] = $get['year'];
         $where_all['month'] = $get['month'];
         $data_all = $this->local_service->get_by_where_all($where_all);
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $data = $this->convert_data_statistics($data, $data_all);
         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);

         $total = [];
         foreach ($data_all as $data) {
             $total['M_Amount'] += $data['M_Amount'];
             $total['M_Quantity'] += $data['M_Quantity'];
             $total['Y_Amount'] += $data['Y_Amount'];
             $total['Y_Quantity'] += $data['Y_Quantity'];
             $total['T_Amount'] += $data['T_Amount'];
             $total['T_Quantity'] += $data['T_Quantity'];

         }
         $this->assign('total', $total);
         $this->display();
     }

     protected function convert_data_statistics($data, $data_all)
     {
         if ($data) {
             $all_names = result_to_array($data, 'all_name');
             $DepartmentService = \Common\Service\DepartmentService::get_instance();
             $departments = $DepartmentService->get_by_all_names($all_names, \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds);
             $departments_map = result_to_map($departments, 'all_name');
             foreach ($data as $key => $info) {
                 if (isset($departments_map[$info['all_name']])) {
                     $data[$key]['build_time'] = $departments_map[$info['all_name']]['build_time'];
                 } else {
                     $data[$key]['build_time'] = '未知';
                 }
             }
         }
         return $data;



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
        action_user_log('删除转贷资金单位');
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
                                action_user_log('修改转贷资金单位');
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
                                action_user_log('添加转贷资金单位');
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
        foreach ($data as $k=>$v) {
            if (isset($users_map[$v['uid']])) {
                $data[$k]['user'] = $users_map[$v['uid']];
            }
        }
    }



 }