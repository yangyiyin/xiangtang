<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 09:09:56
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialLeaseController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease;
         parent::_initialize();

     }

//     public function submit_monthly()
//     {
//                if (IS_POST) {
//                     $id = I('get.id');
//                     $data = I('post.');
//                     $data['uid'] = UID;
//
//                     if (!$this->is_history) {
//                         $data['year'] = intval(date('Y'));
//                         $data['month'] = intval(date('m'));
//                     } else {
//                         $time = intval(strtotime($data['year'] . '-' . $data['month']));
//                         if (!$time || $time > strtotime('201712')) {
//                             $this->error('历史数据时间必须小于201712');
//                         }
//                     }
//                     if ($id) {
//                         $ret = $this->local_service->update_by_id($id, $data);
//                         if ($ret->success) {
//                             action_user_log('修改融资租赁单位月报表');
//                             $this->success('修改成功！');
//                         } else {
//                             $this->error($ret->message);
//                         }
//                     } else {
//                        $check_ret = $this->check_by_month_year($data['year'], $data['month'], $data['all_name']);
//                         if ($check_ret === true){
//                            //新增 不做处理
//                         } elseif($check_ret) {
//                             if ($data['force_modify']) {//强制修改
//                                $id = $check_ret['id'];
//                                 $ret = $this->local_service->update_by_id($id, $data);
//                                 if ($ret->success) {
//                                     action_user_log('修改融资租赁单位月报表');
//                                     $this->success('修改成功！');
//                                 } else {
//                                     $this->error($ret->message);
//                                 }
//                             } else {
//                                 $this->error('该月已提交报表,请不要重复提交');
//                             }
//                         } else {
//                             $this->error('参数错误');
//                         }
//                         //var_dump($data);die();
//                         $ret = $this->local_service->add_one($data);
//                         if ($ret->success) {
//                             action_user_log('新增融资租赁单位月报表');
//                             $this->success('添加成功！');
//                         } else {
//                             $this->error($ret->message);
//                         }
//                     }
//                 } else {
//                     $this->title = '融资租赁单位月填报('. date('Y-m') .'月)';
//                     if ($this->is_history) {
//                         $this->title = '融资租赁单位月填报[正在编辑历史数据]';
//                     }
//
//                     parent::submit_monthly();
//
//                     $this->display();
//                 }
//     }
     final function submit_monthly()
     {
         $this->title = '融资租赁月填报';
         parent::submit_monthly();

     }

     public function statistics()
     {
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
         $service = '\Common\Service\\'.$this->local_service_name;
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

         //获取平均值
         $average = [];
         $total = [];
         foreach ($data_all as $data) {
             $total['Assets_M'][] = $data['Assets_M'];
             $total['Assets_Y'][] = $data['Assets_Y'];
             $total['Business_Stay'][] = $data['Business_Stay'];
             $total['Business_M_New'][] = $data['Business_M_New'];
             $total['Business_Y_New'][] = $data['Business_Y_New'];
             $total['Business_T_New'][] = $data['Business_T_New'];
             $total['Client_Stay'][] = $data['Client_Stay'];
             $total['Client_M_New'][] = $data['Client_M_New'];
             $total['Client_Y_New'][] = $data['Client_Y_New'];
             $total['Client_T_New'][] = $data['Client_T_New'];
             $total['Business_C1'][] = $data['Business_C1'];
             $total['Business_C2'][] = $data['Business_C2'];
             $total['Business_C3'][] = $data['Business_C3'];
             $total['Profit_AY'][] = $data['Profit_AY'];
             $total['Profit_BY'][] = $data['Profit_BY'];
             $total['Profit_CY'][] = $data['Profit_CY'];
             $total['Profit_DY'][] = $data['Profit_DY'];
         }

         $average['Assets_M'] = fix_2_float(array_sum($total['Assets_M']) / count($total['Assets_M']));
         $average['Assets_Y'] = fix_2_float(array_sum($total['Assets_Y']) / count($total['Assets_Y']));
         $average['Business_Stay'] = fix_2_float(array_sum($total['Business_Stay']) / count($total['Business_Stay']));
         $average['Business_M_New'] = fix_2_float(array_sum($total['Business_M_New']) / count($total['Business_M_New']));
         $average['Business_Y_New'] = fix_2_float(array_sum($total['Business_Y_New']) / count($total['Business_Y_New']));
         $average['Business_T_New'] = fix_2_float(array_sum($total['Business_T_New']) / count($total['Business_T_New']));
         $average['Client_Stay'] = fix_2_float(array_sum($total['Client_Stay']) / count($total['Client_Stay']));
         $average['Client_M_New'] = fix_2_float(array_sum($total['Client_M_New']) / count($total['Client_M_New']));
         $average['Client_Y_New'] = fix_2_float(array_sum($total['Client_Y_New']) / count($total['Client_Y_New']));
         $average['Client_T_New'] = fix_2_float(array_sum($total['Client_T_New']) / count($total['Client_T_New']));
         $average['Business_C1'] = fix_2_float(array_sum($total['Business_C1']) / count($total['Business_C1']));
         $average['Business_C2'] = fix_2_float(array_sum($total['Business_C2']) / count($total['Business_C2']));
         $average['Business_C3'] = fix_2_float(array_sum($total['Business_C3']) / count($total['Business_C3']));
         $average['Profit_AY'] = fix_2_float(array_sum($total['Profit_AY']) / count($total['Profit_AY']));
         $average['Profit_BY'] = fix_2_float(array_sum($total['Profit_BY']) / count($total['Profit_BY']));
         $average['Profit_CY'] = fix_2_float(array_sum($total['Profit_CY']) / count($total['Profit_CY']));
         $average['Profit_DY'] = fix_2_float(array_sum($total['Profit_DY']) / count($total['Profit_DY']));


         $this->assign('average', $average);
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
        action_user_log('删除融资租赁单位');
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
                                action_user_log('修改融资租赁单位');
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
                                action_user_log('添加融资租赁单位');
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
        }
    }



 }