<?php
/**
 * Created by newModule.
 * Time: 2017-07-30 16:16:37
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialInsuranceMutualController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual;
         parent::_initialize();

     }

     final function submit_monthly()
     {
         $this->title = '保险互助社月填报';
         parent::submit_monthly();

     }

     public function statistics()
     {
         $this->title = '';
         $get = I('get.');
         $where = [];
         if ($get['all_name']) {
             $where['all_name'][] = ['LIKE', '%' . $get['all_name'] . '%'];
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

         $where_extra = [];
         if (isset($type)) {
             //排除非审核通过的单位
             $VerifyService = \Common\Service\VerifyService::get_instance();
             $where_verify = [];
             $where_verify['type'] = $type;
             $where_verify['year'] = $where['year'];
             $where_verify['month'] = $where['month'];
             $where_verify['status'] = ['neq', 2];
             $verifies = $VerifyService->get_by_where_all($where_verify);
             if ($verifies) {
                 $all_nams = result_to_array($verifies, 'all_name');
                 $where['all_name'][] = ['not in', $all_nams];
                 $where_extra['all_name'] = ['not in', $all_nams];
             }

         }


         if (method_exists($this, 'gain_statistics')) {
             $this->gain_statistics($get['year'], $get['month'], $this->type, $where_extra);//自动生成统计
         }



         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         //获取年度和历史累计
         $InsuranceMutualStService = \Common\Service\InsuranceMutualStService::get_instance();
         $st_all = $InsuranceMutualStService->get_all_by_month_year($where['year'], $where['month']);
         $st_all_map = result_to_complex_map($st_all, 'type');
         $type_a = \Common\Model\FinancialInsuranceMutualStModel::TYPE_A;
         $type_b = \Common\Model\FinancialInsuranceMutualStModel::TYPE_B;
         $st_all_a_map = isset($st_all_map[$type_a]) ? $st_all_map[$type_a] : [];
         $st_all_b_map = isset($st_all_map[$type_b]) ? $st_all_map[$type_b] : [];
         $data = $this->convert_data_statistics($data, $st_all_a_map, $st_all_b_map);

         $PageInstance = new \Think\Page($count, $service::$page_size);
         if($total>$service::$page_size){
             $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         }
         $page_html = $PageInstance->show();

         $this->assign('list', $data);
         $this->assign('page_html', $page_html);


         $this->display();
     }

     protected function get_statistics_datas($year, $month) {

         $where_all = [];
         $where_all['year'] = $year;
         $where_all['month'] = $month;

         $VerifyService = \Common\Service\VerifyService::get_instance();
         if ($this->verify_type) {
             $type = $this->verify_type;
         } else {
             $type = $VerifyService->get_type($this->type);
         }


         if (isset($type)) {
             //排除非审核通过的单位
             $VerifyService = \Common\Service\VerifyService::get_instance();
             $where_verify = [];
             $where_verify['type'] = $type;
             $where_verify['year'] = $where_all['year'];
             $where_verify['month'] = $where_all['month'];
             $where_verify['status'] = ['neq', 2];
             $verifies = $VerifyService->get_by_where_all($where_verify);
             if ($verifies) {
                 $all_nams = result_to_array($verifies, 'all_name');
                 $where_all['all_name'] = ['not in', $all_nams];
             }

         }

         $data_all = $this->local_service->get_by_where_all($where_all);
         //获取年度和历史累计
         $InsuranceMutualStService = \Common\Service\InsuranceMutualStService::get_instance();
         $st_all = $InsuranceMutualStService->get_all_by_month_year($where_all['year'], $where_all['month']);
         $st_all_map = result_to_complex_map($st_all, 'type');
         $type_a = \Common\Model\FinancialInsuranceMutualStModel::TYPE_A;
         $type_b = \Common\Model\FinancialInsuranceMutualStModel::TYPE_B;
         $st_all_a_map = isset($st_all_map[$type_a]) ? $st_all_map[$type_a] : [];
         $st_all_b_map = isset($st_all_map[$type_b]) ? $st_all_map[$type_b] : [];
         $data = $this->convert_data_statistics($data_all, $st_all_a_map, $st_all_b_map);

         return $data;
     }

     protected function convert_data_statistics($data, $data_a, $data_b) {
         if ($data) {
             $data_a_map = result_to_map($data_a, 'all_name');
             $data_b_map = result_to_map($data_b, 'all_name');
             foreach ($data as $key => $value) {
                 $data[$key]['total_a'] = $value['Life_A'] + $value['Casualty_A'] + $value['Medical_A'];
                 $data[$key]['total_b'] = $value['Life_B'] + $value['Casualty_B'] + $value['Medical_B'];
                 $data[$key]['total_c'] = $value['Life_C'] + $value['Casualty_C'] + $value['Medical_C'];
                 $data[$key]['total_d'] = $value['Life_D'] + $value['Casualty_D'] + $value['Medical_D'];
                 $data[$key]['total_e'] = $value['Life_E'] + $value['Casualty_E'] + $value['Medical_E'];
                 $data[$key]['total_f'] = $value['Life_F'] + $value['Casualty_F'] + $value['Medical_F'];
                 $data[$key]['st_a'] = isset($data_a_map[$value['all_name']]) ? $data_a_map[$value['all_name']] : [];
                 $data[$key]['st_a']['total_a'] = $data[$key]['st_a']['Life_A'] + $data[$key]['st_a']['Casualty_A'] + $data[$key]['st_a']['Medical_A'];
                 $data[$key]['st_a']['total_b'] = $data[$key]['st_a']['Life_B'] + $data[$key]['st_a']['Casualty_B'] + $data[$key]['st_a']['Medical_B'];
                 $data[$key]['st_a']['total_c'] = $data[$key]['st_a']['Life_C'] + $data[$key]['st_a']['Casualty_C'] + $data[$key]['st_a']['Medical_C'];
                 $data[$key]['st_a']['total_d'] = $data[$key]['st_a']['Life_D'] + $data[$key]['st_a']['Casualty_D'] + $data[$key]['st_a']['Medical_D'];
                 $data[$key]['st_a']['total_e'] = $data[$key]['st_a']['Life_E'] + $data[$key]['st_a']['Casualty_E'] + $data[$key]['st_a']['Medical_E'];
                 $data[$key]['st_a']['total_f'] = $data[$key]['st_a']['Life_F'] + $data[$key]['st_a']['Casualty_F'] + $data[$key]['st_a']['Medical_F'];

                 $data[$key]['st_b'] = isset($data_b_map[$value['all_name']]) ? $data_b_map[$value['all_name']] : [];
                 $data[$key]['st_b']['total_a'] = $data[$key]['st_b']['Life_A'] + $data[$key]['st_b']['Casualty_A'] + $data[$key]['st_b']['Medical_A'];
                 $data[$key]['st_b']['total_b'] = $data[$key]['st_b']['Life_B'] + $data[$key]['st_b']['Casualty_B'] + $data[$key]['st_b']['Medical_B'];
                 $data[$key]['st_b']['total_c'] = $data[$key]['st_b']['Life_C'] + $data[$key]['st_b']['Casualty_C'] + $data[$key]['st_b']['Medical_C'];
                 $data[$key]['st_b']['total_d'] = $data[$key]['st_b']['Life_D'] + $data[$key]['st_b']['Casualty_D'] + $data[$key]['st_b']['Medical_D'];
                 $data[$key]['st_b']['total_e'] = $data[$key]['st_b']['Life_E'] + $data[$key]['st_b']['Casualty_E'] + $data[$key]['st_b']['Medical_E'];
                 $data[$key]['st_b']['total_f'] = $data[$key]['st_b']['Life_F'] + $data[$key]['st_b']['Casualty_F'] + $data[$key]['st_b']['Medical_F'];


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
        action_user_log('删除保险互助社单位');
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
                                action_user_log('修改保险互助社单位');
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
                                action_user_log('添加保险互助社单位');
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

     /**
      * 生成历史数据和本年数据
      * @param $data
      */
    protected function update_st($data) {
        $service = \Common\Service\InsuranceMutualStService::get_instance();
        $ret_a = $service->get_by_month_year($data['year'], $data['month'], $data['all_name'], \Common\Model\FinancialInsuranceMutualStModel::TYPE_A);
        $ret_b = $service->get_by_month_year($data['year'], $data['month'], $data['all_name'], \Common\Model\FinancialInsuranceMutualStModel::TYPE_B);


        $data_a = $this->get_data_st_a($data);
        $data_b = $this->get_data_st_b($data);


        if ($ret_a && $data_a) {
            $ret = $service->update_by_id($ret_a['id'], $data_a);
            if (!$ret->success) {
                //$this->error('更新年度保险互助社统计表失败~');
            }
            action_user_log('修改年度保险互助社统计表');
        } else {
            $ret = $service->add_one($data_a);
            if (!$ret->success) {
                $this->error('新增年度保险互助社统计表失败~');
            }
            action_user_log('新增年度保险互助社统计表');
        }


        if ($ret_b && $data_b) {
            $ret = $service->update_by_id($ret_b['id'], $data_b);
            if (!$ret->success) {
                //$this->error('修改历史保险互助社统计表失败~');
            }
            action_user_log('修改历史保险互助社统计表');

        } else {
            $ret = $service->add_one($data_b);
            if (!$ret->success) {
                $this->error('新增历史保险互助社统计表失败~');
            }
            action_user_log('新增历史保险互助社统计表');
        }

    }

    protected function get_data_st_a($data) {
        $ret = $this->local_service->get_type_a_data($data['year'], $data['month'], $data['all_name']);
        $data_a = [];
        if ($ret) {
            $data_a['uid'] = $data['uid'];
            $data_a['all_name'] = $data['all_name'];
            $data_a['year'] = $data['year'];
            $data_a['month'] = $data['month'];
            $data_a['filler_man'] = $data['filler_man'];
            $data_a['type'] = \Common\Model\FinancialInsuranceMutualStModel::TYPE_A;
            $data_a['ip'] = $_SERVER["REMOTE_ADDR"];
            foreach ($ret as $value) {
                $data_a['Life_A'] += $value['Life_A'];
                $data_a['Life_B'] += $value['Life_B'];
                $data_a['Life_C'] += $value['Life_C'];
                $data_a['Life_D'] += $value['Life_D'];
                $data_a['Life_E'] += $value['Life_E'];
                $data_a['Life_F'] += $value['Life_F'];
                $data_a['Casualty_A'] += $value['Casualty_A'];
                $data_a['Casualty_B'] += $value['Casualty_B'];
                $data_a['Casualty_C'] += $value['Casualty_C'];
                $data_a['Casualty_D'] += $value['Casualty_D'];
                $data_a['Casualty_E'] += $value['Casualty_E'];
                $data_a['Casualty_F'] += $value['Casualty_F'];
                $data_a['Medical_A'] += $value['Medical_A'];
                $data_a['Medical_B'] += $value['Medical_B'];
                $data_a['Medical_C'] += $value['Medical_C'];
                $data_a['Medical_D'] += $value['Medical_D'];
                $data_a['Medical_E'] += $value['Medical_E'];
                $data_a['Medical_F'] += $value['Medical_F'];

            }
        }
        return $data_a;
    }

     protected function get_data_st_b($data) {
         $ret = $this->local_service->get_type_b_data($data['year'], $data['month'], $data['all_name']);

         $data_b = [];
         if ($ret) {
             $data_b['uid'] = $data['uid'];
             $data_b['all_name'] = $data['all_name'];
             $data_b['year'] = $data['year'];
             $data_b['month'] = $data['month'];
             $data_b['filler_man'] = $data['filler_man'];
             $data_b['type'] = \Common\Model\FinancialInsuranceMutualStModel::TYPE_B;
             $data_b['ip'] = $_SERVER["REMOTE_ADDR"];
             foreach ($ret as $value) {
                 $data_b['Life_A'] += $value['Life_A'];
                 $data_b['Life_B'] += $value['Life_B'];
                 $data_b['Life_C'] += $value['Life_C'];
                 $data_b['Life_D'] += $value['Life_D'];
                 $data_b['Life_E'] += $value['Life_E'];
                 $data_b['Life_F'] += $value['Life_F'];
                 $data_b['Casualty_A'] += $value['Casualty_A'];
                 $data_b['Casualty_B'] += $value['Casualty_B'];
                 $data_b['Casualty_C'] += $value['Casualty_C'];
                 $data_b['Casualty_D'] += $value['Casualty_D'];
                 $data_b['Casualty_E'] += $value['Casualty_E'];
                 $data_b['Casualty_F'] += $value['Casualty_F'];
                 $data_b['Medical_A'] += $value['Medical_A'];
                 $data_b['Medical_B'] += $value['Medical_B'];
                 $data_b['Medical_C'] += $value['Medical_C'];
                 $data_b['Medical_D'] += $value['Medical_D'];
                 $data_b['Medical_E'] += $value['Medical_E'];
                 $data_b['Medical_F'] += $value['Medical_F'];

             }
         }
         return $data_b;
     }

 }