<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
class MenuDel extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {
        $id = I('id');
        $map = array('id' => $id );
        if(M('Menu')->where($map)->delete()){

            //记录行为
            action_user_log('删除成功');
            return result_json(TRUE, '删除成功', 1);
        } else {
            return result_json(false, '删除失败', 0);
        }

    }

}