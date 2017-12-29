<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
class AuthManagerGroupAddUser extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {
        $uid = I('uid');
        $gid = I('gid');
        if( empty($uid) ){
            return result_json(false, '参数错误');

        }
        $AuthGroup = new AuthGroupModel();
        if(is_numeric($uid)){
            if ( is_administrator($uid) ) {
                return result_json(false, '该用户为超级管理员');
            }
            if( !M('Member')->where(array('uid'=>$uid))->find() ){
                return result_json(false, '管理员用户不存在');
            }
        }

        if( $gid && !$AuthGroup->checkGroupId($gid)){
            return result_json(false, $AuthGroup->error);
        }

        if ($gid == C('GROUP_FRANCHISEE') && !I('dev_y')) {
            return result_json(false, '加盟商组不支持人为管理,请联系技术');
        }
        if ( $AuthGroup->addToGroup($uid,$gid) ){
            return result_json(TRUE, '操作成功');
        }else{
            return result_json(false, $AuthGroup->error);
        }




    }


}