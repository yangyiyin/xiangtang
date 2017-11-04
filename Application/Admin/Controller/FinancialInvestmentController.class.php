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
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment;

         parent::_initialize();
     }

     public function submit_monthly()
     {
         $this->title = '股权投资和创业投资机构单位月填报';

         parent::submit_monthly();
     }

     public function detail_submit_monthly() {
         $this->local_service = \Common\Service\InvestmentDetailsService::get_instance();
         $this->detail_type = \Common\Model\FinancialInvestmentDetailsModel::TYPE_A;
         if (IS_POST) {

         } else {
             $this->title = '股权投资机构投资明细月填报';
             //获取区域
             $AreaService = \Common\Service\AreaService::get_instance();

             $this->assign('area_options', $AreaService->set_area_options());

         }
         parent::detail_submit_monthly();
     }


     protected function get_add_data_detail_submit_monthly($data, $cache_data='') {
         $batch_data = [];

         if ($cache_data) {
             foreach ($cache_data as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Name'] = $v['Name'];
                     $temp['Area'] = $v['Area'];
                     $temp['Amount'] = $v['Amount'];
                     $temp['Remarks'] = $v['Remarks'];
                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
                     $batch_data[] = $temp;
                 }

             }

         }

         return $batch_data;
     }

     protected function convert_data_detail_submit_monthly(&$infos) {
         //获取区域
         $AreaService = \Common\Service\AreaService::get_instance();
         if ($infos) {
             $infos = $AreaService->set_area_options($infos);
         }
         //   var_dump($infos);die();

     }

     public function get_detail_page_html() {

         $p = I('p');
         $all_name = I('all_name');
         $year= I('year');
         $month = I('month');
         //获取明细
         $InvestmentDetailsService = \Common\Service\InvestmentDetailsService::get_instance();
         $_where = [];
         $_where['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_A;
         $_where['all_name'] = $all_name;
         $_where['year'] = $year;
         $_where['month'] = $month;
         $infos = $InvestmentDetailsService->get_by_where_all($_where);
         if ($infos) {
             $data_1_map = [];
             $this->convert_data_detail_submit_monthly($infos);
             foreach ($infos as $da) {
                 $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
             }
         }

         if (isset($data_1_map[$year.'_'.$month.'_'.$all_name])) {
             $data = $data_1_map[$year.'_'.$month.'_'.$all_name];

             $count = count($data);
             $page_size = \Common\Service\InvestmentService::$page_size;

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



         //获取区域
         $AreaService = \Common\Service\AreaService::get_instance();
         $this->assign('area_options', $AreaService->set_area_options());
         $this->display();


     }

     public function get_exit_detail_page_html() {

         $p = I('p');
         $all_name = I('all_name');
         $year= I('year');
         $month = I('month');
         //获取明细
         $InvestmentExitService = \Common\Service\InvestmentExitService::get_instance();
         $_where = [];
         $_where['all_name'] = $all_name;
         $_where['year'] = $year;
         $_where['month'] = $month;
         $infos = $InvestmentExitService->get_by_where_all($_where);
         if ($infos) {
             $data_1_map = [];
             $this->convert_data_exit_detail_submit_monthly($infos);
             foreach ($infos as $da) {
                 $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
             }
         }

         if (isset($data_1_map[$year.'_'.$month.'_'.$all_name])) {
             $data = $data_1_map[$year.'_'.$month.'_'.$all_name];

             $count = count($data);
             $page_size = \Common\Service\InvestmentService::$page_size;

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



         //获取区域
         $AreaService = \Common\Service\AreaService::get_instance();
         $this->assign('area_options', $AreaService->set_area_options());
         $this->display();


     }

     public function exit_detail_submit_monthly() {
         $this->local_service = \Common\Service\InvestmentExitService::get_instance();
         if (IS_POST) {

         } else {
             $this->title = '新增股权投资机构退出明细月报表';
             $exit_method_options = $this->local_service->get_exit_method_options();
             $this->assign('exit_method_options', $exit_method_options);
         }
         parent::detail_submit_monthly();
     }

     protected function get_add_data_exit_detail_submit_monthly($data, $cache_data='') {
         $batch_data = [];

         if ($cache_data) {
             foreach ($cache_data as $k => $v) {
                 if ($v) {
                     $temp = [];
                     $temp['all_name'] = $data['all_name'];
                     $temp['year'] = $data['year'];
                     $temp['month'] = $data['month'];
                     $temp['Types'] = $data['Types'];
                     $temp['uid'] = $data['uid'];
                     $temp['filler_man'] = $data['filler_man'];
                     $temp['gmt_create'] = time();
                     $temp['Name'] = $v['Name'];

                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];

                     $temp['Name'] = $v['Name'];
                     $temp['Startdate'] = $v['Startdate'];
                     $temp['Exitdate'] = $v['Exitdate'];
                     $temp['Investment'] = $v['Investment'];
                     $temp['Recycling'] = $v['Recycling'];
                     $temp['ExitMethod'] = $v['ExitMethod'];

                     $batch_data[] = $temp;

                 }

             }

         }

         return $batch_data;
     }

//
//     /**
//      * 退出明细
//      */
//     public function exit_detail_submit_monthly() {
//         $this->local_service = \Common\Service\InvestmentExitService::get_instance();
//         if (IS_POST) {
//             $id = I('get.id');
//             $data = I('post.');
//             $data['uid'] = UID;
//             if (!$data['logs1']) {
//                 $this->error('请填写完整的信息~');
//             }
//             if (!$this->is_history) {
//                 $data['year'] = intval(date('Y'));
//                 $data['month'] = intval(date('m'));
//             } else {
//                 $time = intval(strtotime($data['year'] . '-' . $data['month']));
//                 if (!$time || $time > strtotime('201712')) {
//                     $this->error('历史数据时间必须小于201712');
//                 }
//             }
//
//             $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name']);
//             if ($ret){
//                 //删除
//                 if ($this->is_history && !$data['force_modify']) {
//                     $this->error('该月报表已经提交,如需修改,请勾选强制修改');
//                 }
//                 $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name']);
//             }
//             $batch_data = [];
//             foreach ($data['logs1'] as $k => $v) {
//                 if ($v) {
//                     $temp = [];
//                     $temp['all_name'] = $data['all_name'];
//                     $temp['year'] = $data['year'];
//                     $temp['month'] = $data['month'];
//                     $temp['Types'] = $data['Types'];
//                     $temp['uid'] = $data['uid'];
//                     $temp['filler_man'] = $data['filler_man'];
//                     $temp['gmt_create'] = time();
//                     $temp['Name'] = $v;
//                     $temp['Startdate'] = isset($data['logs2'][$k]) ? strtotime($data['logs2'][$k]) : 0;
//                     $temp['Exitdate'] = isset($data['logs3'][$k]) ? strtotime($data['logs3'][$k]) : 0;
//                     $temp['Investment'] = isset($data['logs4'][$k]) ? $data['logs4'][$k] : 0;
//                     $temp['Recycling'] = isset($data['logs5'][$k]) ? $data['logs5'][$k] : 0;
//                     $temp['ExitMethod'] = isset($data['logs6'][$k]) ? $data['logs6'][$k] : 0;
//
//
//                     $temp['ip'] = $_SERVER["REMOTE_ADDR"];
//                     $batch_data[] = $temp;
//                 }
//
//             }
//             $ret = $this->local_service->add_batch($batch_data);
//             if ($ret->success) {
//                 action_user_log('新增股权投资机构退出明细月报表');
//                 $this->success('添加成功！');
//             } else {
//                 $this->error($ret->message);
//             }
//         } else {
//             $this->title = '股权投资机构退出明细月填报('. date('Y-m') .'月)';
//             if ($this->is_history) {
//                 $this->title = '股权投资机构退出明细月填报[正在编辑历史数据]';
//             }
//
//             $this->assign('title', $this->title);
//
//             //获取所有相关的公司
//             $DepartmentService = \Common\Service\DepartmentService::get_instance();
//
//             $departments = $DepartmentService->get_my_list(UID, $this->type);
//
//
//             if (!$departments) {
//                 $departments = $DepartmentService->get_all_list($this->type);
//             } else {
//                 $data = $departments[0];
//             }
//             $departments = result_to_array($departments, 'all_name');
//             $this->assign('departments', $departments);
//
//             //获取当期的数据
//             $infos = [];
//             if (!$this->is_history) {
//                 if (isset($data['all_name']) && $data['all_name']) {
//                     $infos = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);
//                     //$this->convert_data_detail_submit_monthly($infos);
//                 }
//             }
//
//             $exit_method_options = $this->local_service->get_exit_method_options();
//             $this->assign('exit_method_options', $exit_method_options);
//             if ($infos) {
//                 $infos = $this->local_service->get_exit_method_options($infos);
//             }
//
//             //获取区域
//             $this->assign('infos', $infos);
//             $this->display();
//         }
//     }

     protected function convert_data_exit_detail_submit_monthly(&$infos) {

         if ($infos) {
             $this->local_service = \Common\Service\InvestmentExitService::get_instance();
             $infos = $this->local_service->get_exit_method_options($infos);
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