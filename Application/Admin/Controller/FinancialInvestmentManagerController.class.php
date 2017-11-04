<?php
/**
 * Created by newModule.
 * Time: 2017-08-01 08:17:41
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialInvestmentManagerController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager;

         parent::_initialize();
     }

     public function submit_monthly()
     {
         $this->title = '股权投资管理机构单位月填报';

         parent::submit_monthly();

     }

     protected function convert_data_submit_monthly(&$info) {
         if ($info) {
             $info['Staff_Sub'] = explode(',', $info['Staff_Sub']);
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
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentModel::TYPE_B];
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $this->convert_data_statistics($data);
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


    public function detail_submit_monthly() {
        $this->local_service = \Common\Service\InvestmentDetailsService::get_instance();
        $this->detail_type = \Common\Model\FinancialInvestmentDetailsModel::TYPE_B;
        if (IS_POST) {

        } else {
            $this->title = '股权投资管理机构所管理公司明细月填报';
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
        $_where['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_B;
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
             $page_size = \Common\Service\InvestmentManagerService::$page_size;

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
        action_user_log('删除股权投资管理机构单位');
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
            //var_dump($data);
                        if ($id) {

                            $ret = $this->local_service->update_by_id($id, $data);
                            if ($ret->success) {
                                action_user_log('修改股权投资管理机构单位');
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
                                action_user_log('添加股权投资管理机构单位');
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
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentModel::TYPE_B];
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
         $where['Types'] = ['eq', \Common\Model\FinancialInvestmentDetailsModel::TYPE_B];
         list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
         $data = $this->convert_data_detail_log($data);
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

     protected function convert_data_submit_log(&$data) {
         if ($data) {
             foreach ($data as $key => $info) {
                 $data[$key]['Staff_Sub'] = explode(',', $info['Staff_Sub']);
             }

         }

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

     protected function convert_data_statistics(&$data) {
         if ($data) {
             $all_names = result_to_array($data, 'all_name');
             $DepartmentService = \Common\Service\DepartmentService::get_instance();
             $departments = $DepartmentService->get_by_all_names($all_names, \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager);
             $departments_map = result_to_map($departments, 'all_name');
             foreach ($data as $key => $info) {
                 $data[$key]['Staff_Sub'] = explode(',', $info['Staff_Sub']);
                 $data[$key]['capital'] = isset($departments_map[$info['all_name']]['capital']) ? $departments_map[$info['all_name']]['capital'] : '未知';
             }

         }

     }
 }