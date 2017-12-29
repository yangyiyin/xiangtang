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
class AuthManagerGroupRemoveUser extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {


        $uid = I('uid');
        $gid = I('gid');
        if( $uid==UID ){

            return result_json(false, '不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            return result_json(false, '参数有误');
        }
        $AuthGroup = new AuthGroupModel();
        if( !$AuthGroup->find($gid)){
            return result_json(false, '用户组不存在');
        }
        if ( $AuthGroup->removeFromGroup($uid,$gid) ){
            return result_json(true, '操作成功');
        }else{
            return result_json(false, '操作失败');

        }




    }


}