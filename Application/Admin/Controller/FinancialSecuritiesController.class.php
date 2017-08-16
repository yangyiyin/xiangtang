<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 16:31:36
 */
 namespace Admin\Controller;
 use Admin\Model\MemberModel;
 use User\Api\UserApi;
 class FinancialSecuritiesController extends FinancialBaseController  {
     protected function _initialize() {
         $this->type = \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities;
         parent::_initialize();

     }

     public function submit_monthly()
     {
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
                             action_user_log('修改证券营业部单位月报表');
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
                                     action_user_log('修改证券营业部单位月报表');
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
                             action_user_log('新增证券营业部单位月报表');
                             $this->success('添加成功！');
                         } else {
                             $this->error($ret->message);
                         }
                     }
                 } else {
                     $this->title = '证券营业部单位月填报('. date('Y-m') .'月)';
                     if ($this->is_history) {
                         $this->title = '证券营业部单位月填报[正在编辑历史数据]';
                     }

                     parent::submit_monthly();

                     $this->display();
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
        action_user_log('删除证券营业部单位');
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
                                action_user_log('修改证券营业部单位');
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
                                action_user_log('添加证券营业部单位');
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