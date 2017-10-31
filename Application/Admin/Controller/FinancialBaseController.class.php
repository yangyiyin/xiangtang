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
        if ($year && $month && !IS_POST && I('editing')) {
            $info = $this->local_service->get_by_month_year($year,$month,$all_name);
            if (!$can_all_edit && (!$info || $info['all_name'] != $all_name)) {
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

            if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager) {
                foreach ($data['Staff_Sub'] as $sub) {
                    if ($sub == '' || !is_numeric($sub)) {
                        $this->error('请检查从业人员相关数据是否正确');
                    }
                }
                $data['Staff_Sub'] = join(',', $data['Staff_Sub']);
                $data['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_B;
            }

            if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment) {
                foreach ($data['Staff_Sub'] as $sub) {
                    if ($sub == '' || !is_numeric($sub)) {
                        $this->error('请检查从业人员相关数据是否正确');
                    }
                }
                $data['Staff_Sub'] = join(',', $data['Staff_Sub']);
                $data['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_A;
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

        $good_key = I('good_key');
        $cache_data = S($good_key);

        //获取编辑数据
        $infos = [];


        //如果是编辑状态并且是get
        if (!IS_POST && I('editing')) {

            if ($this->detail_type) {
                $infos = $this->local_service->get_by_month_year($year,$month,$all_name,$this->detail_type);
            } else {
                $infos = $this->local_service->get_by_month_year($year,$month,$all_name);
            }
            if (!$can_all_edit && (!$infos || $infos[0]['all_name'] != $all_name)) {
                $this->error('您没有权限查看该部门的信息');
            }

            $function_name = 'convert_data_'. ACTION_NAME;
            $this->$function_name($infos);
        }

        if ($cache_data) {
            $infos = $cache_data;
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
            if (!$cache_data) {
                $this->error('请导入excel数据~');
            }

            $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);

            if ($ret) {
                if (I('get.editing')) {
                    $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);

                } else {
                    $this->error('该月份数据已经保存');
                }
            } else {
                $jump_url = U('index_list');
                if ($can_all_edit) {
                    $jump_url = U('index_all_list');
                }
            }
            $function_name = 'get_add_data_'. ACTION_NAME;

            $batch_data = $this->$function_name($data, $cache_data);
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

            $count = count($infos);
            $page_size = \Common\Service\BaseService::$page_size;
            $PageInstance = new \Think\Page($count, $page_size);
            if($count>$page_size){
                $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $page_html = $PageInstance->show();

            $this->assign('page_html', $page_html);
            $page = I('p') ? I('p') : 1;
            $infos = array_slice($infos, $page_size * ($page-1), $page_size);

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

        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);
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

        list ($list,$count) = $this->get_list_data($where,$p);

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
        list ($list,$count) = $this->get_list_data($where,$p);

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


    private function get_list_data($where, $p) {
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
                if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager) {
                    $da['Staff_Sub'] = explode(',',$da['Staff_Sub']);
                }

                if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment) {
                    $da['Staff_Sub'] = explode(',',$da['Staff_Sub']);
                }

                $data_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
            }

        }

        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager) {
            //获取明细
            $InvestmentDetailsService = \Common\Service\InvestmentDetailsService::get_instance();
            $_where['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_B;
            $infos = $InvestmentDetailsService->get_by_where_all($_where);
            if ($infos) {
                $data_1_map = [];
                $this->convert_data_detail_submit_monthly($infos);
                foreach ($infos as $da) {
                    $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }
            //获取区域
            $AreaService = \Common\Service\AreaService::get_instance();
            $this->assign('area_options', $AreaService->set_area_options());


        }

        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment) {
            //获取明细
            $InvestmentDetailsService = \Common\Service\InvestmentDetailsService::get_instance();
            $_where['Types'] = \Common\Model\FinancialInvestmentModel::TYPE_A;
            $infos = $InvestmentDetailsService->get_by_where_all($_where);
            if ($infos) {
                $data_1_map = [];
                $this->convert_data_detail_submit_monthly($infos);
                foreach ($infos as $da) {
                    $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }

            //获取明细
            $InvestmentExitService = \Common\Service\InvestmentExitService::get_instance();

            $infos = $InvestmentExitService->get_by_where_all([]);
            if ($infos) {
                $data_2_map = [];
                $this->convert_data_exit_detail_submit_monthly($infos);
                foreach ($infos as $da) {
                    $data_2_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
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

                if (isset($data_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_1'] = $data_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];


                    $count = count($list[$k]['data_1']);
                    $page_size = \Common\Service\InvestmentManagerService::$page_size;


                    $PageInstance = new \Think\Page($count, $page_size);
                    if($count>$page_size){
                        $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $PageInstance->parameter['all_name'] = $info['all_name'];
                    $PageInstance->parameter['year'] = $info['year'];
                    $PageInstance->parameter['month'] = $info['month'];
                    $PageInstance->action_name = 'get_detail_page_html';

                    $page_html = $PageInstance->show();
                    $list[$k]['page_html'] = $page_html;
                    $page = 1;
                    $list[$k]['data_1'] = array_slice($list[$k]['data_1'], $page_size * ($page-1), $page_size);

                }


                if (isset($data_2_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_2'] = $data_2_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                    $count = count($list[$k]['data_2']);
                    $page_size = \Common\Service\InvestmentManagerService::$page_size;


                    $PageInstance = new \Think\Page($count, $page_size);
                    if($count>$page_size){
                        $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $PageInstance->parameter['all_name'] = $info['all_name'];
                    $PageInstance->parameter['year'] = $info['year'];
                    $PageInstance->parameter['month'] = $info['month'];
                    if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment) {
                        $PageInstance->action_name = 'get_exit_detail_page_html';
                    }
                    $page_html = $PageInstance->show();
                    $list[$k]['page_html_2'] = $page_html;
                    $page = 1;
                    $list[$k]['data_2'] = array_slice($list[$k]['data_2'], $page_size * ($page-1), $page_size);

                }


            }
        }
        return [$list,$count];

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


    public function upload_excel() {
        set_time_limit(0);
        /** Include path **/
        set_include_path(APP_PATH . '/Common/Lib/PHPExcel/Classes/');

        /** PHPExcel_IOFactory */
        include 'PHPExcel/IOFactory.php';
        $objPHP = new \PHPExcel_Reader_Excel5();
        $objPHPExcel = $objPHP->load($_FILES['file']['tmp_name']);

        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//         var_dump($sheetData);die();

        $data = $bad_data = [];

        $AreaService = \Common\Service\AreaService::get_instance();
        $key = '';
        $page_html = '';
        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager) {

            if (count($sheetData[2]) != 5) {
                $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
            }
            $AreaService = \Common\Service\AreaService::get_instance();

            for($i=3;$i<count($sheetData) + 1;$i++) {
                $temp = [];
                $is_bad_row = false;
                $sheetData[$i] = array_values($sheetData[$i]);
                if (!$sheetData[$i][1]) {
                    break;
                }

                $temp['Name'] = (string) $sheetData[$i][1];

                $area = $AreaService->get_like_name($sheetData[$i][2]);
                $temp['Area'] = isset($area['id']) ? $area['id'] : 0;

                $temp['Amount'] =  (string) $sheetData[$i][3];
                $temp['Remarks'] =  (string)  $sheetData[$i][4];

                $data[] = $temp;

            }

//             if ($bad_data) {
//                 $key = uniqid();
//                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','逾期贷款金额','化解金额','备注']);
//                 S($key, $bad_data, 120);
//             }


        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment) {

            if (I('get.type') != 'exit') {
                if (count($sheetData[2]) != 5) {
                    $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
                }
                $AreaService = \Common\Service\AreaService::get_instance();

                for($i=3;$i<count($sheetData) + 1;$i++) {
                    $temp = [];
                    $is_bad_row = false;
                    $sheetData[$i] = array_values($sheetData[$i]);
                    if (!$sheetData[$i][1]) {
                        break;
                    }

                    $temp['Name'] = (string) $sheetData[$i][1];

                    $area = $AreaService->get_like_name($sheetData[$i][2]);
                    $temp['Area'] = isset($area['id']) ? $area['id'] : 0;

                    $temp['Amount'] =  (string) $sheetData[$i][3];
                    $temp['Remarks'] =  (string)  $sheetData[$i][4];

                    $data[] = $temp;

                }
            } else {

                if (count($sheetData[2]) != 11) {

                    $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
                }

                for($i=4;$i<count($sheetData) + 1;$i++) {
                    $temp = [];
                    $is_bad_row = false;
                    $sheetData[$i] = array_values($sheetData[$i]);
                    //echo_json_die($sheetData[$i]);
                    if (!$sheetData[$i][1]) {
                        break;
                    }

//                    $temp['Name'] = (string) $sheetData[$i][1];
//                    $area = $AreaService->get_like_name($sheetData[$i][2]);
//                    $temp['Area'] = isset($area['id']) ? $area['id'] : 0;
//
//                    $temp['Amount'] =  (string) $sheetData[$i][3];
//                    $temp['Remarks'] =  (string)  $sheetData[$i][4];

                    $temp['Name'] = (string) $sheetData[$i][1];
                    $temp['Startdate'] =strtotime($sheetData[$i][2]);
                    $temp['Exitdate'] = strtotime($sheetData[$i][3]);
                    $temp['Investment'] = (string) $sheetData[$i][4];
                    $temp['Recycling'] = (string) $sheetData[$i][5];
                    if ($sheetData[$i][6]) {
                        $temp['ExitMethod'] = 1;
                    } elseif ($sheetData[$i][7]) {
                        $temp['ExitMethod'] = 2;
                    } elseif ($sheetData[$i][8]) {
                        $temp['ExitMethod'] = 3;
                    } elseif ($sheetData[$i][9]) {
                        $temp['ExitMethod'] = 4;
                    } elseif ($sheetData[$i][10]) {
                        $temp['ExitMethod'] = 5;
                    }

                    $data[] = $temp;

                }
            }

//             if ($bad_data) {
//                 $key = uniqid();
//                 array_unshift($bad_data,['企业名称','法人代表或实际控制人','企业所属乡镇（街道）','逾期贷款金额','化解金额','备注']);
//                 S($key, $bad_data, 120);
//             }


        }


        unlink($_FILES['file']['tmp_name']);

        if ($data) {
            $good_key = uniqid();
            S($good_key, $data, 600);//缓存好的数据
            $this->ajaxReturn(['status'=>true, 'key'=>$key, 'good_key' => $good_key, 'data' => $data]);
        } else {
            $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
        }

    }

    public function get_excel(){
        $key = I('key');
        $bad_data = S($key);
        exportexcel($bad_data,'退回数据', '退回数据');
    }


    public function export_excel() {
        set_time_limit(0);
        /** Include path **/
        set_include_path(APP_PATH . '/Common/Lib/PHPExcel/Classes/');

        /** PHPExcel_IOFactory */
        include 'PHPExcel.php';
        include 'PHPExcel/IOFactory.php';
        include 'PHPExcel/Style/Alignment.php';
        $PHPExcel = new \PHPExcel();
        $PHPExcel->getProperties()->setCreator("cixijinrongban")
            ->setLastModifiedBy("cixijinrongban")
            ->setTitle("慈溪金融办")
            ->setSubject("慈溪金融办")
            ->setDescription("慈溪金融办报表")
            ->setKeywords("金融办报表")
            ->setCategory("金融办报表");
        $title = '报表';
        $year = I('year') ? I('year') : intval(date('Y'));
        $month = I('month') ? I('month') : intval(date('m'));
        $statistics = [
            ['data'=>null,'name'=>'慈溪市金融机构本外币信贷收支情况表(表1)'],
            ['data'=>null,'name'=>'慈溪市金融机构本外币存贷情况表(表2)'],
            ['data'=>null,'name'=>'慈溪市金融机构不良贷款情况表(表3)'],
            ['data'=>null,'name'=>'慈溪市金融机构不良贷款50万(含以上)明细表(表4)'],
            ['data'=>null,'name'=>'慈溪市金融机构不良资产清收情况表(表5)'],
            ['data'=>null,'name'=>'慈溪市金融机构关注类贷款明细表(表6)'],
            ['data'=>null,'name'=>'慈溪市银行贷款利率执行水平监测表(表7)'],
            ['data'=>null,'name'=>'企业贷款利率执行水平监测表(表8)'],
            ['data'=>null,'name'=>'资产质量相关情况调查表(表9)']

        ];
        switch (I('type')) {

            case 2:
                $title = $statistics[1]['name'];
                $Service = \Common\Service\BankCreditBStNewService::get_instance();
                $statistics[1]['data'] = $Service->get_by_month_year($year, $month);
                $statistics = $this->convert_statistics_datas($statistics);
                $PHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', $title);
                $PHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', '类型')
                    ->setCellValue('B2', '金融机构名称!')
                    ->setCellValue('C2', '各项存款')
                    ->setCellValue('I2', '各项贷款')
                    ->setCellValue('O2', '本月存贷比')
                    ->setCellValue('C3', '年初余额')
                    ->setCellValue('D3', '上月余额')
                    ->setCellValue('E3', '月末余额')
                    ->setCellValue('F3', '比上月')
                    ->setCellValue('G3', '比年初')
                    ->setCellValue('H3', '同比')
                    ->setCellValue('I3', '年初余额')
                    ->setCellValue('J3', '上月余额')
                    ->setCellValue('K3', '月末余额')
                    ->setCellValue('L3', '比上月')
                    ->setCellValue('M3', '比年初')
                    ->setCellValue('N3', '同比')
                    ->setCellValue('O3', '余额比%')
                    ->setCellValue('P3', '增量比%');

                // echo json_encode($statistics);die();
                if ($statistics[1]['data']) {
                    $start = 4;
                    foreach ($statistics[1]['data'] as $key => $value) {
                        if ($key != '合计') {
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);
                            $num = $value ? count($value) : 0;
                            $end = $start + $num  - 1;
                            if ($end > $start) {
                                $PHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$start.':A'.$end);
                                $PHPExcel->getActiveSheet(0)->getStyle('A'.$start)->applyFromArray(
                                    [
                                        'alignment' => [
                                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        ]
                                    ]
                                );
                            }
                            if ($value) {
                                $unit_start = $start;
                                foreach ($value as $unit) {
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$unit_start, $unit['all_name']);

                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$unit_start, $unit['content']['Deposits'][0]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$unit_start, $unit['content']['Deposits'][1]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$unit_start, $unit['content']['Deposits'][2]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$unit_start, $unit['content']['Deposits'][3]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$unit_start, $unit['content']['Deposits'][4]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$unit_start, $unit['content']['Deposits'][5]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$unit_start, $unit['content']['Loans'][0]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$unit_start, $unit['content']['Loans'][1]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$unit_start, $unit['content']['Loans'][2]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$unit_start, $unit['content']['Loans'][3]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$unit_start, $unit['content']['Loans'][4]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$unit_start, $unit['content']['Loans'][5]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$unit_start, $unit['content']['Deposits_Loans'][0]);
                                    $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$unit_start, $unit['content']['Deposits_Loans'][1]);
                                    $unit_start++;
                                }
                            }

                            $start = $start + $num ;
                        } else {
                            // echo json_encode($statistics[0]['data'][$key]);die();
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$start, $key);

                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$start, '');

                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$start,  $statistics[1]['data'][$key]['Deposits'][0]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$start,  $statistics[1]['data'][$key]['Deposits'][1]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$start,  $statistics[1]['data'][$key]['Deposits'][2]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$start,  $statistics[1]['data'][$key]['Deposits'][3]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$start,  $statistics[1]['data'][$key]['Deposits'][4]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$start,  $statistics[1]['data'][$key]['Deposits'][5]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$start,  $statistics[1]['data'][$key]['Loans'][0]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$start,  $statistics[1]['data'][$key]['Loans'][1]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$start,  $statistics[1]['data'][$key]['Loans'][2]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$start,  $statistics[1]['data'][$key]['Loans'][3]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$start,  $statistics[1]['data'][$key]['Loans'][4]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$start,  $statistics[1]['data'][$key]['Loans'][5]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$start,  $statistics[1]['data'][$key]['Deposits_Loans'][0]);
                            $PHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$start,  $statistics[1]['data'][$key]['Deposits_Loans'][1]);

                        }


                    }


                }


                $PHPExcel->getActiveSheet(0)->mergeCells('A1:P1');
                $PHPExcel->getActiveSheet(0)->mergeCells('A2:A3');
                $PHPExcel->getActiveSheet(0)->mergeCells('B2:B3');
                $PHPExcel->getActiveSheet(0)->mergeCells('C2:H2');
                $PHPExcel->getActiveSheet(0)->mergeCells('I2:N2');
                $PHPExcel->getActiveSheet(0)->mergeCells('O2:P2');

                $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray(
                    [
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ]
                    ]
                );



                break;

        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }

}