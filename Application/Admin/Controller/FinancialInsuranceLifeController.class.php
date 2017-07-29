<?php
/**
 * Created by newModule.
 * Time: 2017-07-29 08:11:35
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialInsuranceLifeController extends FinancialBaseController  {
     protected function _initialize() {
         parent::_initialize();
     }

     public function submit_monthly()
     {
         $this->title = '';
         parent::submit_monthly();



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
        $where['type'] = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife;

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
                                    $gid = C('GROUP_Financial'.'InsuranceLife');
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
                            $data['type'] = \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife;
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