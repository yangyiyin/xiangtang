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
    protected $verify_type = '';
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
        if ($this->verify_type) {
            $type = $this->verify_type;
        } else {
            $type = $VerifyService->get_type($this->type);
        }
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

            if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank) {

                foreach ($data as $field => $value) {
                    if (is_array($value)) {
                        if (count($value) == 3 && is_numeric($value[0]) && is_numeric($value[1]) && is_numeric($value[2])) {
                            $data[$field] = join('|', $value);
                        }
                        elseif (count($value) == 4 && is_numeric($value[0]) && is_numeric($value[1]) && is_numeric($value[2])&& is_numeric($value[3])){
                            $data[$field] = join('|', $value);
                        }
                        elseif (count($value) == 5 && is_numeric($value[0]) && is_numeric($value[1]) && is_numeric($value[2]) && is_numeric($value[3]) && is_numeric($value[4])){
                            $data[$field] = join('|', $value);
                        }
                        else {
                            $data[$field] = '';
                        }
                    }

                }
            }



            if ($id) {
                $ret = $this->local_service->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改月报表type:'.$this->type.'--id:'.$id);

                } else {
                    action_user_log('修改月报表type:'.$this->type.'--id:'.$id);

                    if (strpos($ret->message,'网络繁忙') !== false) {

                    } else {
                        $this->error($ret->message);
                    }

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

                    $jump_url = 'javascript:self.location=document.referrer;';
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
                    $jump_url = 'javascript:self.location=document.referrer;';
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
            if (method_exists($this,$function_name)) {
                $this->$function_name($infos);
            }

        }

        if ($cache_data) {
            $infos = $cache_data;

            $function_name = 'convert_data_'. ACTION_NAME;
            if (method_exists($this,$function_name)) {
                $this->$function_name($infos);
            }

        }

        //var_dump($infos);die();
        $VerifyService = \Common\Service\VerifyService::get_instance();
        if ($this->verify_type) {
            $type = $this->verify_type;
        } else {
            $type = $VerifyService->get_type($this->type);
        }
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
            if ($this->detail_type) {
                $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);

            } else {
                $ret = $this->local_service->get_by_month_year($data['year'], $data['month'], $data['all_name']);

            }

            if ($ret) {
                if (I('get.editing')) {
                    if ($this->detail_type) {
                        $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name'], $this->detail_type);
                    } else {
                        $this->local_service->del_by_month_year($data['year'], $data['month'], $data['all_name']);

                    }

                } else {
                    $this->error('该月份数据已经保存');
                }
            } else {

                $jump_url = '';
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
                    $jump_url = 'javascript:self.location=document.referrer;';
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
        if ($this->verify_type) {
            $type = $this->verify_type;
        } else {
            $type = $VerifyService->get_type($this->type);
        }

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
                $where['all_name'][] = ['in', $all_nams];
                $where_all['all_name'][] = ['not in', $all_nams];
                $where_extra['all_name'] = ['not in', $all_nams];
            }

        }


        if (method_exists($this, 'gain_statistics')) {
            $this->gain_statistics($get['year'], $get['month'], $this->type, $where_extra);//自动生成统计
        }

        $data_all = $this->local_service->get_by_where_all($where_all);
        if ($this->order) {
            $order = $this->order;
        } else {
            $order = 'id desc';
        }
        list($data, $count) = $this->local_service->get_by_where($where, $order, $page);
      //  var_dump($where);die();
        $data = $this->convert_data_statistics($data, $data_all);

        $PageInstance = new \Think\Page($count, $service::$page_size);
        if($total>$service::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        return [$data, $data_all];
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
        //var_dump($data_all);die();
        $data = $this->convert_data_statistics($data_all, $data_all);

        return $data;
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
//        $VerifyService = \Common\Service\VerifyService::get_instance();
//        $type = $VerifyService->get_type($this->type);
//
//        if (!$type) {
//            if (I('form_type',1) == 1) {
//                $type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;
//            } else {
//                $type = \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter;
//            }
//        }
//        $where['type'] = $type;

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

        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds) {
            //获取明细
            if ($data) {
                $data_1_map = [];
                foreach ($data as $da) {
                    $data_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }
        }

        if ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialBank) {
            //获取1
            $BankCreditNewService = \Common\Service\BankCreditNewService::get_instance();
            $infos = $BankCreditNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_1_map = [];
                //$this->convert_data_submit_monthly($infos);

                foreach ($infos as $da) {
                    $this->convert_data_submit_monthly($da);
                    $data_bank_1_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
            }


            //获取2
            $BankBaddebtNewService = \Common\Service\BankBaddebtNewService::get_instance();
            $infos = $BankBaddebtNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_2_map = [];
                foreach ($infos as $da) {
                    $data_bank_2_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
            }


            //获取3
            $BankBaddebtDetailNewService = \Common\Service\BankBaddebtDetailNewService::get_instance();
            $infos = $BankBaddebtDetailNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_3_map = [];

                foreach ($infos as $da) {
                    $data_bank_3_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }

            //获取4
            $BankBaddebtDisposeNewService = \Common\Service\BankBaddebtDisposeNewService::get_instance();
            $infos = $BankBaddebtDisposeNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_4_map = [];

                foreach ($infos as $da) {
                    $data_bank_4_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }

            //获取5
            $BankFocusDetailNewService = \Common\Service\BankFocusDetailNewService::get_instance();
            $infos = $BankFocusDetailNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_5_map = [];

                foreach ($infos as $da) {
                    $data_bank_5_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']][] = $da;
                }
            }

            //获取6
            $BankQuaterlyQuantityANewService = \Common\Service\BankQuaterlyQuantityANewService::get_instance();
            $infos = $BankQuaterlyQuantityANewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_6_map = [];
                //$this->convert_data_submit_monthly($infos);

                foreach ($infos as $da) {
                    $this->convert_data_submit_monthly($da);
                    $data_bank_6_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
            }

            //获取7
            $BankQuaterlyQuantityBNewService = \Common\Service\BankQuaterlyQuantityBNewService::get_instance();
            $infos = $BankQuaterlyQuantityBNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_7_map = [];
                //$this->convert_data_submit_monthly($infos);

                foreach ($infos as $da) {
                    $this->convert_data_submit_monthly($da);
                    $data_bank_7_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
            }

            //获取8
            $BankQuaterlyQuantityCNewService = \Common\Service\BankQuaterlyQuantityCNewService::get_instance();
            $infos = $BankQuaterlyQuantityCNewService->get_by_where_all($_where);
            if ($infos) {
                $data_bank_8_map = [];
                //$this->convert_data_submit_monthly($infos);

                foreach ($infos as $da) {
                    $this->convert_data_submit_monthly($da);
                    $data_bank_8_map[$da['year'].'_'.$da['month'].'_'.$da['all_name']] = $da;
                }
            }

        }


