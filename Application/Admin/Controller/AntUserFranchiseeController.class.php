<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;
use Admin\Model\MemberModel;
use User\Api\UserApi;
class AntUserFranchiseeController extends AdminController  {
    protected $MemberService;
    protected function _initialize() {
        parent::_initialize();
        $this->MemberService = \Common\Service\MemberService::get_instance();
    }

    public function index() {
        $nickname       =   I('nickname');
        $map['status']  =   array('egt',0);
        if(is_numeric($nickname)){
            $map['uid|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
        }
        $map['attr'] = MemberModel::ATTR_FRANCHISEE;
        $list   = $this->lists('Member', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display();
    }
    public function add() {

        if ($id = I('get.id')) {
            $user = $this->MemberService->get_info_by_id($id);
            if ($user) {
                $this->assign('info',$user);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');

            if ($id) {
                $ret = $this->MemberService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改加盟商');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                /* 检测密码 */
                $password = '123456';
                $username = $data['username'];
                /* 调用注册接口注册用户 */
                $User   =   new UserApi();
                $uid    =   $User->register($username, $password, '');
                if(0 < $uid){ //注册成功
                    $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1, 'reg_time' => time());
                    $user['entity_name'] = $data['entity_name'];
                    $user['entity_title'] = $data['entity_title'];
                    $user['entity_tel'] = $data['entity_tel'];
                    $user['entity_license'] = $data['entity_license'];
                    $user['attr'] = MemberModel::ATTR_FRANCHISEE;
                    if(!M('Member')->add($user)){
                        $this->error('加盟商添加失败！');
                    } else {
                        $gid = C('GROUP_FRANCHISEE');
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
                        $this->success('加盟商添加成功！',U('index'));
                    }
                } else { //注册失败，显示错误信息
                    $this->error('加盟商添加失败!');
                }
            }

        }
    }

    public function entity_info() {
        $id = I('get.id');
        if (!$id) {
            $this->error('id没有');
        }
        $user = $this->MemberService->get_info_by_id($id);
        if (!$user) {
            $this->error('没有找到信息~');
        }
        $this->assign('info', $user);
        $this->display();
    }
}