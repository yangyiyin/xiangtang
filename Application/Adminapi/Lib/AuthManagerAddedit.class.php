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
class AuthManagerAddedit extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {

    }

    public function excute() {

        $post = $this->post_data['data'];
//        var_dump($post);
        $data = [];
        if ($post['rules']) {
            $data['rules'] = $post['rules'];
        }
        $data['module'] =  'admin';
        $data['type']   =  AuthGroupModel::TYPE_ADMIN;
        $data['id'] = $post['id'];
        if ($post['title']) {
            $data['title'] = $post['title'];
        }
        if ($post['description']) {
            $data['description'] = $post['description'];
        }
        $AuthGroup       =  D('AuthGroup');
        $ret = $AuthGroup->create($data);
        if ( $ret ) {
            if ( empty($ret['id']) ) {
                $r = $AuthGroup->add();
            }else{
                $r = $AuthGroup->save();
            }
            if($r===false){
                return result_json(false, '操作失败'.$AuthGroup->getError(), 0);

            } else{
                return result_json(TRUE, '操作成功', 1);

            }
        }else{
            return result_json(false, '参数错误', 0);

        }




    }

}