//
       // echo_json_die($data_bank_1_map);

        //审核信息
        $VerifyService = \Common\Service\VerifyService::get_instance();
        $type = $VerifyService->get_type($this->type);

        //银行type
        if (!$type) {
            if (I('form_type',1) == 1) {
                $type = \Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH;
            } else {
                $type = \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter;
            }
        }

        $where['type'] = $type;
        list($list, $count) = $VerifyService->get_by_where($where, 'month desc', $p);

        if ($list){
            foreach ($list as $k => $info) {
                $list[$k]['status_desc'] = \Common\Model\FinancialVerifyModel::$status_map[$info['status']];
                if (isset($data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data'] = $data_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];
                }

                if (isset($data_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_1'] = $data_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];


                    $count = count($list[$k]['data_1']);
                    $page_size = \Common\Service\BaseService::$page_size;


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
                    $page_size = \Common\Service\BaseService::$page_size;


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

                if (isset($data_bank_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    //echo 1;die();
                    $list[$k]['data_bank_1'] = $data_bank_1_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                }

                if (isset($data_bank_2_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    //echo 1;die();
                    $list[$k]['data_bank_2'] = $data_bank_2_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                }

                if (isset($data_bank_3_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_bank_3'] = $data_bank_3_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                    $count = count($list[$k]['data_bank_3']);
                    $page_size = \Common\Service\BaseService::$page_size;


                    $PageInstance = new \Think\Page($count, $page_size);
                    if($count>$page_size){
                        $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $PageInstance->parameter['all_name'] = $info['all_name'];
                    $PageInstance->parameter['year'] = $info['year'];
                    $PageInstance->parameter['month'] = $info['month'];
                    $PageInstance->parameter['bank_type'] = 3;
                    $PageInstance->action_name = 'get_detail_page_html';
                    $page_html = $PageInstance->show();
                    $list[$k]['page_html_bank_3'] = $page_html;
                    $page = 1;
                    $list[$k]['data_bank_3'] = array_slice($list[$k]['data_bank_3'], $page_size * ($page-1), $page_size);

                }

                if (isset($data_bank_4_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_bank_4'] = $data_bank_4_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                    $count = count($list[$k]['data_bank_4']);
                    $page_size = \Common\Service\BaseService::$page_size;


                    $PageInstance = new \Think\Page($count, $page_size);
                    if($count>$page_size){
                        $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $PageInstance->parameter['all_name'] = $info['all_name'];
                    $PageInstance->parameter['year'] = $info['year'];
                    $PageInstance->parameter['month'] = $info['month'];
                    $PageInstance->parameter['bank_type'] = 4;
                    $PageInstance->action_name = 'get_detail_page_html';
                    $page_html = $PageInstance->show();
                    $list[$k]['page_html_bank_4'] = $page_html;
                    $page = 1;
                    $list[$k]['data_bank_4'] = array_slice($list[$k]['data_bank_4'], $page_size * ($page-1), $page_size);

                }

                if (isset($data_bank_5_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    $list[$k]['data_bank_5'] = $data_bank_5_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                    $count = count($list[$k]['data_bank_5']);
                    $page_size = \Common\Service\BaseService::$page_size;


                    $PageInstance = new \Think\Page($count, $page_size);
                    if($count>$page_size){
                        $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $PageInstance->parameter['all_name'] = $info['all_name'];
                    $PageInstance->parameter['year'] = $info['year'];
                    $PageInstance->parameter['month'] = $info['month'];
                    $PageInstance->parameter['bank_type'] = 5;
                    $PageInstance->action_name = 'get_detail_page_html';
                    $page_html = $PageInstance->show();
                    $list[$k]['page_html_bank_5'] = $page_html;
                    $page = 1;
                    $list[$k]['data_bank_5'] = array_slice($list[$k]['data_bank_5'], $page_size * ($page-1), $page_size);

                }
                if (isset($data_bank_6_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    //echo 1;die();
                    $list[$k]['data_bank_6'] = $data_bank_6_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                }
                if (isset($data_bank_7_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    //echo 1;die();
                    $list[$k]['data_bank_7'] = $data_bank_7_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                }
                if (isset($data_bank_8_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']])) {
                    //echo 1;die();
                    $list[$k]['data_bank_8'] = $data_bank_8_map[$info['year'].'_'.$info['month'].'_'.$info['all_name']];

                }

            }
        }
        //var_dump($list);die();
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
        if ($this->verify_type) {
            $type = $this->verify_type;
        } else {
            $type = $VerifyService->get_type($this->type);
        }
        $this->verify_info = $VerifyService->get_info_by_id($id);
        if (!$this->verify_info) {
            $this->error('找不到数据');
        }

        $status = \Common\Model\FinancialVerifyModel::STATUS_SUBMIT;
        if ($VerifyService->is_ok_direct($this->type)) {
            $status = \Common\Model\FinancialVerifyModel::STATUS_OK;
        }
        $ret = $this->_submit_verify($this->verify_info,$this->verify_info['year'],$this->verify_info['month'],$this->verify_info['all_name'],$type,$status);
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


        } elseif ($this->type == \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds) {

            if (count($sheetData[4]) != 12) {
                $this->ajaxReturn(['status'=>false, 'info' => '没有解析成功,请确认导入的数据是否按照要求正确导入~']);
            }
            $AreaService = \Common\Service\AreaService::get_instance();

            for($i=6;$i<count($sheetData) + 1;$i++) {
                $temp = [];
                $is_bad_row = false;
                $sheetData[$i] = array_values($sheetData[$i]);
                if (!$sheetData[$i][1] || $sheetData[$i][1] == '合计') {
                    break;
                }

                $temp['Bank'] = (string)$sheetData[$i][1];
                $temp['Account'] = (string)$sheetData[$i][2];
                $temp['Unit'] = (string)$sheetData[$i][3];
                $temp['Legal_Person'] = (string)$sheetData[$i][4];
                $temp['Amount'] = (string)$sheetData[$i][5];
                $temp['S_Date'] = strtotime($sheetData[$i][6]);
                $temp['E_Date'] = strtotime($sheetData[$i][7]);
                $temp['Days'] = (string)$sheetData[$i][8];
                $temp['Remarks'] = (string)$sheetData[$i][9];

                $data[] = $temp;

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
        $year = I('year') ? I('year') : intval(date('Y'));
        $month = I('month') ? I('month') : intval(date('m'));

        $statistics_datas = $this->get_statistics_datas($year, $month);
        //var_dump($statistics_datas);die();

        $title= '('.$year.'-'.$month.')';

        switch ($this->type) {

            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    if ($department['sub_type']) {
                        $data[$department['sub_type']][] = $statistics_datas_map[$department['all_name']];


                    } else {
                        $data[0][] = $statistics_datas_map[$department['all_name']];
                    }
                }

                //求累计
                $data_temp = $data;
                foreach ($data_temp as $key => $item) {
                    $all_item = [];
                    $all_item['other_name'] = '累计';
                    foreach ($item as $_item){


                        $all_item['income'] += $_item['income'];
                        $all_last_year_income += $_item['income'] / (1 + $_item['income_yoy'] / 100);
                        $all_item['income_a'] += $_item['income_a'];
                        $all_item['income_b'] += $_item['income_b'];
                        $all_item['income_c'] += $_item['income_c'];
                        $all_item['reserves'] += $_item['reserves'];
                        $all_item['payoff'] += $_item['payoff'];
                        $all_item['payoff_a'] += $_item['payoff_a'];
                        $all_item['payoff_b'] += $_item['payoff_b'];
                        $all_item['payoff_c'] += $_item['payoff_c'];
                        $all_item['staff'] += $_item['staff'];
                        $all_item['authorized'] += $_item['authorized'];

                    }
                    $all_item['income_yoy'] = fix_2(($all_item['income'] - $all_last_year_income) / $all_last_year_income);
                    $all_item['payoff_rate'] =$all_item['income'] ?  fix_2($all_item['payoff'] / $all_item['income']) : 100;
                    $all_item['payoff_a_rate'] =$all_item['income_a'] ? fix_2($all_item['payoff_a'] / $all_item['income_a']) : 100;
                    $all_item['payoff_b_rate'] =$all_item['income_b'] ? fix_2($all_item['payoff_b'] / $all_item['income_b']) : 100;
                    $all_item['payoff_c_rate'] =$all_item['income_c'] ? fix_2($all_item['payoff_b'] / $all_item['income_c']) : 100;
                    array_unshift($data[$key], $all_item);

                }



                //var_dump($data);die();

                $title = '财产保险公司统计表' . $title;
                $index = 0;

                //没有一级部门的部门数据
                if ($data[0]) {

                    $line = 'D';
                    foreach ($data[0] as $_data) {

                        if ($line > 'Q') {
                            $PHPExcel->createSheet();
                            $index++;
                            $line = 'D';
                        }

                        if ($line == 'D') {

                            $PHPExcel->setActiveSheetIndex($index)
                                ->setCellValue('A1', '财产保险公司统计表'.'('.$year.'-'.$month.')'. '    单位: 万元    '.'('.$index.')');

                            $PHPExcel->setActiveSheetIndex($index)
                                ->setCellValue('A2', '机构')
                                ->setCellValue('A3', '保费')
                                ->setCellValue('B3', '保费收入')
                                ->setCellValue('B4', '同比%')
                                ->setCellValue('B5', '其中')
                                ->setCellValue('C5', '企业财产险')
                                ->setCellValue('C6', '机动车辆险')
                                ->setCellValue('C7', '其他险')
                                ->setCellValue('A8', '存储金额')
                                ->setCellValue('A9', '赔付')
                                ->setCellValue('B9', '赔付支出')
                                ->setCellValue('B10', '简单赔付率%')
                                ->setCellValue('B11', '其中')
                                ->setCellValue('C11', '企业财产险')
                                ->setCellValue('C12', '赔付率%')
                                ->setCellValue('C13', '机动车辆险')
                                ->setCellValue('C14', '赔付率%')
                                ->setCellValue('C15', '其他险')
                                ->setCellValue('C16', '赔付率%')
                                ->setCellValue('A17', '营销员')
                                ->setCellValue('C17', '期末在岗人数')
                                ->setCellValue('C18', '期末持证人数')
                                ->setCellValue('A19', '保费排名')
                                ->setCellValue('A20', '保费份额占比%');

                            $PHPExcel->getActiveSheet($index)->mergeCells('A1:Q1');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A2:C2');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A3:A7');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B3:C3');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B4:C4');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B5:B7');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A8:C8');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A9:A16');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B9:C9');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B10:C10');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B11:B16');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A17:B18');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A19:C19');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A20:C20');


                            $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                                [
                                    'alignment' => [
                                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ]
                                ]
                            );
                        }

                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue($line.'2', $_data['other_name'])
                            ->setCellValue($line.'3', $_data['income'])
                            ->setCellValue($line.'4', $_data['income_yoy'])
                            ->setCellValue($line.'5', $_data['income_a'])
                            ->setCellValue($line.'6', $_data['income_b'])
                            ->setCellValue($line.'7', $_data['income_c'])
                            ->setCellValue($line.'8', $_data['reserves'])
                            ->setCellValue($line.'9', $_data['payoff'])
                            ->setCellValue($line.'10', $_data['payoff_rate'])
                            ->setCellValue($line.'11', $_data['payoff_a'])
                            ->setCellValue($line.'12', $_data['payoff_a_rate'])
                            ->setCellValue($line.'13', $_data['payoff_b'])
                            ->setCellValue($line.'14', $_data['payoff_b_rate'])
                            ->setCellValue($line.'15', $_data['payoff_c'])
                            ->setCellValue($line.'16', $_data['payoff_c_rate'])
                            ->setCellValue($line.'17', $_data['staff'])
                            ->setCellValue($line.'18', $_data['authorized'])
                            ->setCellValue($line.'19', $_data['sort'])
                            ->setCellValue($line.'20', $_data['percent']);
                        $line = chr(ord($line)+1);

                    }
                }

                //获取附表
                if (count($data) > 2) {
                    $PHPExcel->createSheet();
                    $index ++;
                }

                $sub_type_map = \Common\Model\FinancialDepartmentModel::$SUB_TYPE_insurance_property_MAP;
                $line = 'D';

                foreach ($data as $key => $__data) {
                    if ($key > 0) {
                        $PHPExcel->setActiveSheetIndex($index)->setCellValue($line.'2', $sub_type_map[$key]);

                        if (isset($old_line) && $line > $old_line) {
                           // echo $old_line . '-' . $line;
                            $PHPExcel->getActiveSheet($index)->mergeCells($old_line.'2'.':'.chr(ord($line)-1).'2');
                        }
                        $old_line = $line;

                        foreach ($__data as $_data){
                            //$line = 'D';
//                            if ($line > 'F') {
//                                $PHPExcel->createSheet();
//                                $index++;
//                                $line = 'D';//Q
//                            }

                            if ($line == 'D') {

                                $PHPExcel->setActiveSheetIndex($index)
                                    ->setCellValue('A1', '财产保险公司统计表(附表)'.'('.$year.'-'.$month.')'. '    单位: 万元');

                                $PHPExcel->setActiveSheetIndex($index)
                                    ->setCellValue('A2', '机构')
                                    ->setCellValue('A4', '保费')
                                    ->setCellValue('B4', '保费收入')
                                    ->setCellValue('B5', '同比%')
                                    ->setCellValue('B6', '其中')
                                    ->setCellValue('C6', '企业财产险')
                                    ->setCellValue('C7', '机动车辆险')
                                    ->setCellValue('C8', '其他险')
                                    ->setCellValue('A9', '存储金额')
                                    ->setCellValue('A10', '赔付')
                                    ->setCellValue('B10', '赔付支出')
                                    ->setCellValue('B11', '简单赔付率%')
                                    ->setCellValue('B12', '其中')
                                    ->setCellValue('C12', '企业财产险')
                                    ->setCellValue('C13', '赔付率%')
                                    ->setCellValue('C14', '机动车辆险')
                                    ->setCellValue('C15', '赔付率%')
                                    ->setCellValue('C16', '其他险')
                                    ->setCellValue('C17', '赔付率%')
                                    ->setCellValue('A18', '营销员')
                                    ->setCellValue('C18', '期末在岗人数')
                                    ->setCellValue('C19', '期末持证人数')
                                    ->setCellValue('A20', '保费排名')
                                    ->setCellValue('A21', '保费份额占比%');

                                $PHPExcel->getActiveSheet($index)->mergeCells('A1:Q1');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A2:C3');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A4:A8');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B4:C4');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B5:C5');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B6:B8');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A9:C9');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A10:A17');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B10:C10');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B11:C11');
                                $PHPExcel->getActiveSheet($index)->mergeCells('B12:B17');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A18:B19');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A20:C20');
                                $PHPExcel->getActiveSheet($index)->mergeCells('A21:C21');


                                $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                                    [
                                        'alignment' => [
                                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                        ]
                                    ]
                                );
                            }

                            $PHPExcel->setActiveSheetIndex($index)
                                ->setCellValue($line.'3', $_data['other_name'])
                                ->setCellValue($line.'4', $_data['income'])
                                ->setCellValue($line.'5', $_data['income_yoy'])
                                ->setCellValue($line.'6', $_data['income_a'])
                                ->setCellValue($line.'7', $_data['income_b'])
                                ->setCellValue($line.'8', $_data['income_c'])
                                ->setCellValue($line.'9', $_data['reserves'])
                                ->setCellValue($line.'10', $_data['payoff'])
                                ->setCellValue($line.'11', $_data['payoff_rate'])
                                ->setCellValue($line.'12', $_data['payoff_a'])
                                ->setCellValue($line.'13', $_data['payoff_a_rate'])
                                ->setCellValue($line.'14', $_data['payoff_b'])
                                ->setCellValue($line.'15', $_data['payoff_b_rate'])
                                ->setCellValue($line.'16', $_data['payoff_c'])
                                ->setCellValue($line.'17', $_data['payoff_c_rate'])
                                ->setCellValue($line.'18', $_data['staff'])
                                ->setCellValue($line.'19', $_data['authorized'])
                                ->setCellValue($line.'20', $_data['sort'])
                                ->setCellValue($line.'21', $_data['percent']);



                            $line = chr(ord($line)+1);

                        }

                    }
                }
                if (isset($old_line) && $line > $old_line) {
                    // echo $old_line . '-' . $line;
                    $PHPExcel->getActiveSheet($index)->mergeCells($old_line.'2'.':'.chr(ord($line)-1).'2');
                }



                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

              //  var_dump($data);die();
                //求累计
                $data_temp = $data;
                $all_item = [];
                $all_item['other_name'] = '累计';
                foreach ($data_temp as $key => $_item) {

                    $all_item['income'] += $_item['income'];
                    $all_last_year_income_a += $_item['income_a'] / (1 + $_item['income_a_yoy'] / 100);
                    $all_item['income_a'] += $_item['income_a'];

                    $all_item['income_a_a'] += $_item['income_a_a'];
                    $all_item['income_a_b'] += $_item['income_a_b'];
                    $all_item['income_b'] += $_item['income_b'];
                    $all_item['income_c'] += $_item['income_c'];

                    $all_item['payoff_a'] += $_item['payoff_a'];
                    $all_item['payoff_b'] += $_item['payoff_b'];
                    $all_item['payoff_c'] += $_item['payoff_c'];
                    $all_item['payoff_d'] += $_item['payoff_d'];
                    $all_item['backoff'] += $_item['backoff'];
                    $all_item['staff'] += $_item['staff'];
                    $all_item['authorized'] += $_item['authorized'];


                }

                $all_item['income_a_yoy'] = fix_2(($all_item['income_a'] - $all_last_year_income_a) / $all_last_year_income_a);
                $all_item['payoff_a_rate'] =$all_item['income_b'] ?  fix_2($all_item['payoff_a'] / $all_item['income_b']) : 100;


                array_unshift($data, $all_item);

                //var_dump($data);die();

                $title = '人身保险公司统计表' . $title;
                $index = 0;

                if ($data) {

                    $line = 'D';
                    foreach ($data as $_data) {

                        if ($line > 'F') {
                            $PHPExcel->createSheet();
                            $index++;
                            $line = 'D';
                        }

                        if ($line == 'D') {

                            $PHPExcel->setActiveSheetIndex($index)
                                ->setCellValue('A1', '人身保险公司统计表'.'('.$year.'-'.$month.')'. '    单位: 万元    '.'('.$index.')');

                            $PHPExcel->setActiveSheetIndex($index)
                                ->setCellValue('A2', '机构')
                                ->setCellValue('A3', '保费')
                                ->setCellValue('B3', '保费收入')
                                ->setCellValue('B4', '1.个人营销')
                                ->setCellValue('B5', '同比%')
                                ->setCellValue('B6', '其中')
                                ->setCellValue('C6', '新单首期')
                                ->setCellValue('C7', '新单期缴')
                                ->setCellValue('B8', '2.团体业务')
                                ->setCellValue('B9', '3.银行代理')
                                ->setCellValue('A10', '赔付')
                                ->setCellValue('B10', '短险赔付金额')
                                ->setCellValue('B11', '短险赔付率%')
                                ->setCellValue('B12', '死伤医给付金额')
                                ->setCellValue('B13', '满期给付金额')
                                ->setCellValue('B14', '年金给付金额')
                                ->setCellValue('A15', '退保金额')
                                ->setCellValue('A16', '营销员人数')
                                ->setCellValue('A17', '持证人数')
                                ->setCellValue('A18', '保费排名')
                                ->setCellValue('A19', '保费占比');


                            $PHPExcel->getActiveSheet($index)->mergeCells('A1:Q1');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A2:C2');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A3:A9');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B3:C3');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B4:C4');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B5:C5');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B6:B7');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B8:C8');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B9:C9');

                            $PHPExcel->getActiveSheet($index)->mergeCells('A10:A14');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B10:C10');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B11:C11');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B12:C12');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B13:C13');
                            $PHPExcel->getActiveSheet($index)->mergeCells('B14:C14');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A15:C15');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A16:C16');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A17:C17');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A18:C18');
                            $PHPExcel->getActiveSheet($index)->mergeCells('A19:C19');



                            $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                                [
                                    'alignment' => [
                                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ]
                                ]
                            );
                        }

                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue($line.'2', $_data['other_name'])
                            ->setCellValue($line.'3', $_data['income'])
                            ->setCellValue($line.'4', $_data['income_a'])
                            ->setCellValue($line.'5', $_data['income_a_yoy'])
                            ->setCellValue($line.'6', $_data['income_a_a'])
                            ->setCellValue($line.'7', $_data['income_a_b'])
                            ->setCellValue($line.'8', $_data['income_b'])
                            ->setCellValue($line.'9', $_data['income_c'])
                            ->setCellValue($line.'10', $_data['payoff_a'])
                            ->setCellValue($line.'11', $_data['payoff_a_rate'])
                            ->setCellValue($line.'12', $_data['payoff_b'])
                            ->setCellValue($line.'13', $_data['payoff_c'])
                            ->setCellValue($line.'14', $_data['payoff_d'])
                            ->setCellValue($line.'15', $_data['backoff'])
                            ->setCellValue($line.'16', $_data['staff'])
                            ->setCellValue($line.'17', $_data['authorized'])
                            ->setCellValue($line.'18', $_data['sort'])
                            ->setCellValue($line.'19', $_data['percent']);
                        $line = chr(ord($line)+1);

                    }
                }

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

                //var_dump($data);die();
                $title = '保险互助社统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '保险互助社统计表'.'('.$year.'-'.$month.')'. '    单位: 万元)');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '家庭财产险')
                        ->setCellValue('H2', '意外险')
                        ->setCellValue('N2', '补充医疗互助保险')
                        ->setCellValue('T2', '合计')
                        ->setCellValue('B3', '承保件数')
                        ->setCellValue('C3', '承保户数')
                        ->setCellValue('D3', '保费')
                        ->setCellValue('E3', '保险金额')
                        ->setCellValue('F3', '赔付件数')
                        ->setCellValue('G3', '赔付金额')
                        ->setCellValue('H3', '承保件数')
                        ->setCellValue('I3', '承保户数')
                        ->setCellValue('J3', '保费')
                        ->setCellValue('K3', '保险金额')
                        ->setCellValue('L3', '赔付件数')
                        ->setCellValue('M3', '赔付金额')
                        ->setCellValue('N3', '承保件数')
                        ->setCellValue('O3', '承保户数')
                        ->setCellValue('P3', '保费')
                        ->setCellValue('Q3', '保险金额')
                        ->setCellValue('R3', '赔付件数')
                        ->setCellValue('S3', '赔付金额')
                        ->setCellValue('T3', '承保件数')
                        ->setCellValue('U3', '承保户数')
                        ->setCellValue('V3', '保费')
                        ->setCellValue('W3', '保险金额')
                        ->setCellValue('X3', '赔付件数')
                        ->setCellValue('Y3', '赔付金额');


                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:Y1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:G2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('H2:M2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('N2:S2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('T2:Y2');




                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;
                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['Life_A'])
                            ->setCellValue('C'.$line, $_data['Life_B'])
                            ->setCellValue('D'.$line, $_data['Life_C'])
                            ->setCellValue('E'.$line, $_data['Life_D'])
                            ->setCellValue('F'.$line, $_data['Life_E'])
                            ->setCellValue('G'.$line, $_data['Life_F'])
                            ->setCellValue('H'.$line, $_data['Casualty_A'])
                            ->setCellValue('I'.$line, $_data['Casualty_B'])
                            ->setCellValue('J'.$line, $_data['Casualty_C'])
                            ->setCellValue('K'.$line, $_data['Casualty_D'])
                            ->setCellValue('L'.$line, $_data['Casualty_E'])
                            ->setCellValue('M'.$line, $_data['Casualty_F'])
                            ->setCellValue('N'.$line, $_data['Medical_A'])
                            ->setCellValue('O'.$line, $_data['Medical_B'])
                            ->setCellValue('P'.$line, $_data['Medical_C'])
                            ->setCellValue('Q'.$line, $_data['Medical_D'])
                            ->setCellValue('R'.$line, $_data['Medical_E'])
                            ->setCellValue('S'.$line, $_data['Medical_F'])
                            ->setCellValue('T'.$line, $_data['total_a'])
                            ->setCellValue('U'.$line, $_data['total_b'])
                            ->setCellValue('V'.$line, $_data['total_c'])
                            ->setCellValue('W'.$line, $_data['total_d'])
                            ->setCellValue('X'.$line, $_data['total_e'])
                            ->setCellValue('Y'.$line, $_data['total_f']);
                        $line ++;
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'].'(年度)')
                            ->setCellValue('B'.$line, $_data['st_a']['Life_A'])
                            ->setCellValue('C'.$line, $_data['st_a']['Life_B'])
                            ->setCellValue('D'.$line, $_data['st_a']['Life_C'])
                            ->setCellValue('E'.$line, $_data['st_a']['Life_D'])
                            ->setCellValue('F'.$line, $_data['st_a']['Life_E'])
                            ->setCellValue('G'.$line, $_data['st_a']['Life_F'])
                            ->setCellValue('H'.$line, $_data['st_a']['Casualty_A'])
                            ->setCellValue('I'.$line, $_data['st_a']['Casualty_B'])
                            ->setCellValue('J'.$line, $_data['st_a']['Casualty_C'])
                            ->setCellValue('K'.$line, $_data['st_a']['Casualty_D'])
                            ->setCellValue('L'.$line, $_data['st_a']['Casualty_E'])
                            ->setCellValue('M'.$line, $_data['st_a']['Casualty_F'])
                            ->setCellValue('N'.$line, $_data['st_a']['Medical_A'])
                            ->setCellValue('O'.$line, $_data['st_a']['Medical_B'])
                            ->setCellValue('P'.$line, $_data['st_a']['Medical_C'])
                            ->setCellValue('Q'.$line, $_data['st_a']['Medical_D'])
                            ->setCellValue('R'.$line, $_data['st_a']['Medical_E'])
                            ->setCellValue('S'.$line, $_data['st_a']['Medical_F'])
                            ->setCellValue('T'.$line, $_data['st_a']['total_a'])
                            ->setCellValue('U'.$line, $_data['st_a']['total_b'])
                            ->setCellValue('V'.$line, $_data['st_a']['total_c'])
                            ->setCellValue('W'.$line, $_data['st_a']['total_d'])
                            ->setCellValue('X'.$line, $_data['st_a']['total_e'])
                            ->setCellValue('Y'.$line, $_data['st_a']['total_f']);
                        $line ++;
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'].'(开业以来)')
                            ->setCellValue('B'.$line, $_data['st_b']['Life_A'])
                            ->setCellValue('C'.$line, $_data['st_b']['Life_B'])
                            ->setCellValue('D'.$line, $_data['st_b']['Life_C'])
                            ->setCellValue('E'.$line, $_data['st_b']['Life_D'])
                            ->setCellValue('F'.$line, $_data['st_b']['Life_E'])
                            ->setCellValue('G'.$line, $_data['st_b']['Life_F'])
                            ->setCellValue('H'.$line, $_data['st_b']['Casualty_A'])
                            ->setCellValue('I'.$line, $_data['st_b']['Casualty_B'])
                            ->setCellValue('J'.$line, $_data['st_b']['Casualty_C'])
                            ->setCellValue('K'.$line, $_data['st_b']['Casualty_D'])
                            ->setCellValue('L'.$line, $_data['st_b']['Casualty_E'])
                            ->setCellValue('M'.$line, $_data['st_b']['Casualty_F'])
                            ->setCellValue('N'.$line, $_data['st_b']['Medical_A'])
                            ->setCellValue('O'.$line, $_data['st_b']['Medical_B'])
                            ->setCellValue('P'.$line, $_data['st_b']['Medical_C'])
                            ->setCellValue('Q'.$line, $_data['st_b']['Medical_D'])
                            ->setCellValue('R'.$line, $_data['st_b']['Medical_E'])
                            ->setCellValue('S'.$line, $_data['st_b']['Medical_F'])
                            ->setCellValue('T'.$line, $_data['st_b']['total_a'])
                            ->setCellValue('U'.$line, $_data['st_b']['total_b'])
                            ->setCellValue('V'.$line, $_data['st_b']['total_c'])
                            ->setCellValue('W'.$line, $_data['st_b']['total_d'])
                            ->setCellValue('X'.$line, $_data['st_b']['total_e'])
                            ->setCellValue('Y'.$line, $_data['st_b']['total_f']);
                        $line ++;

                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

                //var_dump($data);die();
                $title = '担保公司统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '担保公司统计表'.'('.$year.'-'.$month.')'. '    单位: 万元)');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '期末担保余额')
                        ->setCellValue('F2', '累计担保额')
                        ->setCellValue('J2', '担保费')
                        ->setCellValue('L2', '累计收回')
                        ->setCellValue('N2', '本期利润')
                        ->setCellValue('O2', '纳税总额')
                        ->setCellValue('B3', '本期余额')
                        ->setCellValue('C3', '本期笔数')
                        ->setCellValue('D3', '去年同期余额')
                        ->setCellValue('E3', '去年同期笔数')
                        ->setCellValue('F3', '本年担保额')
                        ->setCellValue('G3', '本年担保笔数')
                        ->setCellValue('H3', '去年同期担保额')
                        ->setCellValue('I3', '去年同期担保笔数')
                        ->setCellValue('J3', '本月收入')
                        ->setCellValue('K3', '本年收入')
                        ->setCellValue('L3', '本月收回')
                        ->setCellValue('M3', '本年累计收回');



                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:O1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:I2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('J2:K2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('L2:M2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('N2:N3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('O2:O3');



                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['C_Balance'])
                            ->setCellValue('C'.$line, $_data['C_Quantity'])
                            ->setCellValue('D'.$line, $_data['C_Balance_Ly'])
                            ->setCellValue('E'.$line, $_data['C_Quantity_Ly'])
                            ->setCellValue('F'.$line, $_data['G_Vouch'])
                            ->setCellValue('G'.$line, $_data['G_Quantity'])
                            ->setCellValue('H'.$line, $_data['G_Vouch_Ly'])
                            ->setCellValue('I'.$line, $_data['G_Quantity_Ly'])
                            ->setCellValue('J'.$line, $_data['C_Income'])
                            ->setCellValue('K'.$line, $_data['G_Income'])
                            ->setCellValue('L'.$line, $_data['C_Recover'])
                            ->setCellValue('M'.$line, $_data['G_Recover'])
                            ->setCellValue('N'.$line, $_data['C_Profit'])
                            ->setCellValue('O'.$line, $_data['C_Taxable']);

                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

               // var_dump($data);die();
                $title = '股权投资和创业投资机构统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '股权投资和创业投资机构统计表'.'('.$year.'-'.$month.')'. '    单位: 万元)');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '注册资金')
                        ->setCellValue('C2', '已投资项目数')
                        ->setCellValue('F2', '已投资项目额')
                        ->setCellValue('I2', '退出项目')
                        ->setCellValue('Y2', '各类税费')
                        ->setCellValue('AB2', '从业人员数量')
                        ->setCellValue('AC2', '从业人员')

                        ->setCellValue('C3', '已投资项目总数')
                        ->setCellValue('D3', '其中：属于初创期投资的项目数')
                        ->setCellValue('E3', '所投资项目总额')
                        ->setCellValue('F3', '其中：属于初创期投资的项目投资额')
                        ->setCellValue('G3', '退出项目数（个）')
                        ->setCellValue('H3', '其中：上市退出项目数（个')
                        ->setCellValue('I3', '原始投资额')
                        ->setCellValue('J3', '退出后回收额')
                        ->setCellValue('K3', '管理层回购退出项目数（个）')
                        ->setCellValue('L3', '原始投资额')
                        ->setCellValue('M3', '退出后回收额')
                        ->setCellValue('N3', '股权转让退出项目数（个')
                        ->setCellValue('O3', '原始投资额')
                        ->setCellValue('P3', '退出后回收额')
                        ->setCellValue('Q3', '企业并购退出项目个数（个）')
                        ->setCellValue('R3', '原始投资额')
                        ->setCellValue('S3', '退出后回收额')
                        ->setCellValue('T3', '其他退出方式项目数（个）')
                        ->setCellValue('U3', '原始投资额')
                        ->setCellValue('V3', '退出后回收额')
                        ->setCellValue('W3', '营业税')
                        ->setCellValue('X3', '所得税')
                        ->setCellValue('Y3', '其他税费')
                        ->setCellValue('Z3', '按性别')
                        ->setCellValue('AC3', '按学历')
                        ->setCellValue('AG3', '管理人员')
                        ->setCellValue('AH3', '劳务派遣制员工')
                        ->setCellValue('AI3', '持有国内高端从业资格证书的人员')
                        ->setCellValue('AJ3', '持有国外高端从业资格证书的人员')
                        ->setCellValue('Z4', '男性')
                        ->setCellValue('AA4', '女性')
                        ->setCellValue('AC4', '大专及大专以下')
                        ->setCellValue('AD4', '本科')
                        ->setCellValue('AE4', '硕士研究生')
                        ->setCellValue('AF4', '博士及博士以上');



                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:AF1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:B4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AB2:AB4');

                    $PHPExcel->getActiveSheet($index)->mergeCells('C2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:H2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('I2:X2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('Y2:AA2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AC2:AJ2');

                    $PHPExcel->getActiveSheet($index)->mergeCells('Z3:AA3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AC3:AF3');

                    $PHPExcel->getActiveSheet($index)->mergeCells('C3:C4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('D3:D4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('E3:E4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F3:F4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('G3:G4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('H3:H4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('I3:I4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('J3:J4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('K3:K4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('L3:L4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('M3:M4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('N3:N4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('O3:O4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('P3:P4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('Q3:Q4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('R3:R4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('S3:S4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('T3:T4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('U3:U4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('V3:V4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('W3:W4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('X3:X4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('Y3:Y4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AG3:AG4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AH3:AH4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AI3:AI4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('AJ3:AJ4');

                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 5;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['capital'])
                            ->setCellValue('C'.$line, $_data['Projects'])
                            ->setCellValue('D'.$line, $_data['Projects_su'])
                            ->setCellValue('E'.$line, $_data['Amount'])
                            ->setCellValue('F'.$line, $_data['Amount_su'])
                            ->setCellValue('G'.$line, $_data['exits_num'])
                            ->setCellValue('H'.$line, $_data['exits_a_num'])
                            ->setCellValue('I'.$line, $_data['exits_a_Investment'])
                            ->setCellValue('J'.$line, $_data['exits_a_Recycling'])
                            ->setCellValue('K'.$line, $_data['exits_b_num'])
                            ->setCellValue('L'.$line, $_data['exits_b_Investment'])
                            ->setCellValue('M'.$line, $_data['exits_b_Recycling'])
                            ->setCellValue('N'.$line, $_data['exits_c_num'])
                            ->setCellValue('O'.$line, $_data['exits_c_Investment'])
                            ->setCellValue('P'.$line, $_data['exits_c_Recycling'])
                            ->setCellValue('Q'.$line, $_data['exits_d_num'])
                            ->setCellValue('R'.$line, $_data['exits_d_Investment'])
                            ->setCellValue('S'.$line, $_data['exits_d_Recycling'])
                            ->setCellValue('T'.$line, $_data['exits_e_num'])
                            ->setCellValue('U'.$line, $_data['exits_e_Investment'])
                            ->setCellValue('V'.$line, $_data['exits_e_Recycling'])
                            ->setCellValue('W'.$line, $_data['Tax_B'])
                            ->setCellValue('X'.$line, $_data['Tax_I'])
                            ->setCellValue('Y'.$line, $_data['Tax_O'])
                            ->setCellValue('Z'.$line, $_data['Staff'])
                            ->setCellValue('AA'.$line, $_data['Staff_Sub'][0])
                            ->setCellValue('AB'.$line, $_data['Staff_Sub'][1])
                            ->setCellValue('AC'.$line, $_data['Staff_Sub'][2])
                            ->setCellValue('AD'.$line, $_data['Staff_Sub'][3])
                            ->setCellValue('AE'.$line, $_data['Staff_Sub'][4])
                            ->setCellValue('AF'.$line, $_data['Staff_Sub'][5])
                            ->setCellValue('AG'.$line, $_data['Staff_Sub'][6])
                            ->setCellValue('AH'.$line, $_data['Staff_Sub'][7])
                            ->setCellValue('AI'.$line, $_data['Staff_Sub'][8])
                            ->setCellValue('AJ'.$line, $_data['Staff_Sub'][9]);

                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

                // var_dump($data);die();
                $title = '股权投资管理机构统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '股权投资管理机构统计表'.'('.$year.'-'.$month.')'. '    单位: 万元)');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '注册资金')
                        ->setCellValue('C2', '管理资金规模')
                        ->setCellValue('D2', '管理机构数')
                        ->setCellValue('E2', '各类税费')
                        ->setCellValue('H2', '从业人员数量')
                        ->setCellValue('I2', '从业人员')

                        ->setCellValue('E3', '营业税')
                        ->setCellValue('F3', '所得税')
                        ->setCellValue('G3', '其他税费')
                        ->setCellValue('I3', '按性别')
                        ->setCellValue('K3', '按学历')
                        ->setCellValue('O3', '管理人员')
                        ->setCellValue('P3', '劳务派遣制员工')
                        ->setCellValue('Q3', '持有国内高端从业资格证书的人员')
                        ->setCellValue('R3', '持有国外高端从业资格证书的人员')

                        ->setCellValue('I4', '男性')
                        ->setCellValue('J4', '女性')
                        ->setCellValue('K4', '大专及大专以下')
                        ->setCellValue('L4', '本科')
                        ->setCellValue('M4', '硕士研究生')
                        ->setCellValue('N4', '博士及博士以上');



                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:R1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:B4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('C2:C4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('D2:D4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('H2:H4');

                    $PHPExcel->getActiveSheet($index)->mergeCells('E2:G2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('I2:R2');

                    $PHPExcel->getActiveSheet($index)->mergeCells('I3:J3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('K3:N3');


                    $PHPExcel->getActiveSheet($index)->mergeCells('E3:E4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F3:F4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('G3:G4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('G3:G4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('O3:O4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('P3:P4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('Q3:Q4');
                    $PHPExcel->getActiveSheet($index)->mergeCells('R3:R4');

                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 5;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['capital'])
                            ->setCellValue('C'.$line, $_data['Amount'])
                            ->setCellValue('D'.$line, $_data['Projects'])
                            ->setCellValue('E'.$line, $_data['Tax_B'])
                            ->setCellValue('F'.$line, $_data['Tax_I'])
                            ->setCellValue('G'.$line, $_data['Tax_O'])
                            ->setCellValue('H'.$line, $_data['Staff'])
                            ->setCellValue('I'.$line, $_data['Staff_Sub'][0])
                            ->setCellValue('J'.$line, $_data['Staff_Sub'][1])
                            ->setCellValue('K'.$line, $_data['Staff_Sub'][2])
                            ->setCellValue('L'.$line, $_data['Staff_Sub'][3])
                            ->setCellValue('M'.$line, $_data['Staff_Sub'][4])
                            ->setCellValue('N'.$line, $_data['Staff_Sub'][5])
                            ->setCellValue('O'.$line, $_data['Staff_Sub'][6])
                            ->setCellValue('P'.$line, $_data['Staff_Sub'][7])
                            ->setCellValue('Q'.$line, $_data['Staff_Sub'][8])
                            ->setCellValue('R'.$line, $_data['Staff_Sub'][9]);


                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:

                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

                // var_dump($data);die();
                $title = '期货营业部统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '期货营业部统计表'.'('.$year.'-'.$month.')'. '');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '成交量（万手')
                        ->setCellValue('D2', '成交额（亿）')
                        ->setCellValue('F2', '新开户数（个）')
                        ->setCellValue('H2', '资产总值（亿）')
                        ->setCellValue('J2', '利润总额（万）')

                        ->setCellValue('B3', '本年累计')
                        ->setCellValue('C3', '同比%')
                        ->setCellValue('D3', '本年累计')
                        ->setCellValue('E3', '同比%')
                        ->setCellValue('F3', '本年累计')
                        ->setCellValue('G3', '同比%')
                        ->setCellValue('H3', '当月')
                        ->setCellValue('I3', '同比%')
                        ->setCellValue('J3', '当月')
                        ->setCellValue('K3', '同比%');




                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:K1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');


                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:C2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('D2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:G2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('H2:I2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('J2:K2');



                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['G_Volume'])
                            ->setCellValue('C'.$line, $_data['G_Volume_yoy'])
                            ->setCellValue('D'.$line, $_data['G_Turnover'])
                            ->setCellValue('E'.$line, $_data['G_Turnover_yoy'])
                            ->setCellValue('F'.$line, $_data['G_Account'])
                            ->setCellValue('G'.$line, $_data['G_Account_yoy'])
                            ->setCellValue('H'.$line, $_data['C_Assets'])
                            ->setCellValue('I'.$line, $_data['C_Assets_yoy'])
                            ->setCellValue('J'.$line, $_data['C_Profit'])
                            ->setCellValue('K'.$line, $_data['C_Profit_yoy']);

                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                $average = $statistics_datas[1];
                $statistics_datas = $statistics_datas[0];
                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }
                $average['other_name'] = '合计(平均)';
                array_push($data, $average);
                //var_dump($data);die();
                $title = '融资租赁统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '融资租赁统计表'.'('.$year.'-'.$month.')'. '单位:万元');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '基本信息')
                        ->setCellValue('D2', '租赁资产额')
                        ->setCellValue('F2', '业务笔数')
                        ->setCellValue('I2', '服务客户数')
                        ->setCellValue('L2', '经营情况（%）')
                        ->setCellValue('O2', '盈利情况')

                        ->setCellValue('B3', '实缴注册资本')
                        ->setCellValue('C3', '所有者权益')
                        ->setCellValue('D3', '月末余额')
                        ->setCellValue('E3', '本年累计额')
                        ->setCellValue('F3', '月末留存笔数')
                        ->setCellValue('G3', '本年新增笔数')
                        ->setCellValue('H3', '开业以来累计')
                        ->setCellValue('I3', '月末留存户数')
                        ->setCellValue('J3', '本年新增户数')
                        ->setCellValue('K3', '开业以来累计')
                        ->setCellValue('L3', '售后回租占比')
                        ->setCellValue('M3', '平均收益率')
                        ->setCellValue('N3', '租金回收率')
                        ->setCellValue('O3', '本年累计营业收入')
                        ->setCellValue('P3', '本年累计净利润')
                        ->setCellValue('Q3', '本年累计应缴增值税')
                        ->setCellValue('R3', '本年累计应缴所得税');




                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:R1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');


                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:C2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('D2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:H2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('I2:K2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('L2:N2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('O2:R2');


                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['Capital'])
                            ->setCellValue('C'.$line, $_data['Owner'])
                            ->setCellValue('D'.$line, $_data['Assets_M'])
                            ->setCellValue('E'.$line, $_data['Assets_Y'])
                            ->setCellValue('F'.$line, $_data['Business_Stay'])
                            ->setCellValue('G'.$line, $_data['Business_Y_New'])
                            ->setCellValue('H'.$line, $_data['Business_T_New'])
                            ->setCellValue('I'.$line, $_data['Client_Stay'])
                            ->setCellValue('J'.$line, $_data['Client_Y_New'])
                            ->setCellValue('K'.$line, $_data['Client_T_New'])
                            ->setCellValue('L'.$line, $_data['Business_C1'])
                            ->setCellValue('M'.$line, $_data['Business_C2'])
                            ->setCellValue('N'.$line, $_data['Business_C3'])
                            ->setCellValue('O'.$line, $_data['Profit_AY'])
                            ->setCellValue('P'.$line, $_data['Profit_BY'])
                            ->setCellValue('Q'.$line, $_data['Profit_CY'])
                            ->setCellValue('R'.$line, $_data['Profit_DY']);

                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                //var_dump($statistics_datas);die();
                $average = $statistics_datas[1];
                $statistics_datas = $statistics_datas[0];
                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }
                $average['other_name'] = '合计';
                array_push($data, $average);
               // var_dump($data);die();
                $title = '小额贷款统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '小额贷款统计表'.'('.$year.'-'.$month.')'. '(单位:万元)');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '注册资本')
                        ->setCellValue('C2', '可放贷资金')
                        ->setCellValue('E2', '月末贷款')
                        ->setCellValue('I2', '本年贷款累放')
                        ->setCellValue('M2', '开业以来贷款累放')
                        ->setCellValue('Q2', '年利率(%)')
                        ->setCellValue('R2', '不良贷款')
                        ->setCellValue('S2', '净利润')
                        ->setCellValue('T2', '总收入')


                        ->setCellValue('C3', '所有者权益')
                        ->setCellValue('D3', '银行融资')
                        ->setCellValue('E3', '余额')
                        ->setCellValue('F3', '笔数')
                        ->setCellValue('G3', '小额贷款')
                        ->setCellValue('H3', '笔数')
                        ->setCellValue('I3', '余额')
                        ->setCellValue('J3', '笔数')
                        ->setCellValue('K3', '小额贷款')
                        ->setCellValue('L3', '笔数')
                        ->setCellValue('M3', '余额')
                        ->setCellValue('N3', '笔数')
                        ->setCellValue('O3', '小额贷款')
                        ->setCellValue('P3', '笔数')
                        ->setCellValue('Q3', '年利率(%)')
                        ->setCellValue('R3', '不良贷款')
                        ->setCellValue('S3', '净利润')
                        ->setCellValue('T3', '总收入');




                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:T1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:B3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('Q2:Q3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('R2:R3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('S2:S3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('T2:T3');

                    $PHPExcel->getActiveSheet($index)->mergeCells('C2:D2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('E2:H2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('I2:L2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('M2:P2');

                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['Capital'])
                            ->setCellValue('C'.$line, $_data['Funds_Owner'])
                            ->setCellValue('D'.$line, $_data['Funds_Bank'])
                            ->setCellValue('E'.$line, $_data['Month_Amount'])
                            ->setCellValue('F'.$line, $_data['Month_Amount_N'])
                            ->setCellValue('G'.$line, $_data['Month_Small'])
                            ->setCellValue('H'.$line, $_data['Month_Small_N'])
                            ->setCellValue('I'.$line, $_data['Year_Amount'])
                            ->setCellValue('J'.$line, $_data['Year_Amount_N'])
                            ->setCellValue('K'.$line, $_data['Year_Small'])
                            ->setCellValue('L'.$line, $_data['Year_Small_N'])
                            ->setCellValue('M'.$line, $_data['Total_Amount'])
                            ->setCellValue('N'.$line, $_data['Total_Amount_N'])
                            ->setCellValue('O'.$line, $_data['Total_Small'])
                            ->setCellValue('P'.$line, $_data['Total_Small_N'])
                            ->setCellValue('Q'.$line, $_data['Interest_Rate'])
                            ->setCellValue('R'.$line, $_data['Bad_Debt'])
                            ->setCellValue('S'.$line, $_data['Net_Profit'])
                            ->setCellValue('T'.$line, $_data['Revenue']);

                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:


                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }

                 //var_dump($data);die();
                $title = '证券营业部统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '证券营业部统计表'.'('.$year.'-'.$month.')'. '');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '成交额（亿）')
                        ->setCellValue('F2', '新开户数（个）')
                        ->setCellValue('J2', '资产总值（亿）')
                        ->setCellValue('L2', '利润（万）')

                        ->setCellValue('B3', '本月')
                        ->setCellValue('C3', '同比%')
                        ->setCellValue('D3', '本年累计')
                        ->setCellValue('E3', '同比%')
                        ->setCellValue('F3', '本月')
                        ->setCellValue('G3', '同比%')
                        ->setCellValue('H3', '本年累计')
                        ->setCellValue('I3', '同比%')
                        ->setCellValue('J3', '当期')
                        ->setCellValue('K3', '同比%')
                        ->setCellValue('L3', '当期')
                        ->setCellValue('M3', '同比%');




                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:M1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');

                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:I2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('J2:K2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('L2:M2');

                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['Volume'])
                            ->setCellValue('C'.$line, $_data['Volume_yoy'])
                            ->setCellValue('D'.$line, $_data['G_Volume'])
                            ->setCellValue('E'.$line, $_data['G_Volume_yoy'])
                            ->setCellValue('F'.$line, $_data['New_Account'])
                            ->setCellValue('G'.$line, $_data['New_Account_yoy'])
                            ->setCellValue('H'.$line, $_data['G_New_Account'])
                            ->setCellValue('I'.$line, $_data['G_New_Account_yoy'])
                            ->setCellValue('J'.$line, $_data['Assets'])
                            ->setCellValue('K'.$line, $_data['Assets_yoy'])
                            ->setCellValue('L'.$line, $_data['Profit'])
                            ->setCellValue('M'.$line, $_data['Profit_yoy']);


                        $line ++;


                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                //var_dump($statistics_datas);die();
                $average = $statistics_datas[1];
                $statistics_datas = $statistics_datas[0];
                $all_names = result_to_array($statistics_datas, 'all_name');
                $statistics_datas_map = result_to_map($statistics_datas, 'all_name');
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $departments = $DepartmentService->get_by_all_names($all_names, $this->type);
                $data = [];
                foreach ($departments as $department) {
                    $statistics_datas_map[$department['all_name']]['other_name'] = $department['other_name'] ? $department['other_name'] : $department['all_name'];
                    $data[] = $statistics_datas_map[$department['all_name']];

                }
                $average['other_name'] = '总计';
                array_push($data, $average);
                // var_dump($data);die();
                $title = '转贷统计表' . $title;

                $index = 0;
                if ($data) {

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A1', '转贷统计表'.'('.$year.'-'.$month.')'. '');

                    $PHPExcel->setActiveSheetIndex($index)
                        ->setCellValue('A2', '公司名称')
                        ->setCellValue('B2', '本月')
                        ->setCellValue('D2', '本年累计')
                        ->setCellValue('F2', '开业以来累计')
                        ->setCellValue('H2', '成立时间')

                        ->setCellValue('B3', '金额(亿)')
                        ->setCellValue('C3', '笔数')
                        ->setCellValue('D3', '金额(亿)')
                        ->setCellValue('E3', '笔数')
                        ->setCellValue('F3', '金额(亿)')
                        ->setCellValue('G3', '笔数');


                    $PHPExcel->getActiveSheet($index)->mergeCells('A1:H1');
                    $PHPExcel->getActiveSheet($index)->mergeCells('A2:A3');
                    $PHPExcel->getActiveSheet($index)->mergeCells('H2:H3');


                    $PHPExcel->getActiveSheet($index)->mergeCells('B2:C2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('D2:E2');
                    $PHPExcel->getActiveSheet($index)->mergeCells('F2:G2');

                    $PHPExcel->getActiveSheet($index)->getStyle('A1')->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            ]
                        ]
                    );

                    $line = 4;

                    foreach ($data as $_data) {
                        $PHPExcel->setActiveSheetIndex($index)
                            ->setCellValue('A'.$line, $_data['other_name'])
                            ->setCellValue('B'.$line, $_data['M_Amount'])
                            ->setCellValue('C'.$line, $_data['M_Quantity'])
                            ->setCellValue('D'.$line, $_data['Y_Amount'])
                            ->setCellValue('E'.$line, $_data['Y_Quantity'])
                            ->setCellValue('F'.$line, $_data['T_Amount'])
                            ->setCellValue('G'.$line, $_data['T_Quantity'])
                            ->setCellValue('H'.$line, time_to_date($_data['build_time']));

                        $line ++;

                    }
                }
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


    protected function gain_statistics($year,$month,$type,$where = []) {

        $where['year'] = $year;
        $where['month'] = $month;
        switch ($type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                $Service = \Common\Service\InsurancePropertyService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['income_yoy']);
                        unset($value['payoff_rate']);
                        unset($value['payoff_a_rate']);
                        unset($value['payoff_b_rate']);
                        unset($value['payoff_c_rate']);
                        $Service->update_by_id($value['id'], $value);
                    }
                }

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:
                $Service = \Common\Service\InsuranceLifeService::get_instance();

                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['income_a_yoy']);
                        unset($value['payoff_a_rate']);

                        $Service->update_by_id($value['id'], $value);
                    }
                }


                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:
                $Service = \Common\Service\InsuranceMutualService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        $this->update_st($value);
                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                $Service = \Common\Service\VouchService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['C_Balance_Ly']);
                        unset($value['C_Quantity_Ly']);
                        unset($value['G_Quantity_Ly']);
                        unset($value['G_Income']);
                        unset($value['G_Recover']);
                        unset($value['G_Vouch']);
                        unset($value['G_Vouch_Ly']);

                        $Service->update_by_id($value['id'], $value);
                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:
                $Service = \Common\Service\InvestmentService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['Staff']);
                        $Service->update_by_id($value['id'], $value);
                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:
                $Service = \Common\Service\InvestmentManagerService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['Staff']);
                        $Service->update_by_id($value['id'], $value);
                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:
                $Service = \Common\Service\FuturesService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['G_Volume']);
                        unset($value['G_Volume_yoy']);
                        unset($value['G_Turnover']);
                        unset($value['G_Turnover_yoy']);
                        unset($value['G_Account']);
                        unset($value['G_Account_yoy']);
                        unset($value['C_Assets_yoy']);
                        unset($value['C_Profit_yoy']);

                        $Service->update_by_id($value['id'], $value);
                    }
                }
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                $Service = \Common\Service\LeaseService::get_instance();

                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['Assets_Y']);
                        unset($value['Business_Y_New']);
                        unset($value['Business_T_New']);
                        unset($value['Client_Y_New']);
                        unset($value['Client_T_New']);
                        unset($value['Profit_AY']);
                        unset($value['Profit_BY']);
                        unset($value['Profit_CY']);
                        unset($value['Profit_DY']);
                        $Service->update_by_id($value['id'], $value);
                    }
                }

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                $Service = \Common\Service\LoanService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['Year_Amount']);
                        unset($value['Year_Amount_N']);
                        unset($value['Year_Small']);
                        unset($value['Year_Small_N']);
                        unset($value['Total_Amount']);
                        unset($value['Total_Amount_N']);
                        unset($value['Total_Small']);
                        unset($value['Total_Small_N']);

                        $Service->update_by_id($value['id'], $value);
                    }
                }

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:
                $Service = \Common\Service\SecuritiesService::get_instance();
                $list = $Service->get_by_where_all($where);
                if ($list) {
                    foreach ($list as $key => $value) {
                        unset($value['Volume_yoy']);
                        unset($value['G_Volume']);
                        unset($value['G_Volume_yoy']);
                        unset($value['New_Account_yoy']);
                        unset($value['G_New_Account']);
                        unset($value['G_New_Account_yoy']);
                        unset($value['Assets_yoy']);
                        unset($value['Profit_yoy']);

                        $Service->update_by_id($value['id'], $value);
                    }
                }

                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                $Service = \Common\Service\TransferFundsService::get_instance();

                $list = $Service->get_by_where_all($where);
                $list_map = result_to_complex_map($list,'all_name');
                if ($list_map) {
                    foreach ($list_map as $key => $value) {

                        $this->update_st($value);
                    }
                }

                break;
        }

    }

}