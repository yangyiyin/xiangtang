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

     protected function convert_data_statistics(&$data) {
         if ($data) {
             $all_names = result_to_array($data, 'all_name');
             $DepartmentService = \Common\Service\DepartmentService::get_instance();
             $departments = $DepartmentService->get_by_all_names($all_names, \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment);
             $departments_map = result_to_map($departments, 'all_name');
             foreach ($data as $key => $info) {
                 $data[$key]['Staff_Sub'] = explode(',', $info['Staff_Sub']);
                 $data[$key]['capital'] = isset($departments_map[$info['all_name']]['capital']) ? $departments_map[$info['all_name']]['capital'] : '未知';
             }

         }

     }
 }