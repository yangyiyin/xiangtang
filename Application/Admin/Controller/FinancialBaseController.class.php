<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

use Think\Exception;
use Admin\Model\MemberModel;
use User\Api\UserApi;
class FinancialBaseController extends AdminController {
    protected $title = '';
    protected $local_service;
    protected $local_service_name;
    protected $is_history = false;
    protected function _initialize() {
        parent::_initialize();
        //FinancialInsuranceMutual
        try{
            $service_name = str_replace('Financial', '', CONTROLLER_NAME) . 'Service';
            $service = '\Common\Service\\'.$service_name;
            $this->local_service_name = $service_name;
            if (class_exists($service)) {
                $this->local_service = $service::get_instance();
            }

        } catch (Exception $e) {

        }

        if (ACTION_NAME == 'index') {
            $group_options = '';
            $group_cats = D('GroupCat')->where(['cid'=>$this->type])->select();

            $gids = result_to_array($group_cats, 'gid');
            $group_options = '';
            if ($gids) {
                $groups = D('AuthGroup')->where(['id' => ['in',$gids],'module'=>'admin', 'status'=>1])->select();

                if ($groups) {
                    foreach ($groups as $_group) {
                        $group_options .= '<option value="'.$_group['id'].'">'.$_group['title'].'</option>';
                    }
                }


            }

            $this->assign('group_options', $group_options);
        }

    }

    public function submit_monthly() {
        $this->assign('title', $this->title);

        //获取所有相关的公司
        $DepartmentService = \Common\Service\DepartmentService::get_instance();

        $departments = $DepartmentService->get_my_list(UID, $this->type);

        $all_name = '';
        if (!$departments) {
            $departments = $DepartmentService->get_all_list($this->type);
        }
        $data = $departments[0];
        $all_name = $data['all_name'];
        $all_name = I('all_name') ? I('all_name') : $all_name;

        $departments = result_to_array($departments, 'all_name');
        $this->assign('departments', $departments);

        //获取当期的数据
        $info = [];

        if (!$this->is_history) {
            if ($all_name) {
                $year = I('year') ? I('year') : intval(date('Y'));
                $month = I('month') ? I('month') : intval(date('m'));
                $info = $this->local_service->get_by_month_year($year, $month, $all_name);
                $this->convert_data_submit_monthly($info);
            }
        }
        $this->assign('info', $info);
    }

    protected function convert_data_submit_monthly(&$info) {

    }
    public function statistics() {
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
        list($data, $count) = $this->local_service->get_by_where($where, 'income desc', $page);
        $data = $this->convert_data_statistics($data, $data_all);
        $PageInstance = new \Think\Page($count, $service::$page_size);
        if($total>$service::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);
    }

    public function add_unit() {
        $this->assign('title', $this->title);



    }
    public function check_by_month_year($year, $month, $all_name) {
        if (!$year || !$month || !$all_name) {
            return false;
        }
        $ret = $this->local_service->get_by_month_year($year, $month, $all_name);

        if ($ret) {
            return $ret;
        }
        return true;
    }

    public function add_history() {
        $this->is_history = true;
        $this->submit_monthly();
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

    protected function convert_data_submit_log($data) {
        //子类实现

    }
    protected function convert_data_statistics($data, $data_all) {
        //子类实现
        return $data;
    }

    public function add_user() {

        $data = I('post.');
        if ($data['id']) {//修改
            $MemberService = \Common\Service\MemberService::get_instance();
            $data_update = [];
            $data_update['entity_tel'] = $data['entity_tel'];
            $MemberService->update_by_id($data['id'], $data_update);

            $gid = $data['gid'];
            $AuthGroup = D('AuthGroup');
            if( $gid && !$AuthGroup->checkGroupId($gid)){
                $this->error($AuthGroup->error);
            }

            $AuthGroup->removeFromGroup($data['id'], $gid);
            if ( $AuthGroup->addToGroup($data['id'],$gid) ){
                $this->success('修改成功');
            }else{
                $this->error($AuthGroup->getError());
            }

        } else {//新增
            $password = '123456';
            $username = $data['username'];
            if (!$username) {
                $this->error('后台登录名不能为空');
            }
            if (!$data['gid']) {
                $this->error('请选择组');
            }
            /* 调用注册接口注册用户 */
            $User   =   new UserApi();
            $uid    =   $User->register($username, $password, '');
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'entity_tel'=>$data['entity_tel'], 'status' => 1, 'reg_time' => time());
                if(!M('Member')->add($user)){
                    $this->error('添加失败！');
                } else {
                    $gid = $data['gid'];
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

                    //添加部门和uid的联系
                    $data_department_uid = [];
                    $data_department_uid['did'] = $data['did'];
                    $data_department_uid['uid'] = $uid;
                    D('FinancialDepartmentUid')->add($data_department_uid);

                    $this->success('添加成功');

                }
            } else { //注册失败，显示错误信息
                $this->error('添加失败!'.$uid.',登录名可能重复,请重试');
            }
        }


    }
}