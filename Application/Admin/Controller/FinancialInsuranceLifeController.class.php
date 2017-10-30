<?php
/**
 * Created by newModule.
 * Time: 2017-07-30 10:39:53
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialInsuranceLifeController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife;
         parent::_initialize();

     }

     final function submit_monthly()
     {
         $this->title = '人身保险公司月填报';
         parent::submit_monthly();

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
        action_user_log('删除人身保险公司单位');
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
                                action_user_log('修改人身保险公司单位');
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
                                action_user_log('添加人身保险公司单位');
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

     public function convert_data_statistics($data, $data_all) {
         $incomes = result_to_array($data_all, 'income');
         $income_sum = array_sum($incomes);
         array_multisort($incomes, SORT_DESC, $data_all);
         foreach ($data as $key => $value) {
             $data[$key]['percent'] = fix_2($value['income'] / $income_sum);
             foreach ($data_all as $k => $v) {
                 if ($v['id'] == $value['id']) {
                     $data[$key]['sort'] = $k + 1;
                     break;
                 }
             }
         }
         return $data;
     }

 }