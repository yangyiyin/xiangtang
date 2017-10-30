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
    protected $verify_info = [];
    protected $detail_type = '';
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

        $this->type_map = \Common\Model\FinancialDepartmentModel::$TYPE_MAP;
        $this->type_name = $this->type_map[$this->type];

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
        $can_all_edit = $this->check_rule('Admin/'.$this->type_name.'/submit_monthly_all');
        $this->assign('can_all_edit', $can_all_edit);

        //获取所有相关的公司
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $departments = $DepartmentService->get_my_list(UID, $this->type);
        $all_name = '';
        if (!$departments && !$can_all_edit) {
            $this->error('找不到您所属的部门信息');
        }

        $data = $departments[0];
        $all_name = $data['all_name'];

        if ($can_all_edit) {
            $departments = $DepartmentService->get_all_list($this->type);
            $this->assign('departments',result_to_array($departments,'all_name'));
            $all_name = I('all_name');
        }


        $year = I('year');
        $month = I('month');

        //获取编辑数据
        $info = [];
        if ($year && $month && !IS_POST) {
            $info = $this->local_service->get_by_month_year($year,$month,$all_name);
            if (!$info || $info['all_name'] != $all_name) {
                $this->error('您没有权限查看该部门的信息');
            }
            $this->convert_data_submit_monthly($info);
        }

        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
        $this->verify_info = $VerifyService->get_info($year,$month,$all_name,$type);

        if (isset($this->verify_info['status']) && $this->verify_info['status'] != \Common\Model\FinancialVerifyModel::STATUS_INIT) {
            $this->error('当前信息已存在或不可编辑');
        }
        $jump_url = '';
        if (IS_POST) {

            $id = I('get.id');
            $data = I('post.');
            $data['uid'] = UID;

            $data['year'] = I('year') ? I('year') : intval(date('Y'));
            $data['month'] = I('month') ? I('month') : intval(date('m'));

            if (strtotime($data['year'].'-'.$data['month']) > strtotime(date('Y-m',time()))){
                $this->error('该月份还不能填报!');
            }

            if ($id) {
                $ret = $this->local_service->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改月报表type:'.$this->type.'--id:'.$id);

                } else {
                    $this->error($ret->message);
                }
            } else {
                $check_ret = $this->check_by_month_year($data['year'], $data['month'], $data['all_name']);
                if ($check_ret === true){
                    //新增 不做处理
                } elseif($check_ret) {
                    $this->error('该月已提交报表,请不要重复提交');
                } else {
                    $this->error('参数错误');
                }
                $ret = $this->local_service->add_one($data);
                if ($ret->success) {

                    $jump_url = U('index_list');
                    if ($can_all_edit) {
                        $jump_url = U('index_all_list');
                    }
                    action_user_log('新增月报表type:'.$this->type);

                } else {
                    $this->error($ret->message);
                }
            }

            if(I('post.submit_verify')) {
                $ret = $this->_submit_verify($this->verify_info, $data['year'], $data['month'], $all_name, $type,I('post.submit_verify'));//提交审核
                if (!$ret->success) {
                    $this->error($ret->message);
                } else {
                    $jump_url = U('index_list');
                    if ($can_all_edit) {
                        $jump_url = U('index_all_list');
                    }
                    $this->success('提交成功！',$jump_url);
                }
            } else {
                $ret = $this->_submit_verify($this->verify_info, $data['year'], $data['month'], $all_name, $type);//提交审核

                $this->success('保存成功！',$jump_url);
            }

        } else {
            $this->assign('title', $this->title);
            $this->assign('all_name', $all_name);
            $this->assign('info', $info);
            $this->display();
        }

    }


    public function detail_submit_monthly() {
        $can_all_edit = $this->check_rule('Admin/'.$this->type_name.'/submit_monthly_all');
        $this->assign('can_all_edit', $can_all_edit);

        //获取所有相关的公司
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $departments = $DepartmentService->get_my_list(UID, $this->type);
        $all_name = '';
        if (!$departments && !$can_all_edit) {
            $this->error('找不到您所属的部门信息');
        }

        $data = $departments[0];
        $all_name = $data['all_name'];

        if ($can_all_edit) {
            $departments = $DepartmentService->get_all_list($this->type);
            $this->assign('departments',result_to_array($departments,'all_name'));
            $all_name = I('all_name');
        }


        $year = I('year');
        $month = I('month');

        //获取编辑数据
        $info = [];
        if ($year && $month && !IS_POST) {
            $infos = $this->local_service->get_by_month_year($year,$month,$all_name,$this->detail_type);
            if (!$infos || $infos[0]['all_name'] != $all_name) {
                $this->error('您没有权限查看该部门的信息');
            }
            $function_name = 'convert_data_'. ACTION_NAME;
            $this->$function_name($infos);
        }



        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
        $this->verify_info = $VerifyService->get_info($year,$month,$all_name,$type);

        if (isset($this->verify_info['status']) && $this->verify_info['status'] != \Common\Model\FinancialVerifyModel::STATUS_INIT) {
            $this->error('当前信息已存在或不可编辑');
        }
        $jump_url = '';
        if (IS_POST) {

            $id = I('get.id');
            $data = I('post.');
            $data['uid'] = UID;
            $data['Types'] = $this->detail_type;

            $data['year'] = I('year') ? I('year') : intval(date('Y'));
            $data['month'] = I('month') ? I('month') : intval(date('m'));

            if (strtotime($data['year'].'-'.$data['month']) > strtotime(date('Y-m',time()))){
                $this->error('该月份还不能填报!');
            }
            if (!$data['logs1']) {
                $this->error('请填写完整的信息~');
            }

            $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);

            if ($ret) {
                $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);
            } else {
                $jump_url = U('index_list');
                if ($can_all_edit) {
                    $jump_url = U('index_all_list');
                }
            }
            $function_name = 'get_add_data_'. ACTION_NAME;
            $this->$function_name($info);
            $batch_data = $this->$function_name($data);
            $ret = $this->local_service->add_batch($batch_data);

            if ($ret->success) {

                action_user_log('编辑明细报表type:'.$this->type.','.ACTION_NAME);

            } else {
                $this->error($ret->message);
            }


            if(I('post.submit_verify')) {
                $ret = $this->_submit_verify($this->verify_info, $data['year'], $data['month'], $all_name, $type,I('post.submit_verify'));//提交审核
                if (!$ret->success) {
                    $this->error($ret->message);
                } else {
                    $jump_url = U('index_list');
                    if ($can_all_edit) {
                        $jump_url = U('index_all_list');
                    }
                    $this->success('提交成功！',$jump_url);
                }
            } else {
                $ret = $this->_submit_verify($this->verify_info, $data['year'], $data['month'], $all_name, $type);//提交审核
                $this->success('保存成功！',$jump_url);
            }

        } else {
            $this->assign('title', $this->title);
            $this->assign('all_name', $all_name);
            $this->assign('infos', $infos);
            $this->display();
        }

    }


    protected function convert_data_submit_monthly(&$info) {

    }
    protected function convert_data_detail_submit_monthly(&$info) {

    }
    public function statistics() {
        $this->assign('title', $this->title);
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
        $where_all = [];
        $where_all['year'] = $get['year'];
        $where_all['month'] = $get['month'];


        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty) {
            $type = \Common\Model\FinancialVerifyModel::TYPE_Insurance_PROP;
        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife) {
            $type = \Common\Model\FinancialVerifyModel::TYPE_Insurance_LIFE;
        }
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
            }

        }


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

    public function verify() {

        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty) {
            $type = \Common\Model\FinancialVerifyModel::TYPE_Insurance_PROP;
        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife) {
            $type = \Common\Model\FinancialVerifyModel::TYPE_Insurance_LIFE;
        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual) {
            $type = \Common\Model\FinancialVerifyModel::TYPE_Insurance_Mutual;
        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank) {
            $type = I('type') ? I('type') : 1;
        } else {
            $this->error('您访问的模块未开发');
        }


        $all_name = I('all_name');
        $year = I('year') ? I('year') : intval(date('Y'));
        $month = I('month') ? I('month') : intval(date('m'));
        $p = I('p') ? I('p') : 1;
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $where = [];
        if ($all_name) {
            $where['all_name'] = ['like', '%'.$all_name.'%'];
        }
        $where['year'] = $year;
        $where['month'] = $month;
        $where['type'] = $type;
        $where['status'] = ['in', [1,2]];

        list($list, $count) = $VerifyService->get_by_where($where, 'status asc, gmt_create asc', $p);
        $page_size = \Common\Service\VerifyService::$page_size;
        $PageInstance = new \Think\Page($count, $page_size);
        if($count>$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('page_html', $page_html);
        $this->assign('list', $list);

        $this->display();
    }

    public function verify_approve() {
        $id = I('id');
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $data = [];
        $data['status'] = 2;
        $ret = $VerifyService->update_by_id($id, $data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('操作成功!');

    }

    public function verify_reject() {
        $id = I('id');
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $data = [];
        $data['status'] = 0;
        $ret = $VerifyService->update_by_id($id, $data);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('操作成功!');

    }

    public function check_submit_log() {
        $all_name = I('all_name');
        $year = I('year') ? I('year') : intval(date('Y'));
        $month = I('month') ? I('month') : intval(date('m'));
        $type = I('type');

        if ($type == 1) {
            $this->local_service = \Common\Service\BankCreditNewService::get_instance();
        } elseif ($type == 2) {
            $this->local_service = \Common\Service\BankBaddebtNewService::get_instance();
        } elseif ($type == 3) {
            $this->local_service = \Common\Service\BankBaddebtDetailNewService::get_instance();
        } elseif ($type == 4) {
            $this->local_service = \Common\Service\BankBaddebtDisposeNewService::get_instance();
        } elseif ($type == 5) {
            $this->local_service = \Common\Service\BankFocusDetailNewService::get_instance();
        } elseif ($type == 6) {
            $this->local_service = \Common\Service\BankQuaterlyQuantityANewService::get_instance();
        } elseif ($type == 7) {
            $this->local_service = \Common\Service\BankQuaterlyQuantityBNewService::get_instance();
        } elseif ($type == 8) {
            $this->local_service = \Common\Service\BankQuaterlyQuantityCNewService::get_instance();
        }
        $tpl = '';
        if ($type) {
            $tpl = 'check_submit_log'.$type;
        }

        $info = $this->local_service->get_by_month_year($year,$month,$all_name);

        //明细类
        if ($type && in_array($type, [3,4,5])) {
            $count = count($info);
            $page_size = 20;
            $PageInstance = new \Think\Page($count, $page_size);
            if($count>$page_size){
                $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $page_html = $PageInstance->show();

            $this->assign('page_html', $page_html);
            $page = I('p') ? I('p') : 1;
            $info = array_slice($info, $page_size * ($page-1), $page_size);
        }

        $this->assign('info',$info);
        $this->display($tpl);

    }

    public function index_list() {
        $list = [];
        $p = I('p',1);
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $my_list = $DepartmentService->get_my_list(UID,$this->type);
        if (!$my_list) {
            $this->error('找不到您所属的部门信息');
        }

        $my_department = $my_list[0];


        $where = [];
        if ($year = intval(I('year'))) {
            $where['year'] = $year;
        }
        if ($month = intval(I('month'))) {
            $where['month'] = $month;
        }
        $status = I('status');
        if ($status || $status==='0') {
            $where['status'] = $status;
        }
        $where['all_name'] = $my_department['all_name'];

        //审核信息
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
        $where['type'] = $type;
        $data_map = [];
        switch ($this->type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                $InsurancePropertyService = \Common\Service\InsurancePropertyService::get_instance();
                $_where = $where;
                unset($_where['status']);
                $data = $InsurancePropertyService->get_by_where_all($_where);
                foreach ($data as $da) {
                    $data_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
                break;
        }

      //  var_dump($where);die();
        list($list, $count) = $VerifyService->get_by_where($where, 'id desc', $p);
        if ($list){
            foreach ($list as $k => $info) {
                $list[$k]['status_desc'] = \Common\Model\FinancialVerifyModel::$status_map[$info['status']];
                if (isset($data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data'] = $data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];
                }
            }
        }


        $PageInstance = new \Think\Page($count, \Common\Service\BaseService::$page_size);
        if($count>\Common\Service\BaseService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $list);
        $this->assign('page_html', $page_html);

        $this->assign('can_edit',$this->check_rule('Admin/'.$this->type_name.'/submit_monthly'));

        $this->display();

    }


    public function index_all_list() {
        $list = [];
        $p = I('p',1);
        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $my_list = $DepartmentService->get_all_list($this->type);
        if (!$my_list) {
            $this->error('找不到该类型下部门信息');
        }
        $this->assign('departments',result_to_array($my_list,'all_name'));
        $where = [];
        if ($year = intval(I('year'))) {
            $where['year'] = $year;
        }
        if ($month = intval(I('month'))) {
            $where['month'] = $month;
        }
        if (($all_name = I('all_name')) && $all_name!='全部') {
            $where['all_name'] = $all_name;
        }

        if ($this->check_rule('Admin/'.$this->type_name.'/submit_monthly_all')) {

        } else {
            $where['status'] = ['neq',0];
        }
        //$where['status'] = ['neq',0];
        $status = I('status');
        if ($status || $status==='0') {
            $where['status'] = $status;
        }
        $data_map = [];
        switch ($this->type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                $Service = \Common\Service\InsurancePropertyService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:
                $Service = \Common\Service\InsuranceLifeService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:
                $Service = \Common\Service\InsuranceMutualService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                $Service = \Common\Service\VouchService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:
                $Service = \Common\Service\InvestmentService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:
                $Service = \Common\Service\InvestmentManagerService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:
                $Service = \Common\Service\FuturesService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                $Service = \Common\Service\LeaseService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                $Service = \Common\Service\LoanService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:
                $Service = \Common\Service\SecuritiesService::get_instance();
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                $Service = \Common\Service\TransferFundsService::get_instance();
                break;
        }

        if (isset($Service)) {
            $_where = $where;
            unset($_where['status']);
            $data = $Service->get_by_where_all($_where);
            foreach ($data as $da) {
                $data_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
            }

        }
        //echo_json_die($data_map);

        //审核信息
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
        $where['type'] = $type;
        list($list, $count) = $VerifyService->get_by_where($where, 'id desc', $p);
        if ($list){
            foreach ($list as $k => $info) {
                $list[$k]['status_desc'] = \Common\Model\FinancialVerifyModel::$status_map[$info['status']];
                if (isset($data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data'] = $data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];
                }
            }
        }


        $PageInstance = new \Think\Page($count, \Common\Service\BaseService::$page_size);
        if($count>\Common\Service\BaseService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $list);
        $this->assign('page_html', $page_html);

        $this->assign('is_all',1);

        $this->assign('can_edit', $this->check_rule('Admin/'.$this->type_name.'/submit_monthly_all'));
        $this->assign('can_change_status',$this->check_rule('Admin/'.$this->type_name.'/verify_change_status'));

        $this->display('index_list');

    }


    protected function _submit_verify($verify_info=[],$year=0,$month=0,$all_name='',$type=0,$status=0) {
        $VerifyService = \Common\Service\VerifyService::get_instance();
        //提交审核
        if ($verify_info && $verify_info['status'] != \Common\Model\FinancialVerifyModel::STATUS_INIT) {
            $this->error('对不起,您无法提交审核,该月审核记录已经提交!');
        }
        $data = [];
        $data['status'] = $status;
        $data['uid'] = UID;

        if ($verify_info) {

            $ret = $VerifyService->update_by_id($verify_info['id'], $data);
            if (!$ret->success) {
                return $ret;
            }
            action_user_log('提交审核,id:'.$verify_info['id']);
        } else {
            $data['year'] = $year;
            $data['month'] = $month;
            $data['all_name'] = $all_name;
            $data['type'] = $type;
            $ret = $VerifyService->add_one($data);
            if (!$ret->success) {
                return $ret;
            }
            action_user_log('提交审核,id:'.$ret->data);
        }
        $ret->success = true;
        return $ret;

    }

    public function submit_monthly_verify_new() {
        $id = I('get.id');
        $can_all_edit = $this->check_rule('Admin/'.$this->type_name.'/submit_monthly_all');

        $DepartmentService = \Common\Service\DepartmentService::get_instance();
        $my_list = $DepartmentService->get_my_list(UID,$this->type);
        if (!$my_list && !$can_all_edit) {
            $this->error('找不到您所属的部门信息');
        }

        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
        $this->verify_info = $VerifyService->get_info_by_id($id);
        if (!$this->verify_info) {
            $this->error('找不到数据');
        }
        $ret = $this->_submit_verify($this->verify_info,$this->verify_info['year'],$this->verify_info['month'],$this->verify_info['all_name'],$type,\Common\Model\FinancialVerifyModel::STATUS_SUBMIT);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        $this->success('提交成功!');
    }


    public function verify_change_status() {
        $id = I('get.id');
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $this->verify_info = $VerifyService->get_info_by_id($id);
        if (!$this->verify_info) {
            $this->error('找不到数据');
        }

        $status = I('status');

        $ret = $VerifyService->update_by_id($id,['status'=>$status]);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        $this->success('操作成功!');
    }
